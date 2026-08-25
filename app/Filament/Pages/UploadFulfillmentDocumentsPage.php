<?php

namespace App\Filament\Pages;

use App\Models\DeliveryReceipt;
use App\Models\PurchaseOrder;
use App\Models\SalesInvoice;
use App\Services\OrderFulfillmentService;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use UnitEnum;

class UploadFulfillmentDocumentsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canCreateDocuments() ?? true;
    }

    public function mount(): void
    {
        $poId = request()->query('purchase_order_id');
        $initialPo = null;

        if ($poId) {
            $initialPo = PurchaseOrder::find($poId);
        }

        if (!$initialPo) {
            $initialPo = PurchaseOrder::where('status', '!=', PurchaseOrder::STATUS_CANCELLED)
                ->where('is_completed', false)
                ->latest()
                ->first();
        }

        $this->form->fill([
            'purchase_order_id' => $initialPo?->id,
            'dr_number'         => DeliveryReceipt::generateNumber(),
            'delivery_date'     => $initialPo?->actual_delivery_date?->toDateString() ?: now()->toDateString(),
            'delivered_by'      => null,
            'received_by'       => null,
            'si_number'         => SalesInvoice::generateNumber(),
            'invoice_date'      => now()->toDateString(),
            'payment_status'    => SalesInvoice::STATUS_PAID,
            'total_amount'      => $initialPo ? (float) $initialPo->order_amount : 0,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Purchase Order Selection')
                    ->description('Select the target Purchase Order to fulfill with Delivery Receipt and Sales Invoice.')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Grid::make(12)->schema([
                            Select::make('purchase_order_id')
                                ->label('Target Purchase Order')
                                ->options(function () {
                                    return PurchaseOrder::whereNotIn('status', [PurchaseOrder::STATUS_CANCELLED, PurchaseOrder::STATUS_REJECTED])
                                        ->orderBy('created_at', 'desc')
                                        ->get()
                                        ->mapWithKeys(function ($po) {
                                            $flag = $po->isCompleted() ? '✅ [Fulfilled]' : ($po->delivery_status === PurchaseOrder::DELIVERY_DELIVERED ? '🚚 [Delivered - Awaiting DR&SI]' : '⏳ [Pending]');
                                            return [$po->id => "{$po->po_number} — {$po->customer_name} (₱" . number_format($po->order_amount, 2) . ") {$flag}"];
                                        });
                                })
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state && $po = PurchaseOrder::find($state)) {
                                        $set('total_amount', (float) $po->order_amount);
                                        if ($po->actual_delivery_date) {
                                            $set('delivery_date', $po->actual_delivery_date->toDateString());
                                        }
                                    }
                                })
                                ->columnSpan(['default' => 12, 'md' => 8]),

                            Placeholder::make('po_status_info')
                                ->label('Current Order State')
                                ->content(function ($get) {
                                    $poId = $get('purchase_order_id');
                                    if (!$poId) return 'No PO selected';
                                    $po = PurchaseOrder::with('salesAgent', 'project')->find($poId);
                                    if (!$po) return 'PO not found';

                                    $statusBadge = match (true) {
                                        $po->isCompleted() => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Completed & Realized</span>',
                                        $po->delivery_status === PurchaseOrder::DELIVERY_DELIVERED => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">Delivered (Pending DR & SI)</span>',
                                        default => '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300">Approved (Pending Delivery)</span>',
                                    };

                                    return new HtmlString("
                                        <div class='text-xs space-y-1'>
                                            <div><strong>Status:</strong> {$statusBadge}</div>
                                            <div><strong>Customer:</strong> {$po->customer_name}</div>
                                            <div><strong>Amount:</strong> ₱" . number_format($po->order_amount, 2) . "</div>
                                        </div>
                                    ");
                                })
                                ->columnSpan(['default' => 12, 'md' => 4]),
                        ]),
                    ]),

                Grid::make(2)->schema([
                    Section::make('Delivery Receipt (DR) Upload')
                        ->description('Attach scanned or digital Delivery Receipt (PDF, JPG, PNG, WEBP)')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            FileUpload::make('dr_file')
                                ->label('Delivery Receipt File (PDF / Image)')
                                ->required()
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                ->maxSize(25600)
                                ->disk('local')
                                ->directory('documents/dr')
                                ->preserveFilenames()
                                ->helperText('Supported formats: PDF, JPG, PNG, WEBP (Max 25MB)'),

                            TextInput::make('dr_number')
                                ->label('DR #')
                                ->default(fn() => DeliveryReceipt::generateNumber())
                                ->required(),

                            DatePicker::make('delivery_date')
                                ->label('Delivery Date')
                                ->default(now()->toDateString())
                                ->required(),

                            TextInput::make('delivered_by')
                                ->label('Delivered By')
                                ->placeholder('Logistics or driver name'),

                            TextInput::make('received_by')
                                ->label('Received By')
                                ->placeholder('Client site receiver name'),
                        ])->columnSpan(1),

                    Section::make('Sales Invoice (SI) Upload')
                        ->description('Attach scanned or digital Sales Invoice (PDF, JPG, PNG, WEBP)')
                        ->icon('heroicon-o-receipt-percent')
                        ->schema([
                            FileUpload::make('si_file')
                                ->label('Sales Invoice File (PDF / Image)')
                                ->required()
                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                ->maxSize(25600)
                                ->disk('local')
                                ->directory('documents/si')
                                ->preserveFilenames()
                                ->helperText('Supported formats: PDF, JPG, PNG, WEBP (Max 25MB)'),

                            TextInput::make('si_number')
                                ->label('SI #')
                                ->default(fn() => SalesInvoice::generateNumber())
                                ->required(),

                            DatePicker::make('invoice_date')
                                ->label('Invoice Date')
                                ->default(now()->toDateString())
                                ->required(),

                            Select::make('payment_status')
                                ->label('Payment Status')
                                ->options([
                                    SalesInvoice::STATUS_PAID => 'Paid',
                                    SalesInvoice::STATUS_UNPAID => 'Unpaid',
                                    SalesInvoice::STATUS_PARTIAL => 'Partial',
                                ])
                                ->default(SalesInvoice::STATUS_PAID)
                                ->required(),

                            TextInput::make('total_amount')
                                ->label('Total Amount (₱)')
                                ->numeric()
                                ->prefix('₱')
                                ->required(),
                        ])->columnSpan(1),
                ]),
            ]);
    }

    public function submit(): void
    {
        $formData = $this->form->getState();

        $po = PurchaseOrder::find($formData['purchase_order_id']);
        if (!$po) {
            Notification::make()->title('PO Not Found')->body('Please select a valid Purchase Order.')->danger()->send();
            return;
        }

        try {
            $result = app(OrderFulfillmentService::class)->fulfillOrder($po, $formData);
            $dr = $result['delivery_receipt'];
            $si = $result['sales_invoice'];

            Notification::make()
                ->title('Transaction Completed & Stock Deducted!')
                ->body("Order for PO {$po->po_number} has been fulfilled with DR #{$dr->dr_number} and SI #{$si->si_number}. Stock quantities have been deducted from product catalog, and sales revenue has been realized in analytics and leaderboards.")
                ->success()
                ->duration(10000)
                ->send();

            $this->redirect(PurchaseOrderResource::getUrl('index'));
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Fulfillment Processing Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
