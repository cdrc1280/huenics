<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductImportExportService
{
    /**
     * Export products into CSV format matching the reference pricelist.
     */
    public function exportCsv(?iterable $products = null): string
    {
        $products = $products ?: Product::with('inventoryItem')->orderBy('category')->orderBy('product_code')->get();

        $output = fopen('php://temp', 'r+');

        // CSV Header matching PRICELIST 2024 Reference
        fputcsv($output, [
            'CATEGORY',
            'PICTURE',
            'CODE',
            'CANONICAL_NAME',
            'WATTAGE',
            'DESCRIPTION',
            'VOLTAGE',
            'COLOR',
            'PRICE',
            'UNIT',
            'STOCK_ON_HAND',
        ]);

        foreach ($products as $product) {
            $priceFormatted = number_format((float) ($product->selling_price ?: $product->default_price), 2, '.', '');
            $unit = strtoupper($product->unit_default ?: 'pcs');

            fputcsv($output, [
                $product->category ?: 'General',
                $product->image_path ?: '',
                $product->product_code ?: '',
                $product->canonical_name ?: '',
                $product->wattage ?: '',
                $product->description ?: '',
                $product->voltage ?: '',
                $product->color_temperature ?: '',
                $priceFormatted,
                $unit,
                $product->inventoryItem?->quantity_on_hand ?? 0,
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Generate a sample CSV import template matching PRICELIST 2024.
     */
    public function generateSampleCsvTemplate(): string
    {
        $output = fopen('php://temp', 'r+');

        fputcsv($output, [
            'CATEGORY',
            'PICTURE',
            'CODE',
            'CANONICAL_NAME',
            'WATTAGE',
            'DESCRIPTION',
            'VOLTAGE',
            'COLOR',
            'PRICE',
            'UNIT',
        ]);

        // Sample reference rows from PRICELIST 2024
        $samples = [
            [
                'SMD LED STRIP LIGHT INDOOR',
                '',
                'HISI-LS-9.6W',
                'SMD LED Strip Light 9.6W/M Indoor',
                '9.6W/M',
                'SMD LED STRIPS SIZE 2835, 120PCS LED/M, IP20 INDOOR',
                'DC12V',
                '3000K/6000K',
                '850.00',
                'ROLL',
            ],
            [
                'SMD LED STRIP LIGHT INDOOR',
                '',
                'HISI-LS-14.4W',
                'SMD LED Strip Light 14.4W/M Indoor',
                '14.4W/M',
                'SMD LED STRIPS SIZE 5050, 60PCS LED/M, IP20 INDOOR',
                'DC12V',
                '3000K/6000K',
                '950.00',
                'ROLL',
            ],
            [
                'COB LED STRIP LIGHT',
                '',
                'HISI-LS-COB-12W',
                'LED COB Strip Light 12W/M Indoor',
                '12W/M',
                'LED COB STRIPS SIZE 5050, 60PCS LED/M, IP20 INDOOR',
                'DC12V',
                '3000K/6000K/4000K',
                '1950.00',
                'ROLL',
            ],
            [
                'SMD LED STRIP LIGHT OUTDOOR',
                '',
                'HISI-LS-2835',
                'SMD LED Strip Light 4.8W/M Outdoor',
                '4.8W/M',
                'SMD LED STRIPS SIZE 2835, 60PCS LED/M, IP20 OUTDOOR',
                'DC12V',
                '3000K/6000K',
                '700.00',
                'ROLL',
            ],
            [
                'SMD LED STRIP LIGHT INDOOR 220V',
                '',
                'HISI-LS-8W',
                'SMD LED Strip Light 8W/M 220V Indoor',
                '8W/M',
                'SMD LED STRIPS SIZE 5050, 60PCS LED/M, 220V INDOOR',
                '220V',
                '3000K/6000K',
                '150.00',
                'METER',
            ],
            [
                'LED NEON FLEX',
                '',
                'HISI-LS-SC-NEON-',
                'LED Neon Flex Single Color 9.6W/M 220V',
                '9.6W/M',
                'LED NEON FLEX SINGLE COLOR',
                '220V',
                '3000K/6000K',
                '200.00',
                'M',
            ],
            [
                'SMD LED STRIP LIGHT ACCESSORIES',
                '',
                'HISI-PLUG',
                'Power Cable for LED Striplight',
                'XXX',
                'POWER CABLE FOR LED STRIPLIGHT',
                'XXX',
                'XXX',
                '250.00',
                'SET',
            ],
        ];

        foreach ($samples as $sample) {
            fputcsv($output, $sample);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Import products from an uploaded CSV file.
     * Supports flexible column mapping and category group headers.
     */
    public function importCsv(string $filePath, bool $updateExisting = true): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \InvalidArgumentException("CSV file does not exist or is not readable: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException("Failed to open CSV file: {$filePath}");
        }

        $importedCount = 0;
        $updatedCount  = 0;
        $skippedCount  = 0;
        $errors        = [];
        $currentCategory = 'General';

        $headerMap = null;
        $rowIndex = 0;

        while (($row = fgetcsv($handle, 4096, ',')) !== false) {
            $rowIndex++;

            // Clean values
            $trimmedRow = array_map('trim', $row);

            // Skip completely blank rows
            if (empty(array_filter($trimmedRow))) {
                continue;
            }

            // Detect Header row if not already found
            if ($headerMap === null) {
                $possibleHeader = $this->parseHeaderRow($trimmedRow);
                if ($possibleHeader !== null) {
                    $headerMap = $possibleHeader;
                    continue;
                }
            }

            // Check if this row is a Category Section Header (e.g. "SMD LED STRIP LIGHT INDOOR" with empty other cells)
            $firstCell = strtoupper($trimmedRow[0] ?? '');
            $secondCell = strtoupper($trimmedRow[1] ?? '');
            $nonEmptyCount = count(array_filter($trimmedRow));

            if ($nonEmptyCount <= 2 && !empty($firstCell) && !str_starts_with($firstCell, 'HISI-') && !is_numeric(str_replace([',', '.'], '', $firstCell))) {
                $currentCategory = $firstCell;
                continue;
            }

            if ($headerMap === null) {
                // Fallback default map if no explicit header row was present
                $headerMap = [
                    'code'        => 1,
                    'wattage'     => 2,
                    'description' => 3,
                    'voltage'     => 4,
                    'color'       => 5,
                    'price'       => 6,
                ];
            }

            $record = $this->extractRecordFromRow($trimmedRow, $headerMap, $currentCategory);

            if (empty($record['product_code']) && empty($record['canonical_name']) && empty($record['description'])) {
                $skippedCount++;
                continue;
            }

            try {
                DB::transaction(function () use ($record, $updateExisting, &$importedCount, &$updatedCount) {
                    $product = null;

                    if (!empty($record['product_code'])) {
                        $product = Product::where('product_code', $record['product_code'])->first();
                    }

                    if (!$product && !empty($record['canonical_name'])) {
                        $product = Product::where('canonical_name', $record['canonical_name'])->first();
                    }

                    if ($product) {
                        if ($updateExisting) {
                            $product->update([
                                'canonical_name'    => $record['canonical_name'] ?: $product->canonical_name,
                                'description'       => $record['description'] ?: $product->description,
                                'sku'               => $record['sku'] ?: $product->sku,
                                'category'          => $record['category'] ?: $product->category,
                                'wattage'           => $record['wattage'] ?: $product->wattage,
                                'voltage'           => $record['voltage'] ?: $product->voltage,
                                'color_temperature' => $record['color_temperature'] ?: $product->color_temperature,
                                'unit_default'      => $record['unit_default'] ?: $product->unit_default,
                                'selling_price'     => $record['price'] > 0 ? $record['price'] : $product->selling_price,
                                'default_price'     => $record['price'] > 0 ? $record['price'] : $product->default_price,
                                'image_path'        => $record['image_path'] ?: $product->image_path,
                                'is_active'         => true,
                            ]);

                            if ($record['stock'] !== null && $product->inventoryItem) {
                                $product->inventoryItem->update([
                                    'quantity_on_hand' => $record['stock'],
                                ]);
                            }

                            $updatedCount++;
                        }
                    } else {
                        $code = $record['product_code'] ?: ('PRD-' . strtoupper(substr(uniqid(), -6)));
                        $name = $record['canonical_name'] ?: ($record['description'] ?: $code);

                        $product = Product::create([
                            'product_code'      => $code,
                            'canonical_name'    => $name,
                            'description'       => $record['description'] ?: $name,
                            'sku'               => $record['sku'] ?: null,
                            'category'          => $record['category'] ?: 'General',
                            'wattage'           => $record['wattage'],
                            'voltage'           => $record['voltage'],
                            'color_temperature' => $record['color_temperature'],
                            'unit_default'      => $record['unit_default'] ?: 'pcs',
                            'selling_price'     => $record['price'],
                            'default_price'     => $record['price'],
                            'base_cost_price'   => round($record['price'] * 0.7, 2),
                            'image_path'        => $record['image_path'],
                            'is_huenics_owned'  => true,
                            'is_active'         => true,
                        ]);

                        // Initialize inventory item
                        InventoryItem::firstOrCreate(
                            ['product_id' => $product->id],
                            [
                                'quantity_on_hand'  => $record['stock'] ?? 0,
                                'quantity_reserved' => 0,
                                'reorder_point'     => 10,
                                'unit'              => $product->unit_default,
                            ]
                        );

                        $importedCount++;
                    }
                });
            } catch (\Throwable $e) {
                $errors[] = "Row {$rowIndex}: " . $e->getMessage();
                Log::warning("Product import error at row {$rowIndex}: " . $e->getMessage());
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
     * Map CSV header row to recognized column indices.
     */
    protected function parseHeaderRow(array $row): ?array
    {
        $map = [];
        $matched = 0;

        foreach ($row as $idx => $cell) {
            $col = preg_replace('/[^A-Z0-9]/', '', strtoupper($cell));

            if (in_array($col, ['CODE', 'ITEMCODE', 'PRODUCTCODE', 'MODEL'])) {
                $map['code'] = $idx;
                $matched++;
            } elseif (in_array($col, ['SKU', 'SKUNO', 'ITEMSKU'])) {
                $map['sku'] = $idx;
                $matched++;
            } elseif (in_array($col, ['WATTAGE', 'WATTS', 'WATT', 'POWER'])) {
                $map['wattage'] = $idx;
                $matched++;
            } elseif (in_array($col, ['DISCRIPTION', 'DESCRIPTION', 'PARTICULARS', 'ITEMDESCRIPTION', 'PRODUCTDESCRIPTION'])) {
                $map['description'] = $idx;
                $matched++;
            } elseif (in_array($col, ['VOLTAGE', 'VOLTAGEDC12V', 'VOLTS', 'V'])) {
                $map['voltage'] = $idx;
                $matched++;
            } elseif (in_array($col, ['COLOR', 'COLORTEMPERATURE', 'CCT', 'KELVIN', 'LIGHTCOLOR'])) {
                $map['color'] = $idx;
                $matched++;
            } elseif (in_array($col, ['PRICE', 'UNITPRICE', 'SELLINGPRICE', 'AMOUNT'])) {
                $map['price'] = $idx;
                $matched++;
            } elseif (in_array($col, ['UNIT', 'UOM', 'UNITOFMEASURE'])) {
                $map['unit'] = $idx;
                $matched++;
            } elseif (in_array($col, ['STOCK', 'STOCKONHAND', 'BALANCE', 'QTY', 'QUANTITY', 'INVENTORY', 'TRANSITIN'])) {
                $map['stock'] = $idx;
                $matched++;
            } elseif (in_array($col, ['CATEGORY', 'GROUP', 'CLASSIFICATION'])) {
                $map['category'] = $idx;
                $matched++;
            } elseif (in_array($col, ['PICTURE', 'PICTURES', 'IMAGE', 'PHOTO'])) {
                $map['picture'] = $idx;
                $matched++;
            } elseif (in_array($col, ['CANONICALNAME', 'PRODUCTNAME', 'NAME', 'TITLE'])) {
                $map['name'] = $idx;
                $matched++;
            }
        }

        return $matched >= 2 ? $map : null;
    }

    /**
     * Extract structured record from CSV row.
     */
    protected function extractRecordFromRow(array $row, array $map, string $currentCategory): array
    {
        $code = isset($map['code']) && isset($row[$map['code']]) ? trim($row[$map['code']]) : null;
        $sku = isset($map['sku']) && isset($row[$map['sku']]) ? trim($row[$map['sku']]) : null;
        if (empty($code) && !empty($sku)) {
            $code = $sku;
        }
        $wattage = isset($map['wattage']) && isset($row[$map['wattage']]) ? trim($row[$map['wattage']]) : null;
        $desc = isset($map['description']) && isset($row[$map['description']]) ? trim($row[$map['description']]) : null;
        $voltage = isset($map['voltage']) && isset($row[$map['voltage']]) ? trim($row[$map['voltage']]) : null;
        $color = isset($map['color']) && isset($row[$map['color']]) ? trim($row[$map['color']]) : null;
        $rawPrice = isset($map['price']) && isset($row[$map['price']]) ? trim($row[$map['price']]) : '';
        $rawUnit = isset($map['unit']) && isset($row[$map['unit']]) ? trim($row[$map['unit']]) : null;
        $category = isset($map['category']) && !empty($row[$map['category']]) ? trim($row[$map['category']]) : $currentCategory;
        $picture = isset($map['picture']) && !empty($row[$map['picture']]) ? trim($row[$map['picture']]) : null;
        $name = isset($map['name']) && !empty($row[$map['name']]) ? trim($row[$map['name']]) : null;

        $rawStock = isset($map['stock']) && isset($row[$map['stock']]) ? trim($row[$map['stock']]) : null;
        $stockVal = null;
        if ($rawStock !== null && $rawStock !== '' && is_numeric(str_replace(',', '', $rawStock))) {
            $stockVal = (float) str_replace(',', '', $rawStock);
        }

        // Clean values like "XXX"
        if ($wattage === 'XXX' || $wattage === 'xxx') {
            $wattage = null;
        }
        if ($voltage === 'XXX' || $voltage === 'xxx') {
            $voltage = null;
        }
        if ($color === 'XXX' || $color === 'xxx') {
            $color = null;
        }

        // Parse price and unit from formats like "850.00/ROLL" or "1,950.00/ROLL"
        $price = 0.0;
        $unit = $rawUnit;

        if (!empty($rawPrice)) {
            if (str_contains($rawPrice, '/')) {
                [$pricePart, $unitPart] = explode('/', $rawPrice, 2);
                $cleanedPriceStr = preg_replace('/[^0-9.]/', '', str_replace(',', '', trim($pricePart)));
                $price = is_numeric($cleanedPriceStr) ? (float) $cleanedPriceStr : 0.0;
                if (empty($unit)) {
                    $unit = strtoupper(trim($unitPart));
                }
            } else {
                $cleanedPriceStr = preg_replace('/[^0-9.]/', '', str_replace(',', '', $rawPrice));
                $price = is_numeric($cleanedPriceStr) ? (float) $cleanedPriceStr : 0.0;
            }
        }

        // Normalize unit
        $normalizedUnit = match (strtoupper($unit ?? 'pcs')) {
            'ROLL', 'ROLLS' => 'roll',
            'METER', 'METERS', 'M' => 'm',
            'SET', 'SETS' => 'set',
            'PC', 'PCS', 'PIECE', 'PIECES' => 'pcs',
            'EACH', 'EA' => 'ea',
            'BOX', 'BOXES' => 'box',
            default => strtolower($unit ?: 'pcs'),
        };

        // Synthesize a clean canonical name if missing
        if (empty($name)) {
            if (!empty($desc)) {
                $name = ucwords(strtolower($desc));
            } elseif (!empty($code)) {
                $name = "Product {$code}";
            } else {
                $name = "Item {$category}";
            }
        }

        return [
            'product_code'      => $code,
            'canonical_name'    => $name,
            'description'       => $desc ?: $name,
            'sku'               => $sku,
            'category'          => $category ?: 'General',
            'wattage'           => $wattage,
            'voltage'           => $voltage,
            'color_temperature' => $color,
            'unit_default'      => $normalizedUnit,
            'price'             => $price,
            'stock'             => $stockVal,
            'image_path'        => $picture,
        ];
    }
}
