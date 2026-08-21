<?php

namespace App\Filament\Pages;

use App\Models\Vendor;
use App\Models\VendorDocumentLayout;
use App\Models\VendorLayoutFieldMapping;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class VendorLayoutEditorPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static \UnitEnum|string|null $navigationGroup = 'Master Data & Registry';
    protected static ?string $navigationLabel = 'Vendor Layout Mappings';
    protected static ?string $title = 'Dynamic Vendor Layout Configurator';
    protected string $view = 'filament.pages.vendor-layout-editor-page';
    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return auth()->user()?->canConfigureLayouts() ?? true;
    }

    public ?int $selectedVendorId = null;
    public string $selectedDocumentType = 'purchase_order';
    public ?int $selectedLayoutId = null;

    public array $mappings = [];
    public ?string $notes = null;
    public ?string $headerRegex = null;

    public function mount(): void
    {
        $this->selectedVendorId = Vendor::first()?->id;
        $this->loadLayout();
    }

    public function loadLayout(): void
    {
        if (!$this->selectedVendorId) {
            return;
        }

        $layout = VendorDocumentLayout::where('vendor_id', $this->selectedVendorId)
            ->where('document_type', $this->selectedDocumentType)
            ->latest('layout_version')
            ->with('fieldMappings')
            ->first();

        if ($layout) {
            $this->selectedLayoutId = $layout->id;
            $this->notes = $layout->notes;
            $this->headerRegex = $layout->header_identifier_regex;
            $this->mappings = [];

            foreach ($layout->fieldMappings as $m) {
                $this->mappings[] = [
                    'id' => $m->id,
                    'field_key' => $m->field_key,
                    'target_scope' => $m->target_scope,
                    'extraction_strategy' => $m->extraction_strategy,
                    'regex_pattern' => $m->regex_pattern,
                    'column_start' => $m->column_start,
                    'column_end' => $m->column_end,
                    'row_offset' => $m->row_offset,
                    'post_process' => $m->post_process,
                    'is_required' => (bool) $m->is_required,
                ];
            }
        } else {
            $this->selectedLayoutId = null;
            $this->notes = 'Default dynamic parser layout';
            $this->headerRegex = null;
            $this->mappings = $this->getDefaultMappings();
        }
    }

    public function updatedSelectedVendorId(): void
    {
        $this->loadLayout();
    }

    public function updatedSelectedDocumentType(): void
    {
        $this->loadLayout();
    }

    public function addMapping(): void
    {
        $this->mappings[] = [
            'id' => null,
            'field_key' => 'custom_field',
            'target_scope' => 'header',
            'extraction_strategy' => 'regex_header',
            'regex_pattern' => '',
            'column_start' => null,
            'column_end' => null,
            'row_offset' => null,
            'post_process' => 'trim',
            'is_required' => false,
        ];
    }

    public function removeMapping(int $index): void
    {
        if (isset($this->mappings[$index])) {
            $id = $this->mappings[$index]['id'] ?? null;
            if ($id) {
                VendorLayoutFieldMapping::destroy($id);
            }
            unset($this->mappings[$index]);
            $this->mappings = array_values($this->mappings);
        }
    }

    public function saveLayout(): void
    {
        if (!$this->selectedVendorId) {
            Notification::make()->title('Please select a vendor first.')->warning()->send();
            return;
        }

        $layout = VendorDocumentLayout::updateOrCreate([
            'vendor_id' => $this->selectedVendorId,
            'document_type' => $this->selectedDocumentType,
            'layout_version' => 1,
        ], [
            'is_active' => true,
            'notes' => $this->notes,
            'header_identifier_regex' => $this->headerRegex,
        ]);

        $this->selectedLayoutId = $layout->id;

        // Sync field mappings
        foreach ($this->mappings as $idx => $m) {
            $layout->fieldMappings()->updateOrCreate(
                ['id' => $m['id'] ?? null],
                [
                    'field_key' => $m['field_key'],
                    'target_scope' => $m['target_scope'],
                    'extraction_strategy' => $m['extraction_strategy'],
                    'regex_pattern' => $m['regex_pattern'] ?: null,
                    'column_start' => $m['column_start'] !== '' ? $m['column_start'] : null,
                    'column_end' => $m['column_end'] !== '' ? $m['column_end'] : null,
                    'row_offset' => $m['row_offset'] !== '' ? $m['row_offset'] : null,
                    'post_process' => $m['post_process'],
                    'is_required' => (bool) ($m['is_required'] ?? false),
                    'sort_order' => $idx,
                ]
            );
        }

        Notification::make()->title('Vendor layout configuration saved!')->success()->send();
        $this->loadLayout();
    }

    protected function getDefaultMappings(): array
    {
        return [
            [
                'id' => null,
                'field_key' => 'document_number',
                'target_scope' => 'header',
                'extraction_strategy' => 'regex_header',
                'regex_pattern' => '/(?:PO\s*No\.?|P\.?O\.?\s*\#?|S\.?O\.?\s*\#?|Quotation\s*\#?)\s*[:\.]?\s*([A-Z0-9\-\_]+)/i',
                'column_start' => null,
                'column_end' => null,
                'row_offset' => null,
                'post_process' => 'trim',
                'is_required' => true,
            ],
            [
                'id' => null,
                'field_key' => 'document_date',
                'target_scope' => 'header',
                'extraction_strategy' => 'regex_header',
                'regex_pattern' => '/(?:Date|Dated)\s*[:\.]?\s*([0-9\/\\-\.]+)/i',
                'column_start' => null,
                'column_end' => null,
                'row_offset' => null,
                'post_process' => 'parse_date',
                'is_required' => false,
            ],
            [
                'id' => null,
                'field_key' => 'printed_subtotal',
                'target_scope' => 'totals',
                'extraction_strategy' => 'keyword_offset',
                'regex_pattern' => '/(?:subtotal|sub-total)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i',
                'column_start' => null,
                'column_end' => null,
                'row_offset' => null,
                'post_process' => 'parse_decimal',
                'is_required' => false,
            ],
            [
                'id' => null,
                'field_key' => 'printed_vat',
                'target_scope' => 'totals',
                'extraction_strategy' => 'keyword_offset',
                'regex_pattern' => '/(?:12\%\s*VAT|VAT)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i',
                'column_start' => null,
                'column_end' => null,
                'row_offset' => null,
                'post_process' => 'parse_decimal',
                'is_required' => false,
            ],
            [
                'id' => null,
                'field_key' => 'printed_total',
                'target_scope' => 'totals',
                'extraction_strategy' => 'keyword_offset',
                'regex_pattern' => '/(?:grand\s*total|total\s*amount)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i',
                'column_start' => null,
                'column_end' => null,
                'row_offset' => null,
                'post_process' => 'parse_decimal',
                'is_required' => false,
            ],
        ];
    }

    public function getVendorsProperty(): array
    {
        return Vendor::pluck('name', 'id')->toArray();
    }
}
