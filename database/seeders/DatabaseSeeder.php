<?php

namespace Database\Seeders;

use App\Actions\ReconcileDocumentTotals;
use App\Models\Document;
use App\Models\DocumentLineItem;
use App\Models\DocumentTotal;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductAlias;
use App\Models\Project;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocumentLayout;
use App\Models\VendorLayoutFieldMapping;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users for all 4 Multi-Auth Roles
        $admin = User::updateOrCreate(
            ['email' => 'admin@huenics.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'role' => User::ROLE_ADMIN,
            ]
        );

        $ops = User::updateOrCreate(
            ['email' => 'ops@huenics.com'],
            [
                'name' => 'Maria Santos (Operations Manager)',
                'password' => Hash::make('password'),
                'is_owner' => false,
                'role' => User::ROLE_OPERATIONS_MANAGER,
            ]
        );

        $sales = User::updateOrCreate(
            ['email' => 'sales@huenics.com'],
            [
                'name' => 'Carlos Reyes (Sales Executive)',
                'password' => Hash::make('password'),
                'is_owner' => false,
                'role' => User::ROLE_SALES_EXECUTIVE,
            ]
        );

        $ceo = User::updateOrCreate(
            ['email' => 'ceo@huenics.com'],
            [
                'name' => 'Chief Executive Officer (CEO)',
                'password' => Hash::make('password'),
                'is_owner' => true,
                'role' => User::ROLE_CEO,
            ]
        );

        $owner = $admin;

        // 2. Vendors
        $huenics = Vendor::firstOrCreate(
            ['slug' => 'huenics-industrial'],
            [
                'name' => 'Huenics Industrial Sales Inc.',
                'tin' => '009-876-543-000',
                'address' => 'Unit 916 Avida Towers Intima, 497 Pres. Quirino Ave. Ext. Cor. Zulueta St., Paco, Manila',
                'contact_person' => 'Customer Care',
                'phone' => '(02) 8561 6836',
                'email' => 'huenicsindustrialsales@gmail.com',
                'notes' => 'Primary operating entity - Colors • Techniques • Technology',
                'is_active' => true,
            ]
        );

        $mgs = Vendor::firstOrCreate(
            ['slug' => 'mgs-corp'],
            [
                'name' => 'MGS Construction & Supply Corp.',
                'tin' => '123-456-789-000',
                'address' => '88 E. Rodriguez Jr. Ave., Quezon City, Metro Manila',
                'contact_person' => 'Engr. Roberto Santos',
                'phone' => '+63 2 8987 6543',
                'email' => 'procurement@mgs.ph',
                'notes' => 'Key general contracting client & vendor',
                'is_active' => true,
            ]
        );

        // 3. Projects
        $palanza = Project::firstOrCreate(
            ['code' => 'PRJ-PALANZA'],
            [
                'name' => 'Palanza Tower Project',
                'customer_name' => 'MGS Construction Corp.',
                'location' => 'Palanza St. cor. Santol, Quezon City',
                'description' => 'High-rise residential plumbing and structural piping supply',
                'status' => 'active',
            ]
        );

        $cebuProject = Project::firstOrCreate(
            ['code' => 'PRJ-CLIP'],
            [
                'name' => 'Cebu Light Industrial Park Phase 2',
                'customer_name' => 'Cebu Holdings',
                'location' => 'MEPZ II, Lapu-Lapu City, Cebu',
                'description' => 'Industrial warehouse drainage & electrical conduit supply',
                'status' => 'active',
            ]
        );

        // 4. Products & Aliases
        $pvcPipe = Product::firstOrCreate(
            ['canonical_name' => '1-1/4" PVC Pipe Sch 40'],
            [
                'sku' => 'PVC-125-SCH40',
                'category' => 'Pipes & Fittings',
                'unit_default' => 'pcs',
                'default_price' => 1880.56,
                'is_huenics_owned' => true,
                'is_active' => true,
            ]
        );

        $steelBar = Product::firstOrCreate(
            ['canonical_name' => '1/2" Deformed Steel Bar Grade 40'],
            [
                'sku' => 'ST-DSB-12MM',
                'category' => 'Structural Steel',
                'unit_default' => 'pcs',
                'default_price' => 320.00,
                'is_huenics_owned' => true,
                'is_active' => true,
            ]
        );

        $pump = Product::firstOrCreate(
            ['canonical_name' => '2" Submersible Sewage Pump 1.5HP'],
            [
                'sku' => 'PUMP-SUB-2IN',
                'category' => 'Pumps & Equipment',
                'unit_default' => 'unit',
                'default_price' => 18500.00,
                'is_huenics_owned' => true,
                'is_active' => true,
            ]
        );

        $conduit = Product::firstOrCreate(
            ['canonical_name' => 'HDPE Conduit Pipe 20mm SDR 11'],
            [
                'sku' => 'HDPE-20MM',
                'category' => 'Electrical',
                'unit_default' => 'mtr',
                'default_price' => 85.50,
                'is_huenics_owned' => false,
                'is_active' => true,
            ]
        );

        // Product Aliases
        ProductAlias::firstOrCreate([
            'product_id' => $pvcPipe->id,
            'normalized_alias' => ProductAlias::normalize('1-1/4" PVC Pipe'),
        ], ['alias_text' => '1-1/4" PVC Pipe', 'vendor_id' => $mgs->id]);

        ProductAlias::firstOrCreate([
            'product_id' => $pvcPipe->id,
            'normalized_alias' => ProductAlias::normalize('PVC PIPE 1.25 INCH'),
        ], ['alias_text' => 'PVC PIPE 1.25 INCH']);

        ProductAlias::firstOrCreate([
            'product_id' => $steelBar->id,
            'normalized_alias' => ProductAlias::normalize('DEFORMED BAR 12MM'),
        ], ['alias_text' => 'DEFORMED BAR 12MM']);

        ProductAlias::firstOrCreate([
            'product_id' => $steelBar->id,
            'normalized_alias' => ProductAlias::normalize('STEEL BAR 10MM'),
        ], ['alias_text' => 'STEEL BAR 10MM']);

        // Scaffold Inventory for Huenics items
        foreach ([$pvcPipe, $steelBar, $pump] as $item) {
            InventoryItem::firstOrCreate(
                ['product_id' => $item->id],
                [
                    'quantity_on_hand' => 250,
                    'quantity_reserved' => 50,
                    'reorder_point' => 30,
                    'unit' => $item->unit_default,
                    'last_counted_at' => now(),
                ]
            );
        }

        // 5. Vendor Layout Configurations
        $docTypes = [
            Document::TYPE_PURCHASE_ORDER,
            Document::TYPE_VENDORS_AGREEMENT,
        ];

        foreach ([$huenics, $mgs] as $v) {
            foreach ($docTypes as $type) {
                $layout = VendorDocumentLayout::firstOrCreate([
                    'vendor_id' => $v->id,
                    'document_type' => $type,
                    'layout_version' => 1,
                ], [
                    'is_active' => true,
                    'notes' => "Standard layout configuration for {$v->name} ({$type})",
                ]);

                // Default field mappings
                $mappings = [
                    ['field_key' => 'document_number', 'target_scope' => 'header', 'extraction_strategy' => 'regex_header', 'regex_pattern' => '/(?:PO\s*(?:No\.?|\#)?|P\.?O\.?\s*(?:No\.?|\#)?|S\.?O\.?\s*(?:No\.?|\#)?|Quotation\s*(?:No\.?|\#)?|Ref\s*(?:No\.?|\#)?)\s*[:\.]?\s*([A-Z0-9\-\_\s]+?)(?=\s+Date|\r|\n|$)/i', 'post_process' => 'trim', 'is_required' => true],
                    ['field_key' => 'document_date', 'target_scope' => 'header', 'extraction_strategy' => 'regex_header', 'regex_pattern' => '/(?:Date|Dated)\s*[:\.]?\s*([0-9\/\\-\.]+)/i', 'post_process' => 'parse_date', 'is_required' => false],
                    ['field_key' => 'printed_subtotal', 'target_scope' => 'totals', 'extraction_strategy' => 'keyword_offset', 'regex_pattern' => '/(?:subtotal|sub-total)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i', 'post_process' => 'parse_decimal', 'is_required' => false],
                    ['field_key' => 'printed_vat', 'target_scope' => 'totals', 'extraction_strategy' => 'keyword_offset', 'regex_pattern' => '/(?:12\%\s*VAT|VAT)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i', 'post_process' => 'parse_decimal', 'is_required' => false],
                    ['field_key' => 'printed_total', 'target_scope' => 'totals', 'extraction_strategy' => 'keyword_offset', 'regex_pattern' => '/(?:grand\s*total|total\s*amount)\s*[:\.]?\s*(?:PHP|₱)?\s*([\d\,\.]+)/i', 'post_process' => 'parse_decimal', 'is_required' => false],
                ];

                foreach ($mappings as $idx => $map) {
                    $layout->fieldMappings()->firstOrCreate(
                        ['field_key' => $map['field_key']],
                        array_merge($map, ['sort_order' => $idx])
                    );
                }
            }
        }

    //     // 6. Sample Documents & Fixtures (Demonstrating blueprint validation rules)
    //     $reconciler = app(ReconcileDocumentTotals::class);

    //     // Document 1: Purchase Order #4010027092 (Demonstrates line item .85 printed error)
    //     $poDoc = Document::firstOrCreate(
    //         ['file_hash' => 'hash_sample_po_4010027092_palanzatower'],
    //         [
    //             'vendor_id' => $mgs->id,
    //             'project_id' => $palanza->id,
    //             'uploaded_by' => $owner->id,
    //             'document_type' => Document::TYPE_PURCHASE_ORDER,
    //             'document_number' => '4010027092',
    //             'document_date' => '2026-08-01',
    //             'original_filename' => 'PO_4010027092_Palanza.pdf',
    //             'disk_path' => 'documents/uploads/media_1786721108461.pdf',
    //             'status' => Document::STATUS_REQUIRES_REVIEW,
    //             'extraction_confidence' => 95.0,
    //             'processed_at' => now(),
    //         ]
    //     );

    //     $poDoc->lineItems()->delete();
    //     // Line 1: Normal line
    //     $poDoc->lineItems()->create([
    //         'line_no' => 1,
    //         'material_code' => null,
    //         'description' => 'DEFORMED BAR 12MM',
    //         'qty' => 500,
    //         'unit' => 'pcs',
    //         'unit_price' => 320.00,
    //         'printed_total' => 160000.00,
    //         'computed_total' => 160000.00,
    //         'total_mismatch' => false,
    //         'product_id' => $steelBar->id,
    //     ]);
    //     // Line 30: The famous blueprint line with printed .85 bug (158 * 1,880.56 = 297,128.48, printed as 297,128.85)
    //     $poDoc->lineItems()->create([
    //         'line_no' => 30,
    //         'material_code' => null,
    //         'description' => '1-1/4" PVC Pipe Sch 40',
    //         'qty' => 158,
    //         'unit' => 'pcs',
    //         'unit_price' => 1880.56,
    //         'printed_total' => 297128.85, // Discrepancy!
    //         'computed_total' => 297128.48,
    //         'total_mismatch' => true,
    //         'product_id' => $pvcPipe->id,
    //     ]);

    //     $poDoc->totals()->updateOrCreate(
    //         ['document_id' => $poDoc->id],
    //         [
    //             'printed_subtotal' => 457128.85,
    //             'printed_vat' => 54855.46,
    //             'printed_total' => 511984.31,
    //             'computed_subtotal' => 457128.48,
    //             'computed_vat' => 54855.42,
    //             'computed_grand_total' => 511983.90,
    //             'vat_mismatch' => false,
    //             'total_mismatch' => true,
    //         ]
    //     );
    //     $reconciler->execute($poDoc);

    //     // Document 2: Order Slip S.O.#26005 (Demonstrates copied wrong VAT of 112,500.00 vs computed 101,785.72)
    //     $osDoc = Document::firstOrCreate(
    //         ['file_hash' => 'hash_sample_orderslip_26005_palanza'],
    //         [
    //             'vendor_id' => $huenics->id,
    //             'project_id' => $palanza->id,
    //             'uploaded_by' => $owner->id,
    //             'document_type' => Document::TYPE_PURCHASE_ORDER,
    //             'document_number' => 'SO-26005',
    //             'document_date' => '2026-08-02',
    //             'original_filename' => 'Order_Slip_26005_Palanza.pdf',
    //             'disk_path' => 'documents/uploads/media_1786721108462.pdf',
    //             'status' => Document::STATUS_REQUIRES_REVIEW,
    //             'extraction_confidence' => 92.0,
    //             'processed_at' => now(),
    //         ]
    //     );

    //     $osDoc->lineItems()->delete();
    //     $osDoc->lineItems()->create([
    //         'line_no' => 1,
    //         'description' => '1-1/4" PVC Pipe Sch 40',
    //         'qty' => 451,
    //         'unit' => 'pcs',
    //         'unit_price' => 1880.74,
    //         'printed_total' => 848214.34,
    //         'computed_total' => 848213.74,
    //         'total_mismatch' => false,
    //         'product_id' => $pvcPipe->id,
    //     ]);

    //     $osDoc->totals()->updateOrCreate(
    //         ['document_id' => $osDoc->id],
    //         [
    //             'printed_subtotal' => 848214.34,
    //             'printed_vat' => 112500.00, // Discrepancy! Copied from companion order VAT
    //             'printed_total' => 960714.34,
    //             'computed_subtotal' => 848213.74,
    //             'computed_vat' => 101785.65,
    //             'computed_grand_total' => 949999.39,
    //             'vat_mismatch' => true,
    //             'total_mismatch' => true,
    //         ]
    //     );
    //     $reconciler->execute($osDoc);

    //     // Document 3: Vendors Agreement Form (Quotation with Negotiated Amount 1,050,000 vs Sum 1,074,060)
    //     $vafDoc = Document::firstOrCreate(
    //         ['file_hash' => 'hash_sample_quotation_vaf_1050000'],
    //         [
    //             'vendor_id' => $huenics->id,
    //             'project_id' => $palanza->id,
    //             'uploaded_by' => $owner->id,
    //             'document_type' => Document::TYPE_VENDORS_AGREEMENT,
    //             'document_number' => 'VAF-2026-081',
    //             'document_date' => '2026-07-28',
    //             'original_filename' => 'Vendors_Agreement_Palanza.pdf',
    //             'disk_path' => 'documents/uploads/media_1786721108464.pdf',
    //             'status' => Document::STATUS_REQUIRES_REVIEW,
    //             'extraction_confidence' => 98.0,
    //             'processed_at' => now(),
    //         ]
    //     );

    //     $vafDoc->lineItems()->delete();
    //     $vafDoc->lineItems()->create([
    //         'line_no' => 1,
    //         'description' => '1-1/4" PVC Pipe Sch 40 Package',
    //         'qty' => 500,
    //         'unit' => 'pcs',
    //         'unit_price' => 1880.00,
    //         'printed_total' => 940000.00,
    //         'computed_total' => 940000.00,
    //         'total_mismatch' => false,
    //         'product_id' => $pvcPipe->id,
    //     ]);
    //     $vafDoc->lineItems()->create([
    //         'line_no' => 2,
    //         'description' => 'Delivery and On-Site Handling Fee',
    //         'qty' => 1,
    //         'unit' => 'lot',
    //         'unit_price' => 134060.00,
    //         'printed_total' => 134060.00,
    //         'computed_total' => 134060.00,
    //         'total_mismatch' => false,
    //         'product_id' => null,
    //     ]);

    //     $vafDoc->totals()->updateOrCreate(
    //         ['document_id' => $vafDoc->id],
    //         [
    //             'printed_subtotal' => 1074060.00,
    //             'printed_vat' => 0.00,
    //             'printed_total' => 1074060.00,
    //             'negotiated_amount' => 1050000.00, // Authoritative deal amount!
    //             'computed_subtotal' => 1074060.00,
    //             'computed_vat' => 128887.20,
    //             'computed_grand_total' => 1202947.20,
    //             'vat_mismatch' => false,
    //             'total_mismatch' => false,
    //         ]
    //     );
    //     $reconciler->execute($vafDoc);

        // Seed all products from official Company Profile
        $this->call(HuenicsCompanyProfileProductSeeder::class);
    }
}
