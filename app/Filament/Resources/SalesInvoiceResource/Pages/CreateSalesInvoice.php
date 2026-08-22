<?php

namespace App\Filament\Resources\SalesInvoiceResource\Pages;

use App\Filament\Resources\SalesInvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesInvoice extends CreateRecord
{
    protected static string $resource = SalesInvoiceResource::class;

    public function mount(): void
    {
        parent::mount();

        $poId = request()->query('purchase_order_id');
        $drId = request()->query('delivery_receipt_id');

        if ($poId) {
            $po = \App\Models\PurchaseOrder::with('lineItems.product')->find($poId);
            if ($po) {
                $subtotal = 0;
                $items = $po->lineItems->map(function ($line) use (&$subtotal) {
                    $qty = (float) $line->qty;
                    $price = (float) ($line->discounted_price ?: $line->unit_price);
                    $lineTotal = (float) ($line->line_total ?: round($qty * $price, 2));
                    $subtotal += $lineTotal;
                    return [
                        'product_id' => $line->product_id,
                        'qty' => $qty,
                        'unit' => $line->unit ?: 'pcs',
                        'unit_price' => $price,
                        'line_total' => $lineTotal,
                    ];
                })->toArray();

                $vat = round($subtotal * 0.12, 2);

                $this->form->fill([
                    'purchase_order_id' => $po->id,
                    'delivery_receipt_id' => $drId ?: null,
                    'customer_name' => $po->customer_name,
                    'invoice_date' => now()->toDateString(),
                    'due_date' => now()->addDays(30)->toDateString(),
                    'payment_status' => 'unpaid',
                    'subtotal' => $subtotal,
                    'vat_amount' => $vat,
                    'total_amount' => $subtotal + $vat,
                    'items' => $items,
                ]);
            }
        }
    }
}
