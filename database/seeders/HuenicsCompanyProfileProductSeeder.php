<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductAlias;
use Illuminate\Database\Seeder;

class HuenicsCompanyProfileProductSeeder extends Seeder
{
    /**
     * Run the database seeds for Huenics Industrial Sales Inc.
     * Seeded directly from the official Company Profile PDF.
     */
    public function run(): void
    {
        $products = [
            // ─── Indoor Downlights (Pages 8-10) ──────────────────────────
            [
                'sku' => 'HISI-JF-2240-7W',
                'canonical_name' => 'Led Downlight C.O.B Citizen Japan 3500k Warmwhite 7w',
                'category' => 'Indoor Downlights',
                'description' => 'Size: Ø110mm x 25mm, premium aluminum casing, Citizen Japan C.O.B chip with high CRI rendering and 2-year warranty.',
                'unit_default' => 'pcs',
                'default_price' => 1950.00,
                'selling_price' => 1755.00,
                'base_cost_price' => 1200.00,
            ],
            [
                'sku' => 'HISI-MZTD-A001R',
                'canonical_name' => 'Architectural Recessed Round Downlight MZTD-A001R',
                'category' => 'Indoor Downlights',
                'description' => 'Architectural recessed round downlight with anti-glare reflector and deep baffle for commercial projects.',
                'unit_default' => 'pcs',
                'default_price' => 1450.00,
                'selling_price' => 1250.00,
                'base_cost_price' => 850.00,
            ],
            [
                'sku' => 'HISI-MZTD-A001SQ',
                'canonical_name' => 'Architectural Recessed Square Downlight MZTD-A001SQ',
                'category' => 'Indoor Downlights',
                'description' => 'Square architectural downlight fixture with matte white trim and specular faceted aluminum reflector.',
                'unit_default' => 'pcs',
                'default_price' => 1550.00,
                'selling_price' => 1350.00,
                'base_cost_price' => 900.00,
            ],
            [
                'sku' => 'HISI-BR-DL2',
                'canonical_name' => 'Deep Recessed Downlight BR-DL2 2107-1',
                'category' => 'Indoor Downlights',
                'description' => 'Commercial deep recessed downlight with faceted aluminum reflector for hotel lobbies and executive offices.',
                'unit_default' => 'pcs',
                'default_price' => 1650.00,
                'selling_price' => 1450.00,
                'base_cost_price' => 980.00,
            ],
            [
                'sku' => 'HISI-THD-C005',
                'canonical_name' => 'Commercial Downlight THD-C005 (4" & 6")',
                'category' => 'Indoor Downlights',
                'description' => 'Available in 4-inch and 6-inch aperture, heavy duty heat sink with orange spring clips and uniform light diffusion.',
                'unit_default' => 'pcs',
                'default_price' => 1850.00,
                'selling_price' => 1650.00,
                'base_cost_price' => 1100.00,
            ],
            [
                'sku' => 'HISI-MLGR-15X1',
                'canonical_name' => 'Linear Gimbal Downlight MLGR 15X1 (1x / 2x / 3x)',
                'category' => 'Indoor Downlights',
                'description' => 'Multi-lamp directional linear downlight in black casing, 30° tilt adjustment for retail accentuation.',
                'unit_default' => 'pcs',
                'default_price' => 2500.00,
                'selling_price' => 2200.00,
                'base_cost_price' => 1500.00,
            ],
            [
                'sku' => 'HISI-MLGR-15X2',
                'canonical_name' => 'Twin Directional Gimbal Downlight MLGR 15X2',
                'category' => 'Indoor Downlights',
                'description' => 'Double head linear gimbal fixture, independent cardan multi-directional aiming.',
                'unit_default' => 'pcs',
                'default_price' => 3800.00,
                'selling_price' => 3400.00,
                'base_cost_price' => 2300.00,
            ],
            [
                'sku' => 'HISI-SM-DY-038',
                'canonical_name' => 'Anti-Glare Commercial Downlight SM-DY-038',
                'category' => 'Indoor Downlights',
                'description' => 'Sizes: 3", 4", 6", 8". High efficiency Citizen C.O.B engine with UGR<19 low glare rating.',
                'unit_default' => 'pcs',
                'default_price' => 1950.00,
                'selling_price' => 1750.00,
                'base_cost_price' => 1180.00,
            ],
            [
                'sku' => 'HISI-SM-CT-107-5',
                'canonical_name' => 'High Output Commercial Downlight SM-CT-107-5',
                'category' => 'Indoor Downlights',
                'description' => 'Sizes: 4", 5", 6", 8". Die-cast aluminum passive cooling heat sink with ribbed thermal fins.',
                'unit_default' => 'pcs',
                'default_price' => 2100.00,
                'selling_price' => 1850.00,
                'base_cost_price' => 1250.00,
            ],
            [
                'sku' => 'HISI-SL-661',
                'canonical_name' => 'Surface Mounted Cylindrical Downlight SL-661',
                'category' => 'Indoor Downlights',
                'description' => 'Clean cylindrical surface mount can light, architectural white finish with dark specular reflector.',
                'unit_default' => 'pcs',
                'default_price' => 1350.00,
                'selling_price' => 1150.00,
                'base_cost_price' => 780.00,
            ],
            [
                'sku' => 'HISI-SL-662',
                'canonical_name' => 'Surface Mounted Dual-Tone Downlight SL-662',
                'category' => 'Indoor Downlights',
                'description' => 'Dual finish surface downlight with high-grade optical reflector and integrated driver housing.',
                'unit_default' => 'pcs',
                'default_price' => 1550.00,
                'selling_price' => 1350.00,
                'base_cost_price' => 920.00,
            ],
            [
                'sku' => 'HISI-LP-600X600',
                'canonical_name' => 'Ultra Slim LED Panel Light LP 600x600mm',
                'category' => 'Indoor Downlights',
                'description' => 'Backlit & edge-lit office acoustic ceiling panel light 40W, 4000lm, flicker-free driver included.',
                'unit_default' => 'pcs',
                'default_price' => 2100.00,
                'selling_price' => 1850.00,
                'base_cost_price' => 1250.00,
            ],

            // ─── Indoor Tracklights & Ceiling Lamps (Page 11) ─────────────
            [
                'sku' => 'HISI-ML-TR-20W',
                'canonical_name' => 'Commercial LED Tracklight ML-TR 20W/27W',
                'category' => 'Tracklights & Ceiling Lamps',
                'description' => 'Available in White and Black housing, Citizen C.O.B with high CRI 90+ and 3-circuit track adapter.',
                'unit_default' => 'pcs',
                'default_price' => 1850.00,
                'selling_price' => 1650.00,
                'base_cost_price' => 1100.00,
            ],
            [
                'sku' => 'HISI-BR-TLA-30W',
                'canonical_name' => 'Architectural Tracklight BR-TLA 20/30W 3000K',
                'category' => 'Tracklights & Ceiling Lamps',
                'description' => 'Die-cast aluminum tracklight with precision lens beam angle options (15°, 24°, 38°).',
                'unit_default' => 'pcs',
                'default_price' => 2400.00,
                'selling_price' => 2100.00,
                'base_cost_price' => 1400.00,
            ],
            [
                'sku' => 'HISI-II2WSMD18W',
                'canonical_name' => 'Flush Mount LED Ceiling Lamp II2WSMD18W 18W',
                'category' => 'Tracklights & Ceiling Lamps',
                'description' => 'Slim profile circular ceiling light with acrylic frosted diffuser for hallways, balconies, and utility rooms.',
                'unit_default' => 'pcs',
                'default_price' => 980.00,
                'selling_price' => 850.00,
                'base_cost_price' => 550.00,
            ],

            // ─── Office Lights & Linear Profiles (Page 12) ───────────────
            [
                'sku' => 'HISI-TRS-1X40',
                'canonical_name' => 'Surface Louver Troffer Office Light TRS-1X40',
                'category' => 'Office & Linear Lights',
                'description' => 'Anodized aluminum parabolic louver for glare control in corporate offices and educational facilities.',
                'unit_default' => 'pcs',
                'default_price' => 1980.00,
                'selling_price' => 1750.00,
                'base_cost_price' => 1180.00,
            ],
            [
                'sku' => 'HISI-TRR-3X40',
                'canonical_name' => 'Recessed Mirror Louver Troffer TRR-3X40',
                'category' => 'Office & Linear Lights',
                'description' => 'T-bar acoustic ceiling recessed 3-lamp luminaire, specular mirror louvers for optimum lumen distribution.',
                'unit_default' => 'pcs',
                'default_price' => 2800.00,
                'selling_price' => 2450.00,
                'base_cost_price' => 1650.00,
            ],
            [
                'sku' => 'HISI-LED-T8',
                'canonical_name' => 'Commercial LED T8 Tube 1200mm 18W',
                'category' => 'Office & Linear Lights',
                'description' => 'Glass & polycarbonate G13 bi-pin LED replacement tube, double-ended wiring, high lumen output.',
                'unit_default' => 'pcs',
                'default_price' => 350.00,
                'selling_price' => 280.00,
                'base_cost_price' => 180.00,
            ],
            [
                'sku' => 'HISI-LL-24W',
                'canonical_name' => 'Architectural Suspended Linear Profile LL-24W',
                'category' => 'Office & Linear Lights',
                'description' => 'Extruded aluminum profile with frosted polycarbonate lens and aircraft cable suspension kit.',
                'unit_default' => 'pcs',
                'default_price' => 3600.00,
                'selling_price' => 3200.00,
                'base_cost_price' => 2100.00,
            ],

            // ─── LED Strip Lights (Page 13) ──────────────────────────────
            [
                'sku' => 'HISI-STRIP-5050',
                'canonical_name' => 'LED Strip Light 5050 12V 5M Reel',
                'category' => 'LED Strips & Neon',
                'description' => '60 LEDs/m flexible PCB with 3M adhesive backing, IP20 indoor cove lighting in 3000K / 4000K / 6500K.',
                'unit_default' => 'roll',
                'default_price' => 780.00,
                'selling_price' => 680.00,
                'base_cost_price' => 450.00,
            ],
            [
                'sku' => 'HISI-STRIP-COB',
                'canonical_name' => 'Dotless Seamless COB LED Strip 24V',
                'category' => 'LED Strips & Neon',
                'description' => 'High density continuous phosphor linear COB strip, completely dotless illumination inside aluminum profiles.',
                'unit_default' => 'roll',
                'default_price' => 1650.00,
                'selling_price' => 1450.00,
                'base_cost_price' => 950.00,
            ],
            [
                'sku' => 'HISI-NEON-FLEX',
                'canonical_name' => 'Architectural Silicone LED Neon Flex 220V',
                'category' => 'LED Strips & Neon',
                'description' => 'UV resistant food-grade silicone jacket, IP67 waterproof exterior facade and sign accent lighting.',
                'unit_default' => 'mtr',
                'default_price' => 380.00,
                'selling_price' => 320.00,
                'base_cost_price' => 210.00,
            ],

            // ─── Highbay & Industrial (Page 15) ──────────────────────────
            [
                'sku' => 'HISI-UFO-100W',
                'canonical_name' => 'Industrial UFO LED Highbay Light 100W IP65',
                'category' => 'Highbay & Industrial',
                'description' => 'Die-cast ADC12 aluminum heatsink, Philips Lumileds SMD3030 chips, Mean Well/DONE driver, 140 lm/W.',
                'unit_default' => 'pcs',
                'default_price' => 4200.00,
                'selling_price' => 3850.00,
                'base_cost_price' => 2600.00,
            ],
            [
                'sku' => 'HISI-UFO-150W',
                'canonical_name' => 'Industrial UFO LED Highbay Light 150W IP65',
                'category' => 'Highbay & Industrial',
                'description' => '150W industrial highbay for factory floors, logistics warehouses, and sporting facilities.',
                'unit_default' => 'pcs',
                'default_price' => 5500.00,
                'selling_price' => 4950.00,
                'base_cost_price' => 3400.00,
            ],

            // ─── Emergency Systems (Page 16) ─────────────────────────────
            [
                'sku' => 'HISI-EM291',
                'canonical_name' => 'Automatic Twin Head LED Emergency Light EM291',
                'category' => 'Emergency Systems',
                'description' => 'Dual adjustable lamp heads, long-life Ni-Cd battery backup with overcharge and deep discharge protection.',
                'unit_default' => 'pcs',
                'default_price' => 1350.00,
                'selling_price' => 1150.00,
                'base_cost_price' => 780.00,
            ],
            [
                'sku' => 'HISI-EX108RA',
                'canonical_name' => 'LED Green Running Man Exit Sign EX108RA',
                'category' => 'Emergency Systems',
                'description' => 'Edge-lit acrylic sign with directional arrow, 3 hours emergency operation during power failure.',
                'unit_default' => 'pcs',
                'default_price' => 1450.00,
                'selling_price' => 1250.00,
                'base_cost_price' => 850.00,
            ],
            [
                'sku' => 'HISI-EM-BATTPACK',
                'canonical_name' => 'Emergency Inverter Battery Backup Conversion Kit',
                'category' => 'Emergency Systems',
                'description' => 'Constant power emergency driver conversion kit for LED downlights and troffers.',
                'unit_default' => 'pcs',
                'default_price' => 2100.00,
                'selling_price' => 1850.00,
                'base_cost_price' => 1250.00,
            ],

            // ─── Outdoor & Landscape Lighting (Pages 17-20) ──────────────
            [
                'sku' => 'HISI-WLB-956',
                'canonical_name' => 'Oval Waterproof Die-Cast Bulkhead Lamp WLB-956',
                'category' => 'Outdoor & Streetlights',
                'description' => 'Size: 105mm x 210mm x H90mm, heavy-duty aluminum cage with tempered glass lens, IP65 rated.',
                'unit_default' => 'pcs',
                'default_price' => 980.00,
                'selling_price' => 850.00,
                'base_cost_price' => 580.00,
            ],
            [
                'sku' => 'HISI-WLB-2001',
                'canonical_name' => 'Architectural Up & Down Outdoor Wall Lamp WLB-2001',
                'category' => 'Outdoor & Streetlights',
                'description' => 'Dual beam exterior facade fixture with high-efficiency optical lenses and waterproof seals.',
                'unit_default' => 'pcs',
                'default_price' => 1850.00,
                'selling_price' => 1650.00,
                'base_cost_price' => 1100.00,
            ],
            [
                'sku' => 'HISI-GST-201',
                'canonical_name' => 'Waterproof Outdoor Garden Spike Spotlight GST 201 MR16',
                'category' => 'Outdoor & Streetlights',
                'description' => 'Cast aluminum landscape spike light with 360° swivel mount for garden shrubbery and tree uplighting.',
                'unit_default' => 'pcs',
                'default_price' => 980.00,
                'selling_price' => 890.00,
                'base_cost_price' => 590.00,
            ],
            [
                'sku' => 'HISI-GDN-191',
                'canonical_name' => 'Contemporary Landscape Bollard Light GDN 191 E27',
                'category' => 'Outdoor & Streetlights',
                'description' => 'Powder coated extruded aluminum column with clear louvers, anchor bolts kit included for pathway lighting.',
                'unit_default' => 'pcs',
                'default_price' => 2950.00,
                'selling_price' => 2650.00,
                'base_cost_price' => 1800.00,
            ],
            [
                'sku' => 'HISI-IL-2909',
                'canonical_name' => 'Inground Walkway Well Light Stainless Steel IL-2909',
                'category' => 'Outdoor & Streetlights',
                'description' => '304 stainless steel trim ring, drive-over tempered glass lens, IP67 waterproof underground burial housing.',
                'unit_default' => 'pcs',
                'default_price' => 2100.00,
                'selling_price' => 1850.00,
                'base_cost_price' => 1250.00,
            ],
            [
                'sku' => 'HISI-SL-80482',
                'canonical_name' => 'Die-cast Heavy Duty LED Streetlight SL 80482',
                'category' => 'Outdoor & Streetlights',
                'description' => 'Aerodynamic die-cast housing with Batwing optical lens for roadway distribution, 60mm spigot mount.',
                'unit_default' => 'pcs',
                'default_price' => 5400.00,
                'selling_price' => 4800.00,
                'base_cost_price' => 3300.00,
            ],
            [
                'sku' => 'HISI-FL-COB100W',
                'canonical_name' => 'Outdoor Heavy Duty COB LED Floodlight 100W',
                'category' => 'Outdoor & Streetlights',
                'description' => 'IP66 waterproof rated with thick heat sink fins, 120° beam angle for building facades and yards.',
                'unit_default' => 'pcs',
                'default_price' => 3400.00,
                'selling_price' => 2950.00,
                'base_cost_price' => 2000.00,
            ],

            // ─── Digital Home & Smart Systems (Pages 21-23) ──────────────
            [
                'sku' => 'HISI-SMART-GW',
                'canonical_name' => 'Multi-Protocol Smart Gateway Hub (Wi-Fi / Zigbee / Bluetooth)',
                'category' => 'Smart Home & Automation',
                'description' => 'Centralized automation controller supporting voice assistants (Alexa, Google), timing, and remote scene control.',
                'unit_default' => 'unit',
                'default_price' => 3200.00,
                'selling_price' => 2800.00,
                'base_cost_price' => 1900.00,
            ],
            [
                'sku' => 'HISI-SMART-SW-4X',
                'canonical_name' => 'Luxury Tempered Glass Touch Switch Panel 4-Gang',
                'category' => 'Smart Home & Automation',
                'description' => 'Capacitive touch switch with LED backlighting, smartphone app control and timer schedules.',
                'unit_default' => 'pcs',
                'default_price' => 2100.00,
                'selling_price' => 1850.00,
                'base_cost_price' => 1250.00,
            ],
            [
                'sku' => 'HISI-SMART-CAM-PTZ',
                'canonical_name' => '360° WiFi PTZ Smart Security Camera Night Vision',
                'category' => 'Smart Home & Automation',
                'description' => 'Pan-tilt-zoom wireless camera with two-way audio, AI motion tracking, and remote mobile viewing.',
                'unit_default' => 'unit',
                'default_price' => 3400.00,
                'selling_price' => 2950.00,
                'base_cost_price' => 2000.00,
            ],
            [
                'sku' => 'HISI-SMART-SOCKET',
                'canonical_name' => 'Smart WiFi Wall Power Socket with Power Monitor',
                'category' => 'Smart Home & Automation',
                'description' => 'Dual universal receptacle with individual smart switching, energy metering, and overload protection.',
                'unit_default' => 'pcs',
                'default_price' => 1350.00,
                'selling_price' => 1150.00,
                'base_cost_price' => 780.00,
            ],
        ];

        foreach ($products as $data) {
            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                array_merge($data, [
                    'is_huenics_owned' => true,
                    'is_composite' => false,
                    'is_active' => true,
                ])
            );

            // Seed inventory
            InventoryItem::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity_on_hand' => 150,
                    'quantity_reserved' => 20,
                    'reorder_point' => 25,
                    'unit' => $product->unit_default,
                    'last_counted_at' => now(),
                ]
            );

            // Normalized Alias
            ProductAlias::firstOrCreate([
                'product_id' => $product->id,
                'normalized_alias' => ProductAlias::normalize($product->canonical_name),
            ], [
                'alias_text' => $product->canonical_name,
            ]);
        }
    }
}
