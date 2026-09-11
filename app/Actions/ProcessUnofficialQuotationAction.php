<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\User;
use App\Services\CustomerDailyInquiryService;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProcessUnofficialQuotationAction
{
    public function __construct(
        protected CustomerDailyInquiryService $inquiryTracker
    ) {}

    public function execute(array $inquiryPayload, Request $inquiryRequest): array
    {
        $clientIp = $inquiryRequest->ip() ?: '127.0.0.1';

        $subtotal = 0.0;
        $subtotalUndiscounted = 0.0;
        $lineItems = [];

        foreach ($inquiryPayload['items'] as $index => $item) {
            $lineQty = (float) ($item['quantity'] ?? 1);
            $productId = ! empty($item['product_id']) && Product::where('id', (int) $item['product_id'])->exists()
                ? (int) $item['product_id']
                : null;
            $productRecord = $productId ? Product::find($productId) : null;

            $unitPrice = (float) ($item['unit_price'] ?? 0);
            if ($unitPrice <= 0 && $productRecord) {
                $unitPrice = (float) ($productRecord->default_price ?: $productRecord->selling_price ?: 0);
            }

            $discountedPrice = $productRecord && (float) $productRecord->selling_price > 0 && (float) $productRecord->selling_price < $unitPrice
                ? (float) $productRecord->selling_price
                : ($unitPrice > 0 ? round($unitPrice * 0.90, 2) : 0);

            if ($unitPrice <= 0) {
                $unitPrice = 0.0;
                $discountedPrice = 0.0;
            }

            $lineTotal = round($lineQty * $discountedPrice, 2);
            $undiscountedTotal = round($lineQty * $unitPrice, 2);

            $subtotal += $lineTotal;
            $subtotalUndiscounted += $undiscountedTotal;

            $itemCode = $item['item_code'] ?? ($productRecord?->sku ?: $productRecord?->product_code ?: ('HISI-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT)));
            $desc = $item['description'] ?: ($productRecord?->canonical_name ?? 'Product Line Item');

            $lineItems[] = [
                'product_id' => $productId,
                'item_code' => $itemCode,
                'description' => $desc,
                'quantity' => $lineQty,
                'unit' => $item['unit'] ?? ($productRecord?->unit_default ?: 'pcs'),
                'unit_price' => $unitPrice,
                'discounted_price' => $discountedPrice,
                'line_total' => $lineTotal,
                'base64_image' => $productRecord?->base64_image,
            ];
        }

        $vatAmount = round($subtotal * 0.12, 2);
        $grandTotal = round($subtotal, 2);
        $refNumber = date('ymd').strtoupper(substr(uniqid(), -3)).' - P';

        $quoteSummary = [
            'quotation_number' => $refNumber,
            'customer_name' => $inquiryPayload['customer_name'] ?? 'Walk-in Client',
            'customer_company' => $inquiryPayload['customer_company'],
            'customer_address' => $inquiryPayload['customer_address'] ?? ($inquiryPayload['project_location'] ?? 'Metro Manila'),
            'email' => ! empty($inquiryPayload['email']) ? $inquiryPayload['email'] : 'N/A',
            'phone_no' => $inquiryPayload['phone_no'],
            'project_name' => ! empty($inquiryPayload['project_name']) ? $inquiryPayload['project_name'] : 'General Procurement Project',
            'project_location' => ! empty($inquiryPayload['project_location']) ? $inquiryPayload['project_location'] : 'Metro Manila',
            'quotation_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(15)->format('Y-m-d'),
            'notes' => $inquiryPayload['notes'] ?? '',
            'items' => $lineItems,
            'subtotal' => $subtotal,
            'subtotal_undiscounted' => $subtotalUndiscounted,
            'total_amount' => $subtotalUndiscounted,
            'negotiated_amount' => $subtotal,
            'vat_amount' => $vatAmount,
            'grand_total' => $grandTotal,
            'is_encoded' => false,
        ];

        try {
            $defaultSalesAgent = User::whereIn('role', [User::ROLE_SALES_EXECUTIVE, User::ROLE_ADMIN])->first();

            $totalAcquisitionCost = 0.0;
            foreach ($lineItems as &$line) {
                $pId = $line['product_id'] ?? null;
                $prod = $pId ? Product::find($pId) : null;
                $linePrice = (float) ($line['discounted_price'] > 0 ? $line['discounted_price'] : $line['unit_price']);
                $baseCost = (float) ($prod?->base_cost_price ?: round($linePrice * 0.70, 2));
                $lineCost = round((float) $line['quantity'] * $baseCost, 2);
                $grossProfit = round((float) $line['line_total'] - $lineCost, 2);

                $line['base_cost'] = $baseCost;
                $line['gross_profit'] = $grossProfit;
                $totalAcquisitionCost += $lineCost;
            }
            unset($line);

            $effectiveTotal = (float) ($quoteSummary['negotiated_amount'] ?: $quoteSummary['total_amount']);
            $estimatedProfit = round($effectiveTotal - $totalAcquisitionCost, 2);

            $adminQuotation = Quotation::create([
                'quotation_number' => $refNumber,
                'sales_agent_id' => $defaultSalesAgent?->id ?? null,
                'customer_name' => $quoteSummary['customer_name'],
                'customer_company' => $quoteSummary['customer_company'],
                'customer_email' => $inquiryPayload['email'] ?? null,
                'phone_no' => $quoteSummary['phone_no'],
                'project_name' => $quoteSummary['project_name'],
                'project_location' => $quoteSummary['project_location'],
                'total_amount' => $quoteSummary['total_amount'],
                'negotiated_amount' => $quoteSummary['negotiated_amount'],
                'total_cost' => $totalAcquisitionCost,
                'estimated_profit' => $estimatedProfit,
                'status' => Quotation::STATUS_PENDING,
                'is_online_request' => true,
                'client_ip' => $clientIp,
                'quotation_date' => $quoteSummary['quotation_date'],
                'valid_until' => $quoteSummary['valid_until'],
                'notes' => ($quoteSummary['notes'] ? $quoteSummary['notes'].' | ' : '')."Client IP: {$clientIp} (Online Quotation Builder)",
                'is_official_po' => false,
            ]);

            foreach ($lineItems as $idx => $line) {
                $lineProdId = (! empty($line['product_id']) && Product::where('id', (int) $line['product_id'])->exists())
                    ? (int) $line['product_id']
                    : null;

                QuotationLineItem::create([
                    'quotation_id' => $adminQuotation->id,
                    'line_no' => $idx + 1,
                    'item_code' => $line['item_code'] ?? null,
                    'product_id' => $lineProdId,
                    'description' => $line['description'],
                    'qty' => $line['quantity'],
                    'unit' => $line['unit'],
                    'unit_price' => $line['unit_price'],
                    'discounted_price' => $line['discounted_price'],
                    'base_cost' => $line['base_cost'] ?? 0,
                    'line_total' => $line['line_total'],
                    'gross_profit' => $line['gross_profit'] ?? 0,
                ]);
            }

            $this->inquiryTracker->recordQuotationSent($inquiryRequest, $clientIp);
            $quoteSummary['is_encoded'] = true;

            $this->notifyStaffOfNewQuotationRequest($adminQuotation);
        } catch (\Throwable $caughtException) {
            Log::warning('Quotation admin sync warning: '.$caughtException->getMessage());
        }

        $this->inquiryTracker->recordQuotationSent($inquiryRequest, $clientIp);
        session(['last_unofficial_quote' => $quoteSummary]);

        return $quoteSummary;
    }

    protected function notifyStaffOfNewQuotationRequest(Quotation $quotation): void
    {
        $staffUsers = User::whereIn('role', [
            User::ROLE_ADMIN,
            User::ROLE_OPERATIONS_MANAGER,
        ])->get();

        $company = $quotation->customer_company ?: $quotation->customer_name;
        $itemCount = $quotation->lineItems()->count();

        $notification = Notification::make()
            ->title('New Online Quotation Request')
            ->body("{$quotation->customer_name} ({$company}) submitted a quotation request with {$itemCount} item(s).")
            ->icon('heroicon-o-document-text')
            ->iconColor('warning')
            ->actions([
                Action::make('view')
                    ->label('Review Quotation')
                    ->url(route('filament.admin.resources.quotations.view', ['record' => $quotation->id]))
                    ->button(),
            ]);

        foreach ($staffUsers as $user) {
            $notification->sendToDatabase($user);
        }
    }
}
