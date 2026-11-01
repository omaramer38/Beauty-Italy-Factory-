<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PhaseOneSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Items
        $glyId = DB::table('items')->insertGetId([
            'code' => 'RM-00142',
            'name' => 'Vegetable Glycerin 99.5%',
            'type' => 'raw_material',
            'inci_name' => 'Glycerin',
            'technical_name' => 'Vegetable Glycerin 99.5%',
            'function' => 'Humectant',
            'unit' => 'kg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $retId = DB::table('items')->insertGetId([
            'code' => 'RM-00028',
            'name' => 'Retinol 10% Encapsulated',
            'type' => 'raw_material',
            'inci_name' => 'Retinol',
            'technical_name' => 'Encapsulated Retinol 10%',
            'function' => 'Active',
            'unit' => 'kg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $haId = DB::table('items')->insertGetId([
            'code' => 'RM-00193',
            'name' => 'Hyaluronic Acid LMW',
            'type' => 'raw_material',
            'inci_name' => 'Sodium Hyaluronate',
            'technical_name' => 'Low Molecular Weight Hyaluronic Acid',
            'function' => 'Active',
            'unit' => 'kg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $botId = DB::table('items')->insertGetId([
            'code' => 'PK-00414',
            'name' => 'Glass Dropper Bottle 30ml',
            'type' => 'packaging',
            'inci_name' => null,
            'technical_name' => 'Flacon verre 30ml ambré',
            'function' => 'Primary Packaging',
            'unit' => 'pcs',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $rfId = DB::table('items')->insertGetId([
            'code' => 'RM-00201',
            'name' => 'Natural Rose Fragrance',
            'type' => 'raw_material',
            'inci_name' => 'Parfum',
            'technical_name' => 'Signature Bloom 114',
            'function' => 'Fragrance',
            'unit' => 'kg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $niaId = DB::table('items')->insertGetId([
            'code' => 'RM-00056',
            'name' => 'Niacinamide USP',
            'type' => 'raw_material',
            'inci_name' => 'Niacinamide',
            'technical_name' => 'Vitamin B3 USP Grade',
            'function' => 'Active',
            'unit' => 'kg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $fgId = DB::table('items')->insertGetId([
            'code' => 'FG-250415',
            'name' => 'Anti-Aging Renewal Cream 50ml',
            'type' => 'finished_good',
            'inci_name' => null,
            'technical_name' => 'Anti-Aging Renewal Cream',
            'function' => 'Finished Cosmetic Product',
            'unit' => 'pcs',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Lots
        $lot1Id = DB::table('lots')->insertGetId([
            'lot_number' => 'GLY-8821-IT',
            'item_id' => $glyId,
            'status' => 'Approved',
            'quantity' => 1240.00,
            'unit' => 'kg',
            'storage_location' => 'WH-A · A03 · S12',
            'expiry_date' => '2025-05-02',
            'supplier_name' => 'Cosmetica Materie S.p.A.',
            'source_document' => 'GR-2025-00842',
            'created_at' => now()->subDays(10),
            'updated_at' => now(),
        ]);

        DB::table('lots')->insert([
            [
                'lot_number' => 'RET-24018',
                'item_id' => $retId,
                'status' => 'Quarantine',
                'quantity' => 18.50,
                'unit' => 'kg',
                'storage_location' => 'WH-Q · Q01 · S04',
                'expiry_date' => '2025-06-18',
                'supplier_name' => 'Retinoid Labs Europe',
                'source_document' => 'GR-2025-00850',
                'created_at' => now()->subDays(2),
                'updated_at' => now(),
            ],
            [
                'lot_number' => 'HA-25-0031',
                'item_id' => $haId,
                'status' => 'Approved',
                'quantity' => 42.75,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A01 · S08',
                'expiry_date' => '2026-03-04',
                'supplier_name' => 'BioActive Swiss',
                'source_document' => 'GR-2025-00790',
                'created_at' => now()->subDays(25),
                'updated_at' => now(),
            ],
            [
                'lot_number' => 'BOT-250322',
                'item_id' => $botId,
                'status' => 'Approved',
                'quantity' => 24600,
                'unit' => 'pcs',
                'storage_location' => 'WH-P · P07 · S02',
                'expiry_date' => null,
                'supplier_name' => 'Vetro Vetri Italia',
                'source_document' => 'GR-2025-00812',
                'created_at' => now()->subDays(15),
                'updated_at' => now(),
            ],
            [
                'lot_number' => 'RF-11884',
                'item_id' => $rfId,
                'status' => 'Rejected',
                'quantity' => 8.25,
                'unit' => 'kg',
                'storage_location' => 'WH-R · R02 · S10',
                'expiry_date' => '2025-04-28',
                'supplier_name' => 'Fragranze Milano',
                'source_document' => 'GR-2025-00831',
                'created_at' => now()->subDays(8),
                'updated_at' => now(),
            ],
            [
                'lot_number' => 'NIA-24091',
                'item_id' => $niaId,
                'status' => 'Approved',
                'quantity' => 320.00,
                'unit' => 'kg',
                'storage_location' => 'WH-A · A02 · S03',
                'expiry_date' => '2026-12-22',
                'supplier_name' => 'PureChem Global',
                'source_document' => 'GR-2025-00780',
                'created_at' => now()->subDays(30),
                'updated_at' => now(),
            ],
        ]);

        // 3. Immutable Ledger Entries for GLY-8821-IT
        DB::table('inventory_ledger')->insert([
            [
                'lot_id' => $lot1Id,
                'transaction_type' => 'lot_created',
                'quantity_change' => 0,
                'running_balance' => 0,
                'source_document' => 'PO-2025-0189',
                'user_name' => 'System',
                'notes' => 'Lot initialized from Purchase Order',
                'created_at' => now()->subDays(2)->setHour(8)->setMinute(29),
            ],
            [
                'lot_id' => $lot1Id,
                'transaction_type' => 'goods_receipt',
                'quantity_change' => 1250.00,
                'running_balance' => 1250.00,
                'source_document' => 'GR-2025-00842',
                'user_name' => 'Marco D.',
                'notes' => 'Received into Quarantine',
                'created_at' => now()->subDays(2)->setHour(8)->setMinute(31),
            ],
            [
                'lot_id' => $lot1Id,
                'transaction_type' => 'qc_sampling',
                'quantity_change' => -0.50,
                'running_balance' => 1249.50,
                'source_document' => 'QC-S-004182',
                'user_name' => 'Luca B.',
                'notes' => 'QC sample pulled for assay testing',
                'created_at' => now()->subDays(2)->setHour(14)->setMinute(18),
            ],
            [
                'lot_id' => $lot1Id,
                'transaction_type' => 'qc_release',
                'quantity_change' => 0,
                'running_balance' => 1240.00,
                'source_document' => 'QC-PASS-004182',
                'user_name' => 'Elena R.',
                'notes' => 'Status changed from Quarantine to Approved',
                'created_at' => now()->setHour(9)->setMinute(42),
            ],
        ]);

        // 4. Formulation
        $formId = DB::table('formulations')->insertGetId([
            'code' => 'FRM-SC-0042',
            'product_name' => 'Anti-Aging Renewal Cream',
            'version' => 'v2.4',
            'status' => 'Approved',
            'target_batch_size' => 1000.00,
            'author_name' => 'Dr. Sofia Bianchi',
            'effective_date' => '2025-04-01',
            'created_at' => now()->subDays(15),
            'updated_at' => now(),
        ]);

        // Formulation Lines
        $ingredients = [
            ['phase' => 'A', 'name' => 'Aqua', 'tech' => 'Purified Water EP', 'func' => 'Solvent', 'pct' => 67.950, 'qty' => 679.50, 'item_id' => $glyId],
            ['phase' => 'A', 'name' => 'Glycerin', 'tech' => 'Vegetable Glycerin 99.5%', 'func' => 'Humectant', 'pct' => 5.000, 'qty' => 50.00, 'item_id' => $glyId],
            ['phase' => 'A', 'name' => 'Niacinamide', 'tech' => 'Niacinamide USP', 'func' => 'Active', 'pct' => 4.000, 'qty' => 40.00, 'item_id' => $niaId],
            ['phase' => 'B', 'name' => 'Cetearyl Alcohol', 'tech' => 'Lanette O', 'func' => 'Emulsifier', 'pct' => 6.000, 'qty' => 60.00, 'item_id' => $glyId],
            ['phase' => 'B', 'name' => 'Caprylic/Capric Triglyceride', 'tech' => 'MCT Oil', 'func' => 'Emollient', 'pct' => 8.000, 'qty' => 80.00, 'item_id' => $glyId],
            ['phase' => 'B', 'name' => 'Butyrospermum Parkii Butter', 'tech' => 'Shea Butter Refined', 'func' => 'Emollient', 'pct' => 5.000, 'qty' => 50.00, 'item_id' => $glyId],
            ['phase' => 'C', 'name' => 'Retinol', 'tech' => 'Encapsulated Retinol 10%', 'func' => 'Active', 'pct' => 1.000, 'qty' => 10.00, 'item_id' => $retId],
            ['phase' => 'C', 'name' => 'Phenoxyethanol, Ethylhexylglycerin', 'tech' => 'Euxyl PE 9010', 'func' => 'Preservative', 'pct' => 1.000, 'qty' => 10.00, 'item_id' => $glyId],
            ['phase' => 'C', 'name' => 'Parfum', 'tech' => 'Signature Bloom 114', 'func' => 'Fragrance', 'pct' => 2.050, 'qty' => 20.50, 'item_id' => $rfId],
        ];

        foreach ($ingredients as $ing) {
            DB::table('formulation_lines')->insert([
                'formulation_id' => $formId,
                'phase' => $ing['phase'],
                'item_id' => $ing['item_id'],
                'percentage' => $ing['pct'],
                'function' => $ing['func'],
                'scale_qty' => $ing['qty'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Formulation Approvals
        DB::table('formulation_approvals')->insert([
            ['formulation_id' => $formId, 'role_title' => 'Formulator', 'approver_name' => 'Dr. Sofia Bianchi', 'initials' => 'SB', 'signed_at' => now()->subDays(5)->setHour(16)->setMinute(42)],
            ['formulation_id' => $formId, 'role_title' => 'Lab Supervisor', 'approver_name' => 'Luca Bellini', 'initials' => 'LB', 'signed_at' => now()->subDays(4)->setHour(11)->setMinute(8)],
            ['formulation_id' => $formId, 'role_title' => 'QA Head', 'approver_name' => 'Elena Rossi', 'initials' => 'ER', 'signed_at' => now()->subDays(3)->setHour(9)->setMinute(14)],
        ]);

        // 5. Production Batches
        $batchId = DB::table('production_batches')->insertGetId([
            'batch_number' => 'B-25-0415',
            'product_name' => 'Anti-Aging Renewal Cream 50ml',
            'line_code' => 'L03',
            'start_time' => '08:15',
            'end_time' => '17:00',
            'progress' => 31,
            'status' => 'QC Hold',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('production_batches')->insert([
            ['batch_number' => 'B-25-0412', 'product_name' => 'HydraGlow Serum 30ml', 'line_code' => 'L01', 'start_time' => '06:30', 'end_time' => '14:00', 'progress' => 74, 'status' => 'Running', 'created_at' => now(), 'updated_at' => now()],
            ['batch_number' => 'B-25-0414', 'product_name' => 'Velvet Matte Lipstick 04', 'line_code' => 'L02', 'start_time' => '07:00', 'end_time' => '15:30', 'progress' => 54, 'status' => 'Running', 'created_at' => now(), 'updated_at' => now()],
            ['batch_number' => 'B-25-0416', 'product_name' => 'Micellar Cleanser 200ml', 'line_code' => 'L04', 'start_time' => '10:30', 'end_time' => '18:00', 'progress' => 12, 'status' => 'Changeover', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 6. Dispensing steps for Batch B-25-0415
        DB::table('batch_dispense_steps')->insert([
            ['batch_id' => $batchId, 'step_number' => 1, 'phase' => 'A', 'material_name' => 'Aqua (Purified Water)', 'item_code' => 'RM-00001', 'scanned_lot_number' => 'PW-250415', 'target_weight' => 679.50, 'actual_weight' => 679.50, 'tolerance_percent' => 0.50, 'scale_id' => 'WS-02', 'operator_name' => 'Giulia Romano', 'is_signed' => true, 'signed_at' => now()->subHour(2), 'created_at' => now(), 'updated_at' => now()],
            ['batch_id' => $batchId, 'step_number' => 2, 'phase' => 'A', 'material_name' => 'Vegetable Glycerin 99.5%', 'item_code' => 'RM-00142', 'scanned_lot_number' => 'GLY-8821-IT', 'target_weight' => 50.00, 'actual_weight' => 50.042, 'tolerance_percent' => 0.50, 'scale_id' => 'WS-02', 'operator_name' => 'Giulia Romano', 'is_signed' => true, 'signed_at' => now()->subMinutes(15), 'created_at' => now(), 'updated_at' => now()],
            ['batch_id' => $batchId, 'step_number' => 3, 'phase' => 'A', 'material_name' => 'Niacinamide USP', 'item_code' => 'RM-00056', 'scanned_lot_number' => null, 'target_weight' => 40.00, 'actual_weight' => null, 'tolerance_percent' => 0.50, 'scale_id' => 'WS-02', 'operator_name' => null, 'is_signed' => false, 'signed_at' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 7. QC Samples & Test Results
        $sampleId = DB::table('qc_samples')->insertGetId([
            'sample_number' => 'QC-S-004182',
            'batch_number' => 'B-25-0398',
            'product_name' => 'HydraGlow Serum 30ml',
            'status' => 'Pending QA release',
            'analyst_name' => 'Luca Bellini',
            'coa_filename' => 'COA-QC-004182.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('qc_test_results')->insert([
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Appearance', 'min_spec' => 'Homogeneous cream', 'max_spec' => null, 'actual_result' => 'Homogeneous cream', 'status' => 'Pass', 'created_at' => now(), 'updated_at' => now()],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Color', 'min_spec' => 'White to ivory', 'max_spec' => null, 'actual_result' => 'Ivory', 'status' => 'Pass', 'created_at' => now(), 'updated_at' => now()],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Odor', 'min_spec' => 'Characteristic', 'max_spec' => null, 'actual_result' => 'Characteristic', 'status' => 'Pass', 'created_at' => now(), 'updated_at' => now()],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'pH @ 25°C', 'min_spec' => '5.50', 'max_spec' => '6.50', 'actual_result' => '5.86', 'status' => 'Pass', 'created_at' => now(), 'updated_at' => now()],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Viscosity @ 25°C', 'min_spec' => '18,000 cP', 'max_spec' => '24,000 cP', 'actual_result' => '21,450 cP', 'status' => 'Pass', 'created_at' => now(), 'updated_at' => now()],
            ['qc_sample_id' => $sampleId, 'parameter_name' => 'Microbiology', 'min_spec' => 'Within ISO 17516 limits', 'max_spec' => null, 'actual_result' => 'Conforms', 'status' => 'Pass', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 8. Traceability Records
        DB::table('traceability_records')->insert([
            'finished_lot' => 'FG-250415-A',
            'supplier_po' => 'PO-2025-0189',
            'supplier_name' => 'Cosmetica Materie S.p.A.',
            'raw_materials' => json_encode([
                ['lot' => 'GLY-8821-IT', 'name' => 'Vegetable Glycerin', 'qty' => '1,250 kg'],
                ['lot' => 'NIA-24091', 'name' => 'Niacinamide USP', 'qty' => '320 kg'],
            ]),
            'production_order' => 'MO-2025-0415',
            'bulk_batch' => 'BULK-25-0415',
            'customer_deliveries' => json_encode([
                ['doc' => 'DN-2025-1184', 'customer' => 'Bellezza Retail', 'qty' => '8,000 units', 'status' => 'Delivered'],
                ['doc' => 'DN-2025-1191', 'customer' => 'Milano Cosmesi', 'qty' => '6,400 units', 'status' => 'In transit'],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
