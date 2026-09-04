<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryReportService
{
    /**
     * Export inventory items into standard CSV matching reference Inventory Report.
     */
    public function exportInventoryReport(?iterable $items = null): string
    {
        $items = $items ?: InventoryItem::with(['product', 'movements' => fn ($q) => $q->latest('created_at')->limit(5)])
            ->join('products', 'inventory_items.product_id', '=', 'products.id')
            ->select('inventory_items.*')
            ->orderBy('products.canonical_name')
            ->get();

        $output = fopen('php://temp', 'r+');

        // Prepend UTF-8 BOM to prevent Excel and Windows CSV decoders from throwing malformed UTF-8 errors
        fwrite($output, "\xEF\xBB\xBF");

        // CSV Header matching exact reference Inventory Report
        fputcsv($output, $this->sanitizeCsvRow([
            'Date',
            'P.O. Nos.',
            'Suppliers Name',
            'S.K.U.',
            'Item Code',
            'Pictures',
            'Particulars',
            'Transit In',
            'Transit Out',
            'Balance',
            'Location',
            'Customer Name',
            'Project Name',
            'Date Released',
            'Remarks',
        ]));

        foreach ($items as $item) {
            $product = $item->product;

            $dateStr = $item->inbound_date 
                ? $item->inbound_date->format('m/d/Y') 
                : ($item->created_at ? $item->created_at->format('m/d/Y') : '');

            $dateReleasedStr = $item->date_released ? $item->date_released->format('m/d/Y') : '';

            $latestIn = $item->movements
                ?->whereIn('transaction_type', ['initial_stock', 'purchase_in', 'adjustment_up'])
                ->first();

            $latestOut = $item->movements
                ?->whereIn('transaction_type', ['sales_out', 'component_deduct', 'adjustment_down'])
                ->first();

            $transitIn = $latestIn ? (float) ($latestIn->transit_in ?: $latestIn->quantity) : '';
            $transitOut = $latestOut ? (float) ($latestOut->transit_out ?: $latestOut->quantity) : '';

            $balance = (float) $item->quantity_on_hand;

            fputcsv($output, $this->sanitizeCsvRow([
                $dateStr,
                $item->po_number ?: '',
                $item->supplier_name ?: '',
                $product?->sku ?: '',
                $product?->product_code ?: '',
                $product?->image_path ?: '',
                $product?->canonical_name ?: ($product?->description ?: ''),
                $transitIn !== '' ? $transitIn : '',
                $transitOut !== '' ? $transitOut : '',
                $balance,
                $item->location ?: '',
                $item->customer_name ?: '',
                $item->project_name ?: '',
                $dateReleasedStr,
                $item->remarks ?: '',
            ]));
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Generate sample CSV template preloaded with real reference entries.
     */
    public function generateSampleInventoryCsv(): string
    {
        $output = fopen('php://temp', 'r+');

        // Prepend UTF-8 BOM
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, $this->sanitizeCsvRow([
            'Date',
            'P.O. Nos.',
            'Suppliers Name',
            'S.K.U.',
            'Item Code',
            'Pictures',
            'Particulars',
            'Transit In',
            'Transit Out',
            'Balance',
            'Location',
            'Customer Name',
            'Project Name',
            'Date Released',
            'Remarks',
        ]));

        $samples = [
            [
                '12/01/2024',
                '',
                'SUPREME COMPONENTS INTL PTE.LTD',
                'CLU028-1204C4-303M2KI',
                'CLU028-1204C4-303M2KI',
                '',
                'CITIZEN CLU028-3000K, CRI80, STANDARD, VER. 5',
                '22',
                '',
                '22',
                'Mam CBS ROOM INSIDE CABINET',
                '',
                '',
                '',
                '',
            ],
            [
                '01/06/2023',
                '2022-3263',
                'SUPREME COMPONENTS INTL PTE.LTD',
                'CLU028-1204C4-303M2M2KI',
                'CLU028-1204C4-303M2M2KI',
                '',
                'CITIZEN CLU028-3000K, CRI80, STANDARD, VER. 6',
                '5',
                '',
                '5',
                'Mam CBS ROOM INSIDE CABINET',
                '',
                '',
                '',
                '',
            ],
            [
                '01/18/2024',
                '241000010-M',
                'SUPREME COMPONENTS INTL PTE.LTD',
                'CLU028-1204C4-353H5M3-F1',
                'CLU028-1204C4-353H5M3-F1',
                '',
                'CITIZEN CLU028-3500K, CRI90, STANDARD, VER. 6',
                '74',
                '2',
                '72',
                'Mam CBS ROOM INSIDE CABINET',
                'FOOTACTION INTERNATIONAL MANUFACTURING CORP.',
                'FAIRVIEW MERRELL STORE',
                '01/24/2024',
                'COMPLETE DELIVERY',
            ],
            [
                '01/15/2024',
                '23300212',
                'SERIAL MICROELECTRONICS PTE.,LTD',
                'LCP12-A4S301C',
                'LCP12-A4301C',
                '',
                'HYPERION, 300 MA, 12W, 30-42Vdc',
                '92',
                '',
                '92',
                'TECHNICAL ROOM SHELF 3 LEVEL 2',
                '',
                '',
                '',
                '',
            ],
            [
                '04/05/2023',
                '2023-0146',
                'NORTHERN HORIZON LIGHT CORP.',
                'LP-36W',
                'LP-36W',
                '',
                'LED POWERSUPPLY 12V, 36W',
                '50',
                '',
                '50',
                'SHELF 1 ROW 5',
                '',
                '',
                '',
                '',
            ],
        ];

        foreach ($samples as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Import Inventory Report CSV file into the database.
     */
    public function importInventoryReport(string $filePath, bool $updateExisting = true): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException("Inventory report CSV file does not exist or is not readable: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Failed to open inventory report file: {$filePath}");
        }

        $importedCount = 0;
        $updatedCount  = 0;
        $skippedCount  = 0;
        $errors        = [];
        $headerMap     = null;
        $rowIndex      = 0;

        while (($row = fgetcsv($handle, 4096, ',')) !== false) {
            $rowIndex++;
            $trimmedRow = array_map('trim', $row);

            // Skip completely empty rows
            if (empty(array_filter($trimmedRow))) {
                continue;
            }

            // Identify header row
            if ($headerMap === null) {
                $possibleHeader = $this->parseHeaderRow($trimmedRow);
                if ($possibleHeader !== null) {
                    $headerMap = $possibleHeader;
                    continue;
                }
            }

            // Fallback default map if header row wasn't present
            if ($headerMap === null) {
                $headerMap = [
                    'date'          => 0,
                    'po_number'     => 1,
                    'supplier_name' => 2,
                    'sku'           => 3,
                    'item_code'     => 4,
                    'picture'       => 5,
                    'particulars'   => 6,
                    'transit_in'    => 7,
                    'transit_out'   => 8,
                    'balance'       => 9,
                    'location'      => 10,
                    'customer_name' => 11,
                    'project_name'  => 12,
                    'date_released' => 13,
                    'remarks'       => 14,
                ];
            }

            $record = $this->extractRecordFromRow($trimmedRow, $headerMap);

            if (empty($record['item_code']) && empty($record['sku']) && empty($record['particulars'])) {
                $skippedCount++;
                continue;
            }

            try {
                DB::transaction(function () use ($record, $updateExisting, &$importedCount, &$updatedCount) {
                    $product = null;

                    // Match product by code or SKU
                    if (!empty($record['item_code'])) {
                        $product = Product::where('product_code', $record['item_code'])->first();
                    }

                    if (!$product && !empty($record['sku'])) {
                        $product = Product::where('sku', $record['sku'])->orWhere('product_code', $record['sku'])->first();
                    }

                    if (!$product && !empty($record['particulars'])) {
                        $product = Product::where('canonical_name', $record['particulars'])->first();
                    }

                    if (!$product) {
                        $code = $record['item_code'] ?: ($record['sku'] ?: ('PRD-' . strtoupper(substr(uniqid(), -6))));
                        $name = $record['particulars'] ?: $code;

                        $product = Product::create([
                            'product_code'      => $code,
                            'sku'               => $record['sku'] ?: $code,
                            'canonical_name'    => $name,
                            'description'       => $record['particulars'] ?: $name,
                            'category'          => 'General',
                            'unit_default'      => 'pcs',
                            'default_price'     => 0.00,
                            'selling_price'     => 0.00,
                            'base_cost_price'   => 0.00,
                            'image_path'        => $record['picture'],
                            'is_huenics_owned'  => true,
                            'is_active'         => true,
                        ]);
                    } else {
                        if ($updateExisting) {
                            $product->update([
                                'sku'        => $record['sku'] ?: $product->sku,
                                'image_path' => $record['picture'] ?: $product->image_path,
                            ]);
                        }
                    }

                    // Locate or create InventoryItem
                    $invItem = InventoryItem::where('product_id', $product->id)->first();
                    $isNewItem = false;

                    if (!$invItem) {
                        $invItem = new InventoryItem();
                        $invItem->product_id = $product->id;
                        $invItem->quantity_on_hand = 0;
                        $invItem->quantity_reserved = 0;
                        $invItem->reorder_point = 10;
                        $invItem->unit = $product->unit_default ?: 'pcs';
                        $isNewItem = true;
                    }

                    if ($isNewItem || $updateExisting) {
                        if ($record['balance'] !== null) {
                            $invItem->quantity_on_hand = $record['balance'];
                        } elseif ($record['transit_in'] !== null) {
                            $invItem->quantity_on_hand = (float) $invItem->quantity_on_hand + $record['transit_in'];
                        }

                        if (!empty($record['location'])) {
                            $invItem->location = $record['location'];
                        }
                        if (!empty($record['supplier_name'])) {
                            $invItem->supplier_name = $record['supplier_name'];
                        }
                        if (!empty($record['po_number'])) {
                            $invItem->po_number = $record['po_number'];
                        }
                        if (!empty($record['customer_name'])) {
                            $invItem->customer_name = $record['customer_name'];
                        }
                        if (!empty($record['project_name'])) {
                            $invItem->project_name = $record['project_name'];
                        }
                        if (!empty($record['date_released'])) {
                            $invItem->date_released = $record['date_released'];
                        }
                        if (!empty($record['inbound_date'])) {
                            $invItem->inbound_date = $record['inbound_date'];
                        }
                        if (!empty($record['remarks'])) {
                            $invItem->remarks = $record['remarks'];
                        }

                        $invItem->save();

                        // Log transaction entry
                        $performedById = auth()->id() ?? User::first()?->id ?? 1;
                        $qty = $record['transit_in'] ?: ($record['transit_out'] ?: (float) $invItem->quantity_on_hand);
                        $type = $record['transit_out'] ? 'sales_out' : ($record['transit_in'] ? 'purchase_in' : 'initial_stock');

                        InventoryTransaction::create([
                            'inventory_item_id' => $invItem->id,
                            'transaction_type'  => $type,
                            'reference_type'    => !empty($record['po_number']) ? 'po' : 'import',
                            'quantity'          => $qty > 0 ? $qty : 0,
                            'po_number'         => $record['po_number'],
                            'supplier_name'     => $record['supplier_name'],
                            'customer_name'     => $record['customer_name'],
                            'project_name'      => $record['project_name'],
                            'location'          => $record['location'],
                            'date_released'     => $record['date_released'],
                            'transit_in'        => $record['transit_in'],
                            'transit_out'       => $record['transit_out'],
                            'balance_after'     => $invItem->quantity_on_hand,
                            'notes'             => $record['remarks'] ?: "Imported from inventory report: {$record['particulars']}",
                            'performed_by'      => $performedById,
                            'created_at'        => $record['inbound_date'] ? Carbon::parse($record['inbound_date']) : now(),
                        ]);

                        if ($isNewItem) {
                            $importedCount++;
                        } else {
                            $updatedCount++;
                        }
                    }
                });
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowIndex}: " . $e->getMessage();
                Log::warning("Inventory import error at row {$rowIndex}: " . $e->getMessage());
            }
        }

        fclose($handle);

        return [
            'imported' => $importedCount,
            'updated'  => $updatedCount,
            'skipped'  => $skippedCount,
            'errors'   => $errors,
        ];
    }

    /**
     * Map CSV header row to columns.
     */
    protected function parseHeaderRow(array $row): ?array
    {
        $map = [];
        $matched = 0;

        foreach ($row as $idx => $cell) {
            $col = preg_replace('/[^A-Z0-9]/', '', strtoupper($cell));

            if (in_array($col, ['DATE', 'INBOUNDDATE', 'LOGDATE', 'RECEIVEDDATE'])) {
                $map['date'] = $idx;
                $matched++;
            } elseif (in_array($col, ['PONOS', 'PONO', 'PONUMBER', 'PURCHASEORDER', 'PO'])) {
                $map['po_number'] = $idx;
                $matched++;
            } elseif (in_array($col, ['SUPPLIERSNAME', 'SUPPLIER', 'SUPPLIERNAME', 'VENDOR'])) {
                $map['supplier_name'] = $idx;
                $matched++;
            } elseif (in_array($col, ['SKU', 'SKUNO', 'ITEMSKU'])) {
                $map['sku'] = $idx;
                $matched++;
            } elseif (in_array($col, ['ITEMCODE', 'CODE', 'PRODUCTCODE', 'MODEL'])) {
                $map['item_code'] = $idx;
                $matched++;
            } elseif (in_array($col, ['PICTURES', 'PICTURE', 'IMAGE', 'PHOTO'])) {
                $map['picture'] = $idx;
                $matched++;
            } elseif (in_array($col, ['PARTICULARS', 'DESCRIPTION', 'PRODUCTNAME', 'ITEMNAME'])) {
                $map['particulars'] = $idx;
                $matched++;
            } elseif (in_array($col, ['TRANSITIN', 'IN', 'RECEIVED', 'STOCKIN'])) {
                $map['transit_in'] = $idx;
                $matched++;
            } elseif (in_array($col, ['TRANSITOUT', 'OUT', 'RELEASED', 'STOCKOUT'])) {
                $map['transit_out'] = $idx;
                $matched++;
            } elseif (in_array($col, ['BALANCE', 'ONHAND', 'STOCK', 'QTY', 'QUANTITY'])) {
                $map['balance'] = $idx;
                $matched++;
            } elseif (in_array($col, ['LOCATION', 'STORAGE', 'SHELF', 'WAREHOUSE'])) {
                $map['location'] = $idx;
                $matched++;
            } elseif (in_array($col, ['CUSTOMERNAME', 'CUSTOMER', 'CLIENT'])) {
                $map['customer_name'] = $idx;
                $matched++;
            } elseif (in_array($col, ['PROJECTNAME', 'PROJECT', 'SITE'])) {
                $map['project_name'] = $idx;
                $matched++;
            } elseif (in_array($col, ['DATERELEASED', 'RELEASEDATE'])) {
                $map['date_released'] = $idx;
                $matched++;
            } elseif (in_array($col, ['REMARKS', 'NOTES', 'COMMENT'])) {
                $map['remarks'] = $idx;
                $matched++;
            }
        }

        return $matched >= 3 ? $map : null;
    }

    /**
     * Extract structured record from CSV row.
     */
    protected function extractRecordFromRow(array $row, array $map): array
    {
        $rawDate = isset($map['date']) && isset($row[$map['date']]) ? trim($row[$map['date']]) : null;
        $rawPo = isset($map['po_number']) && isset($row[$map['po_number']]) ? trim($row[$map['po_number']]) : null;
        $rawSupplier = isset($map['supplier_name']) && isset($row[$map['supplier_name']]) ? trim($row[$map['supplier_name']]) : null;
        $rawSku = isset($map['sku']) && isset($row[$map['sku']]) ? trim($row[$map['sku']]) : null;
        $rawCode = isset($map['item_code']) && isset($row[$map['item_code']]) ? trim($row[$map['item_code']]) : null;
        $rawPic = isset($map['picture']) && isset($row[$map['picture']]) ? trim($row[$map['picture']]) : null;
        $rawDesc = isset($map['particulars']) && isset($row[$map['particulars']]) ? trim($row[$map['particulars']]) : null;
        $rawIn = isset($map['transit_in']) && isset($row[$map['transit_in']]) ? trim($row[$map['transit_in']]) : null;
        $rawOut = isset($map['transit_out']) && isset($row[$map['transit_out']]) ? trim($row[$map['transit_out']]) : null;
        $rawBal = isset($map['balance']) && isset($row[$map['balance']]) ? trim($row[$map['balance']]) : null;
        $rawLoc = isset($map['location']) && isset($row[$map['location']]) ? trim($row[$map['location']]) : null;
        $rawCust = isset($map['customer_name']) && isset($row[$map['customer_name']]) ? trim($row[$map['customer_name']]) : null;
        $rawProj = isset($map['project_name']) && isset($row[$map['project_name']]) ? trim($row[$map['project_name']]) : null;
        $rawRelDate = isset($map['date_released']) && isset($row[$map['date_released']]) ? trim($row[$map['date_released']]) : null;
        $rawRemarks = isset($map['remarks']) && isset($row[$map['remarks']]) ? trim($row[$map['remarks']]) : null;

        $parseDate = function (?string $val): ?string {
            if (empty($val)) return null;
            try {
                return Carbon::parse($val)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        };

        $parseNum = function (?string $val): ?float {
            if ($val === null || $val === '' || str_starts_with($val, '#')) return null;
            $clean = preg_replace('/[^0-9.-]/', '', str_replace(',', '', $val));
            return is_numeric($clean) ? (float) $clean : null;
        };

        return [
            'inbound_date'  => $parseDate($rawDate),
            'po_number'     => $rawPo ?: null,
            'supplier_name' => $rawSupplier ?: null,
            'sku'           => $rawSku ?: null,
            'item_code'     => $rawCode ?: null,
            'picture'       => $rawPic ?: null,
            'particulars'   => $rawDesc ?: null,
            'transit_in'    => $parseNum($rawIn),
            'transit_out'   => $parseNum($rawOut),
            'balance'       => $parseNum($rawBal),
            'location'      => $rawLoc ?: null,
            'customer_name' => $rawCust ?: null,
            'project_name'  => $rawProj ?: null,
            'date_released' => $parseDate($rawRelDate),
            'remarks'       => $rawRemarks ?: null,
        ];
    }

    /**
     * Sanitize array values to guaranteed valid UTF-8 strings.
     */
    private function sanitizeCsvRow(array $row): array
    {
        return array_map(function ($value) {
            if ($value === null) {
                return '';
            }
            $str = (string) $value;
            if (!mb_check_encoding($str, 'UTF-8')) {
                $str = mb_convert_encoding($str, 'UTF-8', 'ISO-8859-1');
            }
            return $str;
        }, $row);
    }
}
