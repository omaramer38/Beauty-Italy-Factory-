<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PhaseOneSeeder extends Seeder
{
    public function run(): void
    {
        // Clear old demo data in reverse foreign key order
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('traceability_records')->truncate();
        DB::table('qa_releases')->truncate();
        DB::table('qc_test_results')->truncate();
        DB::table('qc_samples')->truncate();
        DB::table('batch_dispense_steps')->truncate();
        DB::table('production_batches')->truncate();
        DB::table('formulation_approvals')->truncate();
        DB::table('formulation_lines')->truncate();
        DB::table('formulations')->truncate();
        DB::table('inventory_ledger')->truncate();
        DB::table('lots')->truncate();
        DB::table('items')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Items Master Data
        $now = now();
        $itemsData = [
            'aqua' => [
                'code' => 'RM-00001',
                'name' => 'Purified Water EP/USP',
                'type' => 'raw_material',
                'inci_name' => 'Aqua / Water',
                'technical_name' => 'Purified Water EP demineralized',
                'cas_number' => '7732-18-5',
                'allergens' => 'None',
                'function' => 'Solvent',
                'unit' => 'kg',
                'cost_per_unit' => 0.15,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'gly' => [
                'code' => 'RM-00142',
                'name' => 'Vegetable Glycerin 99.5%',
                'type' => 'raw_material',
                'inci_name' => 'Glycerin',
                'technical_name' => 'Vegetable Glycerin 99.5% USP/EP',
                'cas_number' => '56-81-5',
                'allergens' => 'None',
                'function' => 'Humectant',
                'unit' => 'kg',
                'cost_per_unit' => 2.40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'nia' => [
                'code' => 'RM-00056',
                'name' => 'Niacinamide USP (Vitamin B3)',
                'type' => 'raw_material',
                'inci_name' => 'Niacinamide',
                'technical_name' => 'Vitamin B3 USP Grade High Purity',
                'cas_number' => '98-92-0',
                'allergens' => 'None',
                'function' => 'Active',
                'unit' => 'kg',
                'cost_per_unit' => 14.50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'cet' => [
                'code' => 'RM-00104',
                'name' => 'Cetearyl Alcohol (Lanette O)',
                'type' => 'raw_material',
                'inci_name' => 'Cetearyl Alcohol',
                'technical_name' => 'Lanette O Fatty Alcohol 50/50',
                'cas_number' => '67762-27-0',
                'allergens' => 'None',
                'function' => 'Emulsifier',
                'unit' => 'kg',
                'cost_per_unit' => 3.80,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'mct' => [
                'code' => 'RM-00118',
                'name' => 'Caprylic/Capric Triglyceride (MCT)',
                'type' => 'raw_material',
                'inci_name' => 'Caprylic/Capric Triglyceride',
                'technical_name' => 'Fractionated Coconut MCT Oil',
                'cas_number' => '73398-61-5',
                'allergens' => 'None',
                'function' => 'Emollient',
                'unit' => 'kg',
                'cost_per_unit' => 5.20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'shea' => [
                'code' => 'RM-00135',
                'name' => 'Organic Shea Butter Refined',
                'type' => 'raw_material',
                'inci_name' => 'Butyrospermum Parkii (Shea) Butter',
                'technical_name' => 'Organic Refined Shea Butter EP',
                'cas_number' => '91080-23-8',
                'allergens' => 'None',
                'function' => 'Emollient',
                'unit' => 'kg',
                'cost_per_unit' => 8.50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'ret' => [
                'code' => 'RM-00028',
                'name' => 'Retinol 10% Encapsulated Liposomes',
                'type' => 'raw_material',
                'inci_name' => 'Retinol (and) Phospholipids',
                'technical_name' => 'Encapsulated Retinol 10% Active Liposphere',
                'cas_number' => '68-26-8',
                'allergens' => 'None',
                'function' => 'Active',
                'unit' => 'kg',
                'cost_per_unit' => 125.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'eux' => [
                'code' => 'RM-00091',
                'name' => 'Euxyl PE 9010 Preservative System',
                'type' => 'raw_material',
                'inci_name' => 'Phenoxyethanol, Ethylhexylglycerin',
                'technical_name' => 'Euxyl PE 9010 Liquid Broad Spectrum',
                'cas_number' => '122-99-6',
                'allergens' => 'None',
                'function' => 'Preservative',
                'unit' => 'kg',
                'cost_per_unit' => 9.20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'rf' => [
                'code' => 'RM-00201',
                'name' => 'Signature Bloom 114 Aroma',
                'type' => 'raw_material',
                'inci_name' => 'Parfum',
                'technical_name' => 'Signature Bloom 114 Natural Essences',
                'cas_number' => 'Complex Mix',
                'allergens' => 'Linalool (0.04%), Citronellol (0.02%)',
                'function' => 'Fragrance',
                'unit' => 'kg',
                'cost_per_unit' => 48.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'ha' => [
                'code' => 'RM-00193',
                'name' => 'Hyaluronic Acid LMW (Oligo)',
                'type' => 'raw_material',
                'inci_name' => 'Sodium Hyaluronate',
                'technical_name' => 'Low Molecular Weight Hyaluronic Acid (5-10 kDa)',
                'cas_number' => '9067-32-7',
                'allergens' => 'None',
                'function' => 'Active',
                'unit' => 'kg',
                'cost_per_unit' => 320.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'xan' => [
                'code' => 'RM-00062',
                'name' => 'Xanthan Gum Transparent Grade',
                'type' => 'raw_material',
                'inci_name' => 'Xanthan Gum',
                'technical_name' => 'Transparent High Clarity Xanthan',
                'cas_number' => '11138-66-2',
                'allergens' => 'None',
                'function' => 'Rheology Modifier',
                'unit' => 'kg',
                'cost_per_unit' => 7.50,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'toc' => [
                'code' => 'RM-00077',
                'name' => 'Natural Tocopherol 70% (Vit E)',
                'type' => 'raw_material',
                'inci_name' => 'Tocopherol',
                'technical_name' => 'Non-GMO D-Mixed Tocopherols 70%',
                'cas_number' => '1406-18-4',
                'allergens' => 'None',
                'function' => 'Antioxidant',
                'unit' => 'kg',
                'cost_per_unit' => 28.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'vitc' => [
                'code' => 'RM-00085',
                'name' => 'Ascorbyl Glucoside AA2G',
                'type' => 'raw_material',
                'inci_name' => 'Ascorbyl Glucoside',
                'technical_name' => 'Stabilized Bio-active Vitamin C AA2G',
                'cas_number' => '129499-78-1',
                'allergens' => 'None',
                'function' => 'Active',
                'unit' => 'kg',
                'cost_per_unit' => 75.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'poly20' => [
                'code' => 'RM-00033',
                'name' => 'Polysorbate 20 (Tween 20)',
                'type' => 'raw_material',
                'inci_name' => 'Polysorbate 20',
                'technical_name' => 'High Purity Non-ionic Solubilizer',
                'cas_number' => '9005-64-5',
                'allergens' => 'None',
                'function' => 'Solubilizer',
                'unit' => 'kg',
                'cost_per_unit' => 4.10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'bot' => [
                'code' => 'PK-00414',
                'name' => 'Glass Dropper Bottle 30ml Amber',
                'type' => 'packaging',
                'inci_name' => null,
                'technical_name' => 'Flacon verre 30ml ambré + Pipette silicone',
                'cas_number' => null,
                'allergens' => 'None',
                'function' => 'Primary Packaging',
                'unit' => 'pcs',
                'cost_per_unit' => 0.45,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'jar' => [
                'code' => 'PK-00520',
                'name' => 'Frosted Glass Cosmetic Jar 50ml',
                'type' => 'packaging',
                'inci_name' => null,
                'technical_name' => 'Heavy Base Frosted Glass Jar + Matte Silver Cap',
                'cas_number' => null,
                'allergens' => 'None',
                'function' => 'Primary Packaging',
                'unit' => 'pcs',
                'cost_per_unit' => 0.65,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'fg1' => [
                'code' => 'FG-250415',
                'name' => 'Anti-Aging Renewal Cream 50ml',
                'type' => 'finished_good',
                'inci_name' => null,
                'technical_name' => 'Finished Anti-Aging Cream in Boxed Jar',
                'cas_number' => null,
                'allergens' => 'Linalool, Citronellol',
                'function' => 'Finished Cosmetic Product',
                'unit' => 'pcs',
                'cost_per_unit' => 4.85,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'fg2' => [
                'code' => 'FG-250418',
                'name' => 'HydraGlow Hyaluronic Vitamin C Serum 30ml',
                'type' => 'finished_good',
                'inci_name' => null,
                'technical_name' => 'Finished Glowing Serum in Dropper Bottle',
                'cas_number' => null,
                'allergens' => 'None',
                'function' => 'Finished Cosmetic Product',
                'unit' => 'pcs',
                'cost_per_unit' => 3.90,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            'fg3' => [
                'code' => 'FG-250422',
                'name' => 'Gentle Purifying Micellar Water 200ml',
                'type' => 'finished_good',
                'inci_name' => null,
                'technical_name' => 'Finished Micellar Solution in 200ml Bottle',
                'cas_number' => null,
                'allergens' => 'None',
                'function' => 'Finished Cosmetic Product',
                'unit' => 'pcs',
                'cost_per_unit' => 2.10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $itemIds = [];
        foreach ($itemsData as $key => $data) {
            $itemIds[$key] = DB::table('items')->insertGetId($data);
        }

        // 2. Inventory Lots
        $lotsData = [
            [
                'lot_number' => 'PW-250415',
                'item_id' => $itemIds['aqua'],
                'status' => 'Approved',
                'quantity' => 15000.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · Tank Bay 01',
                'expiry_date' => '2026-10-31',
                'supplier_name' => 'On-Site Osmosis EP Generation',
                'source_document' => 'WTP-2025-0041',
            ],
            [
                'lot_number' => 'GLY-8821-IT',
                'item_id' => $itemIds['gly'],
                'status' => 'Approved',
                'quantity' => 1240.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A03 · S12',
                'expiry_date' => '2025-05-02',
                'supplier_name' => 'Cosmetica Materie S.p.A.',
                'source_document' => 'GR-2025-00842',
            ],
            [
                'lot_number' => 'NIA-24091',
                'item_id' => $itemIds['nia'],
                'status' => 'Approved',
                'quantity' => 320.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A02 · S04',
                'expiry_date' => '2026-08-15',
                'supplier_name' => 'Lonza Special Ingredients',
                'source_document' => 'GR-2025-00711',
            ],
            [
                'lot_number' => 'CET-25011',
                'item_id' => $itemIds['cet'],
                'status' => 'Approved',
                'quantity' => 850.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A01 · S09',
                'expiry_date' => '2026-11-20',
                'supplier_name' => 'BASF Personal Care Europe',
                'source_document' => 'GR-2025-00910',
            ],
            [
                'lot_number' => 'MCT-24089',
                'item_id' => $itemIds['mct'],
                'status' => 'Approved',
                'quantity' => 920.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A02 · S07',
                'expiry_date' => '2026-04-10',
                'supplier_name' => 'Oleon Natural Chemistry',
                'source_document' => 'GR-2025-00788',
            ],
            [
                'lot_number' => 'SHE-25004',
                'item_id' => $itemIds['shea'],
                'status' => 'Approved',
                'quantity' => 450.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A03 · S05',
                'expiry_date' => '2026-01-18',
                'supplier_name' => 'AAK Personal Care Sweden',
                'source_document' => 'GR-2025-00812',
            ],
            [
                'lot_number' => 'RET-24018',
                'item_id' => $itemIds['ret'],
                'status' => 'Quarantine',
                'quantity' => 18.50,
                'unit' => 'kg',
                'storage_location' => 'WH-Q · Quarantine Bay · Q01',
                'expiry_date' => '2025-06-18',
                'supplier_name' => 'Retinoid Labs Europe',
                'source_document' => 'GR-2025-00850',
            ],
            [
                'lot_number' => 'RET-23992',
                'item_id' => $itemIds['ret'],
                'status' => 'Approved',
                'quantity' => 25.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A04 · S02',
                'expiry_date' => '2025-12-10',
                'supplier_name' => 'Retinoid Labs Europe',
                'source_document' => 'GR-2024-00620',
            ],
            [
                'lot_number' => 'EUX-25012',
                'item_id' => $itemIds['eux'],
                'status' => 'Approved',
                'quantity' => 210.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A02 · S11',
                'expiry_date' => '2027-02-14',
                'supplier_name' => 'Schülke & Mayr GmbH',
                'source_document' => 'GR-2025-00902',
            ],
            [
                'lot_number' => 'RF-25019',
                'item_id' => $itemIds['rf'],
                'status' => 'Approved',
                'quantity' => 85.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A04 · S08',
                'expiry_date' => '2026-09-30',
                'supplier_name' => 'Givaudan Fragrances S.A.',
                'source_document' => 'GR-2025-00890',
            ],
            [
                'lot_number' => 'HA-25-0031',
                'item_id' => $itemIds['ha'],
                'status' => 'Approved',
                'quantity' => 42.75,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A01 · S08',
                'expiry_date' => '2026-03-04',
                'supplier_name' => 'BioActive Swiss AG',
                'source_document' => 'GR-2025-00790',
            ],
            [
                'lot_number' => 'XAN-24102',
                'item_id' => $itemIds['xan'],
                'status' => 'Approved',
                'quantity' => 180.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A01 · S14',
                'expiry_date' => '2027-01-10',
                'supplier_name' => 'CP Kelco BioGums',
                'source_document' => 'GR-2025-00822',
            ],
            [
                'lot_number' => 'TOC-25008',
                'item_id' => $itemIds['toc'],
                'status' => 'Approved',
                'quantity' => 65.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A03 · S02',
                'expiry_date' => '2026-07-22',
                'supplier_name' => 'DSM Nutritional Products',
                'source_document' => 'GR-2025-00860',
            ],
            [
                'lot_number' => 'VC-25015',
                'item_id' => $itemIds['vitc'],
                'status' => 'Approved',
                'quantity' => 95.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A02 · S01',
                'expiry_date' => '2026-12-05',
                'supplier_name' => 'Hayashibara Japan',
                'source_document' => 'GR-2025-00874',
            ],
            [
                'lot_number' => 'POL-24099',
                'item_id' => $itemIds['poly20'],
                'status' => 'Approved',
                'quantity' => 310.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A02 · S09',
                'expiry_date' => '2026-10-18',
                'supplier_name' => 'Croda Beauty Actives',
                'source_document' => 'GR-2025-00755',
            ],
            [
                'lot_number' => 'BOT-250322',
                'item_id' => $itemIds['bot'],
                'status' => 'Approved',
                'quantity' => 4800.00,
                'unit' => 'pcs',
                'storage_location' => 'WH-P · P07 · S02',
                'expiry_date' => null,
                'supplier_name' => 'Vetro Packaging Italia',
                'source_document' => 'GR-2025-00780',
            ],
            [
                'lot_number' => 'JAR-250210',
                'item_id' => $itemIds['jar'],
                'status' => 'Approved',
                'quantity' => 6200.00,
                'unit' => 'pcs',
                'storage_location' => 'WH-P · P04 · S01',
                'expiry_date' => null,
                'supplier_name' => 'Bormioli Luigi Glassworks',
                'source_document' => 'GR-2025-00762',
            ],
            [
                'lot_number' => 'REJ-SHE-2309',
                'item_id' => $itemIds['shea'],
                'status' => 'Rejected',
                'quantity' => 80.00,
                'unit' => 'kg',
                'storage_location' => 'WH-R · Isolation Cage · R02',
                'expiry_date' => '2025-03-01',
                'supplier_name' => 'Unverified African Imports',
                'source_document' => 'GR-2024-00410',
            ],
        ];

        $lotIds = [];
        foreach ($lotsData as $ld) {
            $ld['created_at'] = $now->copy()->subDays(rand(5, 45));
            $ld['updated_at'] = $now;
            $lotIds[$ld['lot_number']] = DB::table('lots')->insertGetId($ld);

            // Log initial receipt in immutable ledger
            DB::table('inventory_ledger')->insert([
                'lot_id' => $lotIds[$ld['lot_number']],
                'transaction_type' => 'receipt',
                'quantity_change' => $ld['quantity'],
                'running_balance' => $ld['quantity'],
                'source_document' => $ld['source_document'],
                'user_name' => 'Giulia Romano (Warehouse Lead)',
                'notes' => "Initial Goods Receipt under GMP Protocol ISO 22716. Vendor: {$ld['supplier_name']}",
                'created_at' => $ld['created_at'],
            ]);
        }

        // 3. Formulations Master Data
        // ----------------------------------------------------
        // Formula 1: Anti-Aging Renewal Cream 50ml (FRM-SC-0042)
        // ----------------------------------------------------
        $form1Instructions = [
            [
                'step' => 1,
                'phase' => 'Phase A - Aqueous Phase',
                'temp' => '75°C ± 2°C',
                'text' => 'Charge Purified Water into main vacuum homogenizer. Add Glycerin and slowly disperse Xanthan Gum under high shear (2,200 RPM) until fully hydrated. Dissolve Niacinamide USP completely. Heat vessel to 75°C.'
            ],
            [
                'step' => 2,
                'phase' => 'Phase B - Lipid Phase',
                'temp' => '75°C ± 2°C',
                'text' => 'In auxiliary melting kettle, combine Cetearyl Alcohol, Caprylic/Capric Triglyceride, Organic Shea Butter, and Tocopherol. Heat to 75°C until molten, clear, and homogeneous.'
            ],
            [
                'step' => 3,
                'phase' => 'Emulsification & Homogenization',
                'temp' => '75°C -> 50°C',
                'text' => 'Draw Phase B into Phase A under vacuum (-0.65 bar). Engage bottom homogenizer at 3,200 RPM for 15 minutes. Verify glossy O/W emulsion formation. Begin gentle planetary scraper cooling at 35 RPM.'
            ],
            [
                'step' => 4,
                'phase' => 'Phase C - Cool Down Actives',
                'temp' => '< 38°C',
                'text' => 'Cool bulk to below 38°C. Introduce Encapsulated Retinol Liposomes, Euxyl PE 9010 preservative, and Signature Bloom fragrance. Mix for 25 minutes until uniform without aerating.'
            ],
            [
                'step' => 5,
                'phase' => 'Quality Sampling & Transfer',
                'temp' => '25°C',
                'text' => 'Draw representative 500g QC sample. Verify appearance, pH (5.80 ± 0.3), viscosity (21,000 ± 3,000 cP). Pump through 100-mesh filter into sterile bulk holding tote for filling.'
            ]
        ];

        $form1Id = DB::table('formulations')->insertGetId([
            'code' => 'FRM-SC-0042',
            'product_name' => 'Anti-Aging Renewal Cream 50ml',
            'category' => 'Skincare & Emulsions',
            'version' => 'v2.4',
            'status' => 'Approved',
            'target_batch_size' => 1000.00,
            'author_name' => 'Dr. Sofia Bianchi',
            'ph_spec' => '5.50 - 6.50',
            'viscosity_spec' => '18,000 - 24,000 cP',
            'density_spec' => '0.98 - 1.02 g/ml',
            'method_instructions' => json_encode($form1Instructions),
            'effective_date' => '2026-04-01',
            'created_at' => $now->copy()->subDays(60),
            'updated_at' => $now,
        ]);

        $form1Lines = [
            ['phase' => 'A', 'item_id' => $itemIds['aqua'], 'pct' => 67.650, 'qty' => 676.50, 'func' => 'Solvent', 'temp' => '75°C', 'notes' => 'Demineralized EP Water - Charge first into main kettle'],
            ['phase' => 'A', 'item_id' => $itemIds['gly'], 'pct' => 5.000, 'qty' => 50.00, 'func' => 'Humectant', 'temp' => '75°C', 'notes' => 'Dissolve completely before polymer dispersion'],
            ['phase' => 'A', 'item_id' => $itemIds['nia'], 'pct' => 4.000, 'qty' => 40.00, 'func' => 'Active', 'temp' => '75°C', 'notes' => 'Vitamin B3 USP - Active barrier repair'],
            ['phase' => 'A', 'item_id' => $itemIds['xan'], 'pct' => 0.300, 'qty' => 3.00, 'func' => 'Rheology Modifier', 'temp' => '75°C', 'notes' => 'Disperse under 2,200 RPM high shear until lump-free'],
            ['phase' => 'B', 'item_id' => $itemIds['cet'], 'pct' => 6.000, 'qty' => 60.00, 'func' => 'Emulsifier', 'temp' => '75°C', 'notes' => 'Lanette O - Gives lamellar crystal body and stability'],
            ['phase' => 'B', 'item_id' => $itemIds['mct'], 'pct' => 8.000, 'qty' => 80.00, 'func' => 'Emollient', 'temp' => '75°C', 'notes' => 'Light dry silky skin feel carrier oil'],
            ['phase' => 'B', 'item_id' => $itemIds['shea'], 'pct' => 5.000, 'qty' => 50.00, 'func' => 'Emollient', 'temp' => '75°C', 'notes' => 'Organic Refined Shea Butter - Deep lipid barrier repair'],
            ['phase' => 'B', 'item_id' => $itemIds['toc'], 'pct' => 1.000, 'qty' => 10.00, 'func' => 'Antioxidant', 'temp' => '75°C', 'notes' => 'Protects lipids from oxidation'],
            ['phase' => 'C', 'item_id' => $itemIds['ret'], 'pct' => 1.000, 'qty' => 10.00, 'func' => 'Active', 'temp' => '< 38°C', 'notes' => 'Thermo-sensitive liposomes - Add strictly below 38°C'],
            ['phase' => 'C', 'item_id' => $itemIds['eux'], 'pct' => 1.000, 'qty' => 10.00, 'func' => 'Preservative', 'temp' => '< 38°C', 'notes' => 'Broad-spectrum antimicrobial protection ISO 17516'],
            ['phase' => 'C', 'item_id' => $itemIds['rf'], 'pct' => 1.050, 'qty' => 10.50, 'func' => 'Fragrance', 'temp' => '< 38°C', 'notes' => 'IFRA 50th compliant hypoallergenic rose bouquet'],
        ];

        foreach ($form1Lines as $line) {
            DB::table('formulation_lines')->insert([
                'formulation_id' => $form1Id,
                'phase' => $line['phase'],
                'item_id' => $line['item_id'],
                'percentage' => $line['pct'],
                'function' => $line['func'],
                'scale_qty' => $line['qty'],
                'target_temp' => $line['temp'],
                'notes' => $line['notes'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('formulation_approvals')->insert([
            ['formulation_id' => $form1Id, 'role_title' => 'Formulator (R&D)', 'approver_name' => 'Dr. Sofia Bianchi', 'initials' => 'SB', 'signed_at' => Carbon::parse('2026-03-27 16:42:00')],
            ['formulation_id' => $form1Id, 'role_title' => 'Lab Supervisor', 'approver_name' => 'Luca Bellini', 'initials' => 'LB', 'signed_at' => Carbon::parse('2026-03-28 11:08:00')],
            ['formulation_id' => $form1Id, 'role_title' => 'QA Head', 'approver_name' => 'Elena Rossi', 'initials' => 'ER', 'signed_at' => Carbon::parse('2026-03-29 09:14:00')],
        ]);

        // ----------------------------------------------------
        // Formula 2: HydraGlow Hyaluronic Vitamin C Serum 30ml (FRM-SE-0108)
        // ----------------------------------------------------
        $form2Instructions = [
            [
                'step' => 1,
                'phase' => 'Phase A - Aqueous Solubilization',
                'temp' => 'Ambient (22-25°C)',
                'text' => 'Charge cold Purified Water. Disperse Low Molecular Weight Hyaluronic Acid. Stir slowly for 45 minutes until transparent gel forms.'
            ],
            [
                'step' => 2,
                'phase' => 'Phase A - Active Addition',
                'temp' => 'Ambient (25°C)',
                'text' => 'Dissolve Ascorbyl Glucoside AA2G and Niacinamide USP. Check clear dissolution.'
            ],
            [
                'step' => 3,
                'phase' => 'Phase B - Micro-solubilization',
                'temp' => 'Ambient (25°C)',
                'text' => 'Premix Polysorbate 20, Fragrance, and Tocopherol in separate vessel. Slowly add into main aqueous bulk with paddle mixer.'
            ],
            [
                'step' => 4,
                'phase' => 'Phase C - Preservation',
                'temp' => 'Ambient (25°C)',
                'text' => 'Add Euxyl PE 9010 preservative. Homogenize for 10 minutes at 1,500 RPM. Verify final pH (5.60).'
            ]
        ];

        $form2Id = DB::table('formulations')->insertGetId([
            'code' => 'FRM-SE-0108',
            'product_name' => 'HydraGlow Hyaluronic Vitamin C Serum 30ml',
            'category' => 'Concentrated Serums',
            'version' => 'v1.8',
            'status' => 'Approved',
            'target_batch_size' => 500.00,
            'author_name' => 'Dr. Sofia Bianchi',
            'ph_spec' => '5.20 - 6.00',
            'viscosity_spec' => '2,500 - 4,500 cP',
            'density_spec' => '1.02 - 1.05 g/ml',
            'method_instructions' => json_encode($form2Instructions),
            'effective_date' => '2026-02-15',
            'created_at' => $now->copy()->subDays(90),
            'updated_at' => $now,
        ]);

        $form2Lines = [
            ['phase' => 'A', 'item_id' => $itemIds['aqua'], 'pct' => 84.400, 'qty' => 422.00, 'func' => 'Solvent', 'temp' => 'Ambient', 'notes' => 'Purified Osmosis Water'],
            ['phase' => 'A', 'item_id' => $itemIds['gly'], 'pct' => 4.000, 'qty' => 20.00, 'func' => 'Humectant', 'temp' => 'Ambient', 'notes' => 'Hydrating booster'],
            ['phase' => 'A', 'item_id' => $itemIds['ha'], 'pct' => 1.500, 'qty' => 7.50, 'func' => 'Active', 'temp' => 'Ambient', 'notes' => 'Deep penetration oligo hyaluronic acid'],
            ['phase' => 'A', 'item_id' => $itemIds['vitc'], 'pct' => 5.000, 'qty' => 25.00, 'func' => 'Active', 'temp' => 'Ambient', 'notes' => 'Stabilized AA2G Vitamin C for intense brightening'],
            ['phase' => 'A', 'item_id' => $itemIds['nia'], 'pct' => 2.000, 'qty' => 10.00, 'func' => 'Active', 'temp' => 'Ambient', 'notes' => 'Synergistic barrier reinforcement with Vit C'],
            ['phase' => 'B', 'item_id' => $itemIds['poly20'], 'pct' => 1.500, 'qty' => 7.50, 'func' => 'Solubilizer', 'temp' => 'Ambient', 'notes' => 'Clears oil-based aromatics'],
            ['phase' => 'B', 'item_id' => $itemIds['rf'], 'pct' => 0.300, 'qty' => 1.50, 'func' => 'Fragrance', 'temp' => 'Ambient', 'notes' => 'Subtle clean floral trace'],
            ['phase' => 'B', 'item_id' => $itemIds['toc'], 'pct' => 0.300, 'qty' => 1.50, 'func' => 'Antioxidant', 'temp' => 'Ambient', 'notes' => 'Vitamin C synergistic stabilizer'],
            ['phase' => 'C', 'item_id' => $itemIds['eux'], 'pct' => 1.000, 'qty' => 5.00, 'func' => 'Preservative', 'temp' => 'Ambient', 'notes' => 'Preservative broad spectrum'],
        ];

        foreach ($form2Lines as $line) {
            DB::table('formulation_lines')->insert([
                'formulation_id' => $form2Id,
                'phase' => $line['phase'],
                'item_id' => $line['item_id'],
                'percentage' => $line['pct'],
                'function' => $line['func'],
                'scale_qty' => $line['qty'],
                'target_temp' => $line['temp'],
                'notes' => $line['notes'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('formulation_approvals')->insert([
            ['formulation_id' => $form2Id, 'role_title' => 'Formulator (R&D)', 'approver_name' => 'Dr. Sofia Bianchi', 'initials' => 'SB', 'signed_at' => Carbon::parse('2026-02-10 14:20:00')],
            ['formulation_id' => $form2Id, 'role_title' => 'Lab Supervisor', 'approver_name' => 'Luca Bellini', 'initials' => 'LB', 'signed_at' => Carbon::parse('2026-02-12 10:15:00')],
            ['formulation_id' => $form2Id, 'role_title' => 'QA Head', 'approver_name' => 'Elena Rossi', 'initials' => 'ER', 'signed_at' => Carbon::parse('2026-02-14 16:30:00')],
        ]);

        // ----------------------------------------------------
        // Formula 3: Gentle Purifying Micellar Water 200ml (FRM-CL-0025)
        // ----------------------------------------------------
        $form3Id = DB::table('formulations')->insertGetId([
            'code' => 'FRM-CL-0025',
            'product_name' => 'Gentle Purifying Micellar Water 200ml',
            'category' => 'Cleansers & Toners',
            'version' => 'v3.1',
            'status' => 'Approved',
            'target_batch_size' => 2000.00,
            'author_name' => 'Dr. Matteo Ferri',
            'ph_spec' => '5.00 - 5.80',
            'viscosity_spec' => 'Water-thin (< 50 cP)',
            'density_spec' => '1.00 - 1.01 g/ml',
            'method_instructions' => json_encode([
                ['step' => 1, 'phase' => 'Phase A - Base Solution', 'temp' => 'Ambient', 'text' => 'Charge purified water into tank. Add Glycerin, Niacinamide and stir for 15 minutes.'],
                ['step' => 2, 'phase' => 'Phase B - Micellar Aggregation', 'temp' => 'Ambient', 'text' => 'Premix Polysorbate 20 and delicate fragrance. Pump into bulk water under low shear to avoid foaming.'],
                ['step' => 3, 'phase' => 'Phase C - Preservation', 'temp' => 'Ambient', 'text' => 'Add Euxyl PE 9010. Stir for 20 minutes until completely sparkling clear. Check pH and clarity.']
            ]),
            'effective_date' => '2026-01-10',
            'created_at' => $now->copy()->subDays(120),
            'updated_at' => $now,
        ]);

        $form3Lines = [
            ['phase' => 'A', 'item_id' => $itemIds['aqua'], 'pct' => 91.500, 'qty' => 1830.00, 'func' => 'Solvent', 'temp' => 'Ambient', 'notes' => 'Purified EP Demineralized Water'],
            ['phase' => 'A', 'item_id' => $itemIds['gly'], 'pct' => 3.500, 'qty' => 70.00, 'func' => 'Humectant', 'temp' => 'Ambient', 'notes' => 'Vegetable Glycerin 99.5%'],
            ['phase' => 'A', 'item_id' => $itemIds['nia'], 'pct' => 1.000, 'qty' => 20.00, 'func' => 'Active', 'temp' => 'Ambient', 'notes' => 'Soothing skin conditioner'],
            ['phase' => 'B', 'item_id' => $itemIds['poly20'], 'pct' => 2.800, 'qty' => 56.00, 'func' => 'Surfactant / Solubilizer', 'temp' => 'Ambient', 'notes' => 'Forms non-irritating cleansing micelles'],
            ['phase' => 'B', 'item_id' => $itemIds['rf'], 'pct' => 0.200, 'qty' => 4.00, 'func' => 'Fragrance', 'temp' => 'Ambient', 'notes' => 'Ultra-low concentration hypoallergenic fragrance'],
            ['phase' => 'C', 'item_id' => $itemIds['eux'], 'pct' => 1.000, 'qty' => 20.00, 'func' => 'Preservative', 'temp' => 'Ambient', 'notes' => 'Broad spectrum preservation'],
        ];

        foreach ($form3Lines as $line) {
            DB::table('formulation_lines')->insert([
                'formulation_id' => $form3Id,
                'phase' => $line['phase'],
                'item_id' => $line['item_id'],
                'percentage' => $line['pct'],
                'function' => $line['func'],
                'scale_qty' => $line['qty'],
                'target_temp' => $line['temp'],
                'notes' => $line['notes'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('formulation_approvals')->insert([
            ['formulation_id' => $form3Id, 'role_title' => 'Formulator (R&D)', 'approver_name' => 'Dr. Matteo Ferri', 'initials' => 'MF', 'signed_at' => Carbon::parse('2026-01-05 15:10:00')],
            ['formulation_id' => $form3Id, 'role_title' => 'Lab Supervisor', 'approver_name' => 'Luca Bellini', 'initials' => 'LB', 'signed_at' => Carbon::parse('2026-01-08 11:45:00')],
            ['formulation_id' => $form3Id, 'role_title' => 'QA Head', 'approver_name' => 'Elena Rossi', 'initials' => 'ER', 'signed_at' => Carbon::parse('2026-01-09 17:00:00')],
        ]);

        // ----------------------------------------------------
        // Formula 4: Velvet Botanical Lip Balm 15ml (FRM-BM-0015 - Under Review)
        // ----------------------------------------------------
        $form4Id = DB::table('formulations')->insertGetId([
            'code' => 'FRM-BM-0015',
            'product_name' => 'Velvet Botanical Lip Treatment Balm 15ml',
            'category' => 'Lip Care & Balms',
            'version' => 'v1.2',
            'status' => 'Under Review',
            'target_batch_size' => 250.00,
            'author_name' => 'Dr. Sofia Bianchi',
            'ph_spec' => 'Anhydrous (N/A)',
            'viscosity_spec' => 'Solid / Semi-solid balm',
            'density_spec' => '0.92 - 0.95 g/ml',
            'method_instructions' => json_encode([
                ['step' => 1, 'phase' => 'Phase A - Anhydrous Melt', 'temp' => '80°C', 'text' => 'Melt Shea Butter and Cetearyl Alcohol at 80°C.'],
                ['step' => 2, 'phase' => 'Phase B - Fluid Oil Blending', 'temp' => '65°C', 'text' => 'Add MCT Oil and Tocopherol under low speed.'],
                ['step' => 3, 'phase' => 'Phase C - Aroma & Pour', 'temp' => '55°C', 'text' => 'Add Natural Fragrance at 55°C, stir 5 minutes and hot-pour directly into jars at 50°C.']
            ]),
            'effective_date' => '2026-05-01',
            'created_at' => $now->copy()->subDays(15),
            'updated_at' => $now,
        ]);

        $form4Lines = [
            ['phase' => 'A', 'item_id' => $itemIds['shea'], 'pct' => 45.000, 'qty' => 112.50, 'func' => 'Emollient Base', 'temp' => '80°C', 'notes' => 'Organic Refined Shea Butter'],
            ['phase' => 'A', 'item_id' => $itemIds['cet'], 'pct' => 15.000, 'qty' => 37.50, 'func' => 'Consistency Modifier', 'temp' => '80°C', 'notes' => 'Provides structural rigidity'],
            ['phase' => 'B', 'item_id' => $itemIds['mct'], 'pct' => 36.500, 'qty' => 91.25, 'func' => 'Emollient Oil', 'temp' => '65°C', 'notes' => 'Silky cushion texture'],
            ['phase' => 'B', 'item_id' => $itemIds['toc'], 'pct' => 2.000, 'qty' => 5.00, 'func' => 'Antioxidant', 'temp' => '65°C', 'notes' => 'Protects butters from rancidity'],
            ['phase' => 'C', 'item_id' => $itemIds['rf'], 'pct' => 1.500, 'qty' => 3.75, 'func' => 'Fragrance', 'temp' => '55°C', 'notes' => 'Sweet Rose Petal food-grade aroma'],
        ];

        foreach ($form4Lines as $line) {
            DB::table('formulation_lines')->insert([
                'formulation_id' => $form4Id,
                'phase' => $line['phase'],
                'item_id' => $line['item_id'],
                'percentage' => $line['pct'],
                'function' => $line['func'],
                'scale_qty' => $line['qty'],
                'target_temp' => $line['temp'],
                'notes' => $line['notes'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('formulation_approvals')->insert([
            ['formulation_id' => $form4Id, 'role_title' => 'Formulator (R&D)', 'approver_name' => 'Dr. Sofia Bianchi', 'initials' => 'SB', 'signed_at' => Carbon::parse('2026-10-25 09:30:00')],
        ]);

        // 4. Production Batches
        $batch1Id = DB::table('production_batches')->insertGetId([
            'batch_number' => 'B-25-0415',
            'formulation_id' => $form1Id,
            'product_name' => 'Anti-Aging Renewal Cream 50ml',
            'target_size' => 1000.00,
            'line_code' => 'L01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'progress' => 74,
            'status' => 'Running',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $batch2Id = DB::table('production_batches')->insertGetId([
            'batch_number' => 'B-25-0398',
            'formulation_id' => $form2Id,
            'product_name' => 'HydraGlow Hyaluronic Vitamin C Serum 30ml',
            'target_size' => 500.00,
            'line_code' => 'L03',
            'start_time' => '06:30',
            'end_time' => '14:00',
            'progress' => 100,
            'status' => 'Released',
            'created_at' => $now->copy()->subDays(3),
            'updated_at' => $now,
        ]);

        DB::table('production_batches')->insert([
            ['batch_number' => 'B-25-0414', 'formulation_id' => $form4Id, 'product_name' => 'Velvet Botanical Lip Balm 15ml', 'target_size' => 250.00, 'line_code' => 'L02', 'start_time' => '07:00', 'end_time' => '15:30', 'progress' => 54, 'status' => 'Running', 'created_at' => $now, 'updated_at' => $now],
            ['batch_number' => 'B-25-0416', 'formulation_id' => $form3Id, 'product_name' => 'Gentle Purifying Micellar Water 200ml', 'target_size' => 2000.00, 'line_code' => 'L04', 'start_time' => '10:30', 'end_time' => '18:00', 'progress' => 12, 'status' => 'Changeover', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 5. Dispense Steps for Batch B-25-0415 (Anti-Aging Cream)
        DB::table('batch_dispense_steps')->insert([
            [
                'batch_id' => $batch1Id,
                'step_number' => 1,
                'phase' => 'A',
                'material_name' => 'Purified Water EP/USP',
                'item_code' => 'RM-00001',
                'scanned_lot_number' => 'PW-250415',
                'target_weight' => 676.50,
                'actual_weight' => 676.50,
                'tolerance_percent' => 0.50,
                'scale_id' => 'WS-02',
                'operator_name' => 'Giulia Romano',
                'is_signed' => true,
                'signed_at' => $now->copy()->subHours(2),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'batch_id' => $batch1Id,
                'step_number' => 2,
                'phase' => 'A',
                'material_name' => 'Vegetable Glycerin 99.5%',
                'item_code' => 'RM-00142',
                'scanned_lot_number' => 'GLY-8821-IT',
                'target_weight' => 50.00,
                'actual_weight' => 50.042,
                'tolerance_percent' => 0.50,
                'scale_id' => 'WS-02',
                'operator_name' => 'Giulia Romano',
                'is_signed' => true,
                'signed_at' => $now->copy()->subMinutes(15),
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'batch_id' => $batch1Id,
                'step_number' => 3,
                'phase' => 'A',
                'material_name' => 'Niacinamide USP (Vitamin B3)',
                'item_code' => 'RM-00056',
                'scanned_lot_number' => 'NIA-24091',
                'target_weight' => 40.00,
                'actual_weight' => null,
                'tolerance_percent' => 0.50,
                'scale_id' => 'WS-02',
                'operator_name' => null,
                'is_signed' => false,
                'signed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'batch_id' => $batch1Id,
                'step_number' => 4,
                'phase' => 'A',
                'material_name' => 'Xanthan Gum Transparent Grade',
                'item_code' => 'RM-00062',
                'scanned_lot_number' => 'XAN-24102',
                'target_weight' => 3.00,
                'actual_weight' => null,
                'tolerance_percent' => 0.50,
                'scale_id' => 'WS-02',
                'operator_name' => null,
                'is_signed' => false,
                'signed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'batch_id' => $batch1Id,
                'step_number' => 5,
                'phase' => 'B',
                'material_name' => 'Cetearyl Alcohol (Lanette O)',
                'item_code' => 'RM-00104',
                'scanned_lot_number' => 'CET-25011',
                'target_weight' => 60.00,
                'actual_weight' => null,
                'tolerance_percent' => 0.50,
                'scale_id' => 'WS-02',
                'operator_name' => null,
                'is_signed' => false,
                'signed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'batch_id' => $batch1Id,
                'step_number' => 6,
                'phase' => 'B',
                'material_name' => 'Caprylic/Capric Triglyceride (MCT)',
                'item_code' => 'RM-00118',
                'scanned_lot_number' => 'MCT-24089',
                'target_weight' => 80.00,
                'actual_weight' => null,
                'tolerance_percent' => 0.50,
                'scale_id' => 'WS-02',
                'operator_name' => null,
                'is_signed' => false,
                'signed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'batch_id' => $batch1Id,
                'step_number' => 7,
                'phase' => 'B',
                'material_name' => 'Organic Shea Butter Refined',
                'item_code' => 'RM-00135',
                'scanned_lot_number' => 'SHE-25004',
                'target_weight' => 50.00,
                'actual_weight' => null,
                'tolerance_percent' => 0.50,
                'scale_id' => 'WS-02',
                'operator_name' => null,
                'is_signed' => false,
                'signed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'batch_id' => $batch1Id,
                'step_number' => 8,
                'phase' => 'B',
                'material_name' => 'Natural Tocopherol 70%',
                'item_code' => 'RM-00077',
                'scanned_lot_number' => 'TOC-25008',
                'target_weight' => 10.00,
                'actual_weight' => null,
                'tolerance_percent' => 0.50,
                'scale_id' => 'WS-02',
                'operator_name' => null,
                'is_signed' => false,
                'signed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'batch_id' => $batch1Id,
                'step_number' => 9,
                'phase' => 'C',
                'material_name' => 'Retinol 10% Encapsulated Liposomes',
                'item_code' => 'RM-00028',
                'scanned_lot_number' => 'RET-23992',
                'target_weight' => 10.00,
                'actual_weight' => null,
                'tolerance_percent' => 0.50,
                'scale_id' => 'WS-02',
                'operator_name' => null,
                'is_signed' => false,
                'signed_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // 6. QC Samples & Test Results
        $sampleId = DB::table('qc_samples')->insertGetId([
            'sample_number' => 'QC-S-004182',
            'batch_number' => 'B-25-0398',
            'product_name' => 'HydraGlow Hyaluronic Vitamin C Serum 30ml',
            'status' => 'Pending QA release',
            'analyst_name' => 'Luca Bellini (Senior Analyst)',
            'coa_filename' => 'COA-QC-004182.pdf',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('qc_test_results')->insert([
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Appearance', 'min_spec' => 'Translucent viscous serum', 'max_spec' => null, 'actual_result' => 'Conforms (Translucent)', 'status' => 'Pass', 'created_at' => $now, 'updated_at' => $now],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Color', 'min_spec' => 'Pale golden yellow', 'max_spec' => null, 'actual_result' => 'Pale golden yellow', 'status' => 'Pass', 'created_at' => $now, 'updated_at' => $now],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Odor', 'min_spec' => 'Characteristic light floral', 'max_spec' => null, 'actual_result' => 'Characteristic light floral', 'status' => 'Pass', 'created_at' => $now, 'updated_at' => $now],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'pH @ 25°C', 'min_spec' => '5.20', 'max_spec' => '6.00', 'actual_result' => '5.58', 'status' => 'Pass', 'created_at' => $now, 'updated_at' => $now],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Viscosity @ 25°C', 'min_spec' => '2,500 cP', 'max_spec' => '4,500 cP', 'actual_result' => '3,420 cP', 'status' => 'Pass', 'created_at' => $now, 'updated_at' => $now],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Density @ 20°C', 'min_spec' => '1.020 g/ml', 'max_spec' => '1.050 g/ml', 'actual_result' => '1.034 g/ml', 'status' => 'Pass', 'created_at' => $now, 'updated_at' => $now],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Microbiology (TVC)', 'min_spec' => '< 100 CFU/g (ISO 17516)', 'max_spec' => '< 100 CFU/g', 'actual_result' => '< 10 CFU/g (Conforms)', 'status' => 'Pass', 'created_at' => $now, 'updated_at' => $now],
        ]);

        // 7. Traceability Genealogy
        DB::table('traceability_records')->insert([
            'finished_lot' => 'FG-250415-A',
            'supplier_po' => 'PO-2025-0189',
            'supplier_name' => 'Cosmetica Materie S.p.A. & Lonza Special Ingredients',
            'raw_materials' => json_encode([
                ['lot' => 'GLY-8821-IT', 'name' => 'Vegetable Glycerin 99.5%', 'qty' => '50.04 kg', 'status' => 'Approved'],
                ['lot' => 'NIA-24091', 'name' => 'Niacinamide USP', 'qty' => '40.00 kg', 'status' => 'Approved'],
                ['lot' => 'CET-25011', 'name' => 'Cetearyl Alcohol', 'qty' => '60.00 kg', 'status' => 'Approved'],
                ['lot' => 'MCT-24089', 'name' => 'MCT Oil', 'qty' => '80.00 kg', 'status' => 'Approved'],
                ['lot' => 'SHE-25004', 'name' => 'Shea Butter Refined', 'qty' => '50.00 kg', 'status' => 'Approved'],
                ['lot' => 'RET-23992', 'name' => 'Retinol 10% Liposomes', 'qty' => '10.00 kg', 'status' => 'Approved'],
            ]),
            'production_order' => 'MO-2025-0415',
            'bulk_batch' => 'BULK-25-0415',
            'customer_deliveries' => json_encode([
                ['doc' => 'DN-2025-1184', 'customer' => 'Bellezza Retail Milano', 'qty' => '10,000 units', 'status' => 'Delivered'],
                ['doc' => 'DN-2025-1191', 'customer' => 'Milano Cosmesi Hub Roma', 'qty' => '8,400 units', 'status' => 'In transit'],
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
