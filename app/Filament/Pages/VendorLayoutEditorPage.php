<?php

namespace App\Filament\Pages;

use App\Models\Vendor;
use App\Models\VendorDocumentLayout;
use App\Models\VendorLayoutFieldMapping;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class VendorLayoutEditorPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static UnitEnum|string|null $navigationGroup = 'Master Data & Registry';
    protected static ?string $navigationLabel = 'Vendor Layout Mappings';
    protected static ?string $title = 'Dynamic Vendor Layout Configurator';
    protected string $view = 'filament.pages.vendor-layout-editor-page';
    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->canConfigureLayouts() ?? true;
    }

    public function mount(): void
    {
        $defaultVendor = Vendor::query()->first();
        $this->form->fill([
            'vendor_id' => $defaultVendor?->id,
            'document_type' => 'purchase_order',
            'header_identifier_regex' => null,
            'notes' => null,
            'mappings' => [],
        ]);

        if ($defaultVendor) {
            $this->loadLayoutForSelection($defaultVendor->id, 'purchase_order');
        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Vendor & Document Format')
                    ->description('Select the target vendor and document format to configure specialized field extraction strategies.')
                    ->icon('heroicon-o-building-storefront')
                    ->schema([
                        Select::make('vendor_id')
                            ->label('Vendor / Supplier')
                            ->options(fn() => Vendor::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if ($state) {
                                    $this->loadLayoutForSelection((int) $state, (string) ($get('document_type') ?: 'purchase_order'));
                                }
                            }),

                        Select::make('document_type')
                            ->label('Document Type')
                            ->options([
                                'purchase_order' => 'Purchase Order (PO)',
                                'order_slip' => 'Order Slip (OS)',
                                'vendors_agreement' => 'Vendors Agreement Form (Quotation / QT)',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $vendorId = (int) ($get('vendor_id') ?: 0);
                                if ($vendorId && $state) {
                                    $this->loadLayoutForSelection($vendorId, (string) $state);
                                }
                            }),

                        TextInput::make('header_identifier_regex')
                            ->label('Header Identifier Regex')
                            ->placeholder('e.g. /(?:HUENICS|PURCHASE\s*ORDER)/i')
                            ->helperText('Optional regex pattern used to identify this vendor document from OCR headers automatically.')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Configuration Notes')
                            ->placeholder('e.g. Standard layout for vendor quotations with 12% Philippine VAT and multi-line line items.')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Field Extraction Rules')
                    ->description('Define rule-based field extractors, regex patterns, keyword anchors, and post-processing transformation pipelines.')
                    ->icon('heroicon-o-table-cells')
                    ->schema([
                        Repeater::make('mappings')
                            ->label('Extraction Field Mappings')
                            ->addActionLabel('Add Extraction Field Rule')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn(array $state): ?string => 
                                (!empty($state['field_key']) ? $state['field_key'] : 'New Field') . 
                                ' [' . (!empty($state['target_scope']) ? strtoupper($state['target_scope']) : 'HEADER') . ' • ' . 
                                (!empty($state['extraction_strategy']) ? $state['extraction_strategy'] : 'regex') . ']'
                            )
                            ->schema([
                                Select::make('field_key')
                                    ->label('Target Field Key')
                                    ->options([
                                        'document_number'   => 'document_number (PO/QT Number)',
                                        'document_date'     => 'document_date (Issue/Order Date)',
                                        'customer_name'     => 'customer_name (Client / Recipient)',
                                        'customer_company'  => 'customer_company (Client Company)',
                                        'project_name'      => 'project_name (Project Site / Tower)',
                                        'project_location'  => 'project_location (Site Address)',
                                        'phone_no'          => 'phone_no (Contact Number)',
                                        'line_no'           => 'line_no (Item Sequence Number)',
                                        'material_code'     => 'material_code (SKU / Model #)',
                                        'description'       => 'description (Product Description)',
                                        'qty'               => 'qty (Item Quantity)',
                                        'unit'              => 'unit (Unit of Measure: pcs, sets)',
                                        'unit_price'        => 'unit_price (Catalog Unit Price ₱)',
                                        'discounted_price'  => 'discounted_price (Discounted Price ₱)',
                                        'printed_total'     => 'printed_total (Printed Line Total ₱)',
                                        'printed_subtotal'  => 'printed_subtotal (Net Subtotal ₱)',
                                        'printed_vat'       => 'printed_vat (12% Philippine VAT ₱)',
                                        'negotiated_amount' => 'negotiated_amount (Special Rate ₱)',
                                        'custom_field'      => 'custom_field (Custom Metadata)',
                                    ])
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(4),

                                Select::make('target_scope')
                                    ->label('Scope')
                                    ->options([
                                        'header'    => 'Header (Document Metadata)',
                                        'line_item' => 'Line Item (Table Row)',
                                        'totals'    => 'Totals (Summary Block)',
                                    ])
                                    ->default('header')
                                    ->required()
                                    ->columnSpan(3),

                                Select::make('extraction_strategy')
                                    ->label('Strategy')
                                    ->options([
                                        'regex_header'     => 'Regex Header Pattern',
                                        'keyword_offset'   => 'Keyword Anchor Offset',
                                        'column_position'  => 'Column Slicing / Bounds',
                                        'table_row_index'  => 'Row Index Slicing',
                                    ])
                                    ->default('regex_header')
                                    ->required()
                                    ->live()
                                    ->columnSpan(3),

                                Select::make('post_process')
                                    ->label('Transform')
                                    ->options([
                                        'trim'          => 'Trim Whitespace',
                                        'parse_decimal' => 'Parse Decimal Currency (₱)',
                                        'parse_int'     => 'Parse Integer',
                                        'parse_date'    => 'Parse Date (Y-m-d)',
                                        'strip_commas'  => 'Strip Commas',
                                        'uppercase'     => 'Convert to Uppercase',
                                        'none'          => 'Raw / Unmodified',
                                    ])
                                    ->default('trim')
                                    ->columnSpan(2),

                                TextInput::make('regex_pattern')
                                    ->label('Regex Pattern / Keyword Anchor')
                                    ->placeholder('/(?:PO\s*No\.?)\s*[:\.]?\s*([A-Z0-9\-]+)/i')
                                    ->visible(fn($get) => $get('extraction_strategy') !== 'column_position')
                                    ->columnSpan(8),

                                TextInput::make('column_start')
                                    ->label('Col Start')
                                    ->numeric()
                                    ->visible(fn($get) => $get('extraction_strategy') === 'column_position')
                                    ->columnSpan(4),

                                TextInput::make('column_end')
                                    ->label('Col End')
                                    ->numeric()
                                    ->visible(fn($get) => $get('extraction_strategy') === 'column_position')
                                    ->columnSpan(4),

                                TextInput::make('row_offset')
                                    ->label('Row Offset')
                                    ->numeric()
                                    ->placeholder('0')
                                    ->visible(fn($get) => in_array($get('extraction_strategy'), ['keyword_offset', 'table_row_index']))
                                    ->columnSpan(2),

                                Toggle::make('is_required')
                                    ->label('Required')
                                    ->helperText('Strict validation check')
                                    ->default(false)
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(0),
                    ]),
            ]);
    }

    public function loadLayoutForSelection(int $vendorId, string $documentType): void
    {
        $layout = VendorDocumentLayout::where('vendor_id', $vendorId)
            ->where('document_type', $documentType)
            ->latest('layout_version')
            ->with('fieldMappings')
            ->first();

        if ($layout) {
            $mappings = [];
            foreach ($layout->fieldMappings as $m) {
                $mappings[] = [
                    'id'                  => $m->id,
                    'field_key'           => $m->field_key,
                    'target_scope'        => $m->target_scope,
                    'extraction_strategy' => $m->extraction_strategy,
                    'regex_pattern'       => $m->regex_pattern,
                    'column_start'        => $m->column_start,
                    'column_end'          => $m->column_end,
                    'row_offset'          => $m->row_offset,
                    'post_process'        => $m->post_process ?: 'trim',
                    'is_required'         => (bool) $m->is_required,
                ];
            }

            $this->form->fill([
                'vendor_id'               => $vendorId,
                'document_type'           => $documentType,
                'header_identifier_regex' => $layout->header_identifier_regex,
                'notes'                   => $layout->notes,
                'mappings'                => $mappings,
            ]);
        } else {
            $this->form->fill([
                'vendor_id'               => $vendorId,
                'document_type'           => $documentType,
                'header_identifier_regex' => null,
                'notes'                   => 'Default dynamic parser layout',
                'mappings'                => $this->getDefaultMappings(),
            ]);
        }
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $vendorId = (int) ($state['vendor_id'] ?? 0);
        if (!$vendorId) {
            Notification::make()->title('Please select a vendor first.')->warning()->send();
            return;
        }

        $documentType = (string) ($state['document_type'] ?? 'purchase_order');

        DB::transaction(function () use ($state, $vendorId, $documentType) {
            $layout = VendorDocumentLayout::updateOrCreate([
                'vendor_id'      => $vendorId,
                'document_type'  => $documentType,
                'layout_version' => 1,
            ], [
                'is_active'               => true,
                'notes'                   => $state['notes'] ?? null,
                'header_identifier_regex' => $state['header_identifier_regex'] ?? null,
            ]);

            // Keep track of current IDs to prune removed ones
            $existingIds = [];
            foreach ($state['mappings'] ?? [] as $idx => $m) {
                $mapping = $layout->fieldMappings()->updateOrCreate(
                    ['id' => $m['id'] ?? null],
                    [
                        'field_key'           => $m['field_key'],
                        'target_scope'        => $m['target_scope'],
                        'extraction_strategy' => $m['extraction_strategy'],
                        'regex_pattern'       => !empty($m['regex_pattern']) ? $m['regex_pattern'] : null,
                        'column_start'        => isset($m['column_start']) && $m['column_start'] !== '' ? (int) $m['column_start'] : null,
                        'column_end'          => isset($m['column_end']) && $m['column_end'] !== '' ? (int) $m['column_end'] : null,
                        'row_offset'          => isset($m['row_offset']) && $m['row_offset'] !== '' ? (int) $m['row_offset'] : null,
                        'post_process'        => $m['post_process'] ?? 'trim',
                        'is_required'         => (bool) ($m['is_required'] ?? false),
                        'sort_order'          => $idx,
                    ]
                );
                $existingIds[] = $mapping->id;
            }

            // Remove any field mappings that were deleted from the repeater
            $layout->fieldMappings()->whereNotIn('id', $existingIds)->delete();
        });

        Notification::make()
            ->title('Vendor Layout Preset Saved')
            ->body("Configuration for {$documentType} updated successfully.")
            ->success()
            ->send();

        $this->loadLayoutForSelection($vendorId, $documentType);
    }

    public function resetToDefaults(): void
    {
        $state = $this->form->getState();
        $vendorId = (int) ($state['vendor_id'] ?? 0);
        $documentType = (string) ($state['document_type'] ?? 'purchase_order');

        $this->form->fill([
            'vendor_id'               => $vendorId,
            'document_type'           => $documentType,
            'header_identifier_regex' => null,
            'notes'                   => 'Default dynamic parser layout',
            'mappings'                => $this->getDefaultMappings(),
        ]);

        Notification::make()
            ->title('Reset to Default Rules')
            ->body('Extraction rules reset to standard templates. Click Save to apply.')
            ->info()
            ->send();
    }

    protected function getDefaultMappings(): array
    {
        return [
            [
                'field_key'           => 'document_number',
                'target_scope'        => 'header',
                'extraction_strategy' => 'regex_header',
                'regex_pattern'       => '/(?:PO\s*No\.?|P\.?O\.?\s*\#?|S\.?O\.?\s*\#?|Quotation\s*NO\.?|Quote\s*\#?)\s*[:\.]?\s*([A-Z0-9\-\_\s]+)/i',
                'column_start'        => null,
                'column_end'          => null,
                'row_offset'          => null,
                'post_process'        => 'trim',
                'is_required'         => true,
            ],
            [
                'field_key'           => 'document_date',
                'target_scope'        => 'header',
                'extraction_strategy' => 'regex_header',
                'regex_pattern'       => '/(?:Date|Dated)\s*[:\.]?\s*([0-9\/\\-\.]+)/i',
                'column_start'        => null,
                'column_end'          => null,
                'row_offset'          => null,
                'post_process'        => 'parse_date',
                'is_required'         => false,
            ],
            [
                'field_key'           => 'printed_subtotal',
                'target_scope'        => 'totals',
                'extraction_strategy' => 'keyword_offset',
                'regex_pattern'       => '/(?:subtotal|sub-total)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i',
                'column_start'        => null,
                'column_end'          => null,
                'row_offset'          => null,
                'post_process'        => 'parse_decimal',
                'is_required'         => false,
            ],
            [
                'field_key'           => 'printed_vat',
                'target_scope'        => 'totals',
                'extraction_strategy' => 'keyword_offset',
                'regex_pattern'       => '/(?:12\%\s*VAT|VAT)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i',
                'column_start'        => null,
                'column_end'          => null,
                'row_offset'          => null,
                'post_process'        => 'parse_decimal',
                'is_required'         => false,
            ],
            [
                'field_key'           => 'printed_total',
                'target_scope'        => 'totals',
                'extraction_strategy' => 'keyword_offset',
                'regex_pattern'       => '/(?:grand\s*total|total\s*amount)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i',
                'column_start'        => null,
                'column_end'          => null,
                'row_offset'          => null,
                'post_process'        => 'parse_decimal',
                'is_required'         => false,
            ],
        ];
    }
}
