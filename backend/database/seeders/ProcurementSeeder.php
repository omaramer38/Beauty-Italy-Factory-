<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcurementSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // 1. Seed Suppliers
        $suppliers = [
            [
                'code' => 'SUP-BASF',
                'name' => 'BASF Personal Care Europe',
                'contact_person' => 'Marco Rossi',
                'phone' => '+39 02 4810 4110',
                'email' => 'orders.italy@basf.com',
                'address' => 'Via Marconato 8, 20811 Cesano Maderno (MB), Italy',
                'materials_supplied' => 'مواد خام كيميائية، مستحلبات (Lanette O)، كحول سيتريل، خافضات توتر سطحي',
                'payment_terms' => 'Net 30 Days',
                'tax_number' => 'IT00481230960',
                'status' => 'Active',
                'notes' => 'GMP Qualified Raw Material Supplier. Audited Q1 2026.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SUP-LONZA',
                'name' => 'Lonza Special Ingredients',
                'contact_person' => 'Helena Schmidt',
                'phone' => '+41 61 316 8111',
                'email' => 'cosmetics.sales@lonza.com',
                'address' => 'Muenchensteinerstrasse 38, CH-4002 Basel, Switzerland',
                'materials_supplied' => 'فيتامين B3، نياسيناميد USP، مضادات أكسدة نشطة، مواد حافظة',
                'payment_terms' => 'Net 45 Days',
                'tax_number' => 'CHE-105.823.114',
                'status' => 'Active',
                'notes' => 'High purity pharmaceutical grade vitamin supplier.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SUP-VETRO',
                'name' => 'Vetro Packaging Italia S.r.l.',
                'contact_person' => 'Alessandro Galli',
                'phone' => '+39 02 8920 1144',
                'email' => 'ordini@vetropackaging.it',
                'address' => 'Via dell\'Industria 14, 20090 Segrate (MI), Italy',
                'materials_supplied' => 'زجاجات قطارة 30 مل و 50 مل عسلي وشفاف، بيبتات سيليكون، أغطية ألومنيوم',
                'payment_terms' => 'Net 30 Days',
                'tax_number' => 'IT08412980155',
                'status' => 'Active',
                'notes' => 'Primary packaging glass certified under ISO 15378.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SUP-BORMIOLI',
                'name' => 'Bormioli Luigi Glassworks',
                'contact_person' => 'Chiara Morandi',
                'phone' => '+39 0521 7931',
                'email' => 'sales.cosmetics@bormioliluigi.it',
                'address' => 'Viale Martiri della Libertà 1, 43123 Parma, Italy',
                'materials_supplied' => 'برطمانات زجاجية مطفية 50 مل، أغطية فضية فاخرة، عبوات مستحضرات تجميل',
                'payment_terms' => 'Net 60 Days',
                'tax_number' => 'IT00154380344',
                'status' => 'Active',
                'notes' => 'Heavy base cosmetic glass specialist.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SUP-CARTOTECA',
                'name' => 'Cartotecnica Milano S.r.l.',
                'contact_person' => 'Giuseppe Verdi',
                'phone' => '+39 02 3340 7780',
                'email' => 'preventivi@cartotecnicamilano.it',
                'address' => 'Via Gallarate 210, 20151 Milano, Italy',
                'materials_supplied' => 'علب كرتون خارجية 30مل و 50مل، كراتين شحن مضلعة، نشرات داخلية وسلوفان',
                'payment_terms' => 'Net 30 Days',
                'tax_number' => 'IT09938470158',
                'status' => 'Active',
                'notes' => 'FSC Certified 350gsm virgin bleached board folding boxes with silver foil hot-stamping.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SUP-LBLPRINT',
                'name' => 'LabelPrint Italia S.p.A.',
                'contact_person' => 'Davide Colombo',
                'phone' => '+39 039 689 4411',
                'email' => 'produzione@labelprintitalia.it',
                'address' => 'Via Monza 45, 20863 Concorezzo (MB), Italy',
                'materials_supplied' => 'ملصقات وستيكرات فينيل مقاومة للماء والزيوت، رولات ستيكرات لماكينات التعبئة',
                'payment_terms' => 'Net 30 Days',
                'tax_number' => 'IT07128930961',
                'status' => 'Active',
                'notes' => 'Roll labels compatible with automatic labeling line L02 & L04.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SUP-GIVAUDAN',
                'name' => 'Givaudan Fragrances S.A.',
                'contact_person' => 'Pierre Laurent',
                'phone' => '+41 22 780 9111',
                'email' => 'fragrance.orders@givaudan.com',
                'address' => '5 Chemin de la Parfumerie, 1214 Vernier, Switzerland',
                'materials_supplied' => 'عطور طبيعية، خلاصات زهور، نكهات ومستخلصات عطرية لمستحضرات التجميل',
                'payment_terms' => 'Net 30 Days',
                'tax_number' => 'CHE-101.442.923',
                'status' => 'Active',
                'notes' => 'IFRA compliant fragrances.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $supIds = [];
        foreach ($suppliers as $sup) {
            $existing = DB::table('suppliers')->where('code', $sup['code'])->first();
            if ($existing) {
                $supIds[$sup['code']] = $existing->id;
            } else {
                $supIds[$sup['code']] = DB::table('suppliers')->insertGetId($sup);
            }
        }

        // 2. Fetch some existing items to link to invoices
        $itemBot = DB::table('items')->where('code', 'PK-00414')->first();
        $itemJar = DB::table('items')->where('code', 'PK-00520')->first();
        $itemNia = DB::table('items')->where('code', 'RM-00056')->first();
        $itemCet = DB::table('items')->where('code', 'RM-00104')->first();

        // Ensure we also have a Box and a Label item in items table if missing
        $boxItem = DB::table('items')->where('code', 'PK-BOX-050')->first();
        if (!$boxItem) {
            $boxId = DB::table('items')->insertGetId([
                'code' => 'PK-BOX-050',
                'name' => 'Folding Outer Carton Box 50ml UV Matte',
                'type' => 'packaging',
                'unit' => 'pcs',
                'cost_per_unit' => 0.28,
                'function' => 'Secondary Outer Box',
                'technical_name' => '350gsm Invercote G + Soft Touch + Silver Foil',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $boxItem = DB::table('items')->where('id', $boxId)->first();
        }

        $lblItem = DB::table('items')->where('code', 'PK-LBL-030')->first();
        if (!$lblItem) {
            $lblId = DB::table('items')->insertGetId([
                'code' => 'PK-LBL-030',
                'name' => 'Waterproof Vinyl Front Label 30ml Dropper',
                'type' => 'packaging',
                'unit' => 'pcs',
                'cost_per_unit' => 0.12,
                'function' => 'Primary Container Label',
                'technical_name' => 'Synthetic PP White 60u + Gloss Varnish Roll',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $lblItem = DB::table('items')->where('id', $lblId)->first();
        }

        // 3. Seed Purchase Invoices with lines
        if (DB::table('purchase_invoices')->count() === 0) {
            // Invoice 1: Glass bottles from Vetro Packaging
            $inv1Id = DB::table('purchase_invoices')->insertGetId([
                'invoice_number' => 'INV-2026-00842',
                'supplier_id' => $supIds['SUP-VETRO'],
                'supplier_name' => 'Vetro Packaging Italia S.r.l.',
                'invoice_date' => '2026-10-15',
                'due_date' => '2026-11-15',
                'status' => 'Received',
                'subtotal' => 2160.00,
                'tax_percent' => 14.00,
                'tax_amount' => 302.40,
                'total_amount' => 2462.40,
                'currency' => '€',
                'notes' => 'Delivered under GR-2025-00780. All 4,800 amber dropper bottles inspected and released to packaging bay.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('purchase_invoice_items')->insert([
                'purchase_invoice_id' => $inv1Id,
                'item_id' => $itemBot ? $itemBot->id : null,
                'item_code' => $itemBot ? $itemBot->code : 'PK-00414',
                'item_name' => $itemBot ? $itemBot->name : 'Glass Dropper Bottle 30ml Amber',
                'unit' => 'pcs',
                'quantity' => 4800,
                'unit_price' => 0.45,
                'total_price' => 2160.00,
                'received_status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Invoice 2: Raw material Cetearyl Alcohol from BASF
            $inv2Id = DB::table('purchase_invoices')->insertGetId([
                'invoice_number' => 'INV-2026-00910',
                'supplier_id' => $supIds['SUP-BASF'],
                'supplier_name' => 'BASF Personal Care Europe',
                'invoice_date' => '2026-10-20',
                'due_date' => '2026-11-20',
                'status' => 'Received',
                'subtotal' => 3230.00,
                'tax_percent' => 14.00,
                'tax_amount' => 452.20,
                'total_amount' => 3682.20,
                'currency' => '€',
                'notes' => 'Lot CET-25011. High purity fatty alcohol EP received in Zone WH-A.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('purchase_invoice_items')->insert([
                'purchase_invoice_id' => $inv2Id,
                'item_id' => $itemCet ? $itemCet->id : null,
                'item_code' => $itemCet ? $itemCet->code : 'RM-00104',
                'item_name' => $itemCet ? $itemCet->name : 'Cetearyl Alcohol (Lanette O)',
                'unit' => 'kg',
                'quantity' => 850,
                'unit_price' => 3.80,
                'total_price' => 3230.00,
                'received_status' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Invoice 3: Folding boxes & Roll labels
            $inv3Id = DB::table('purchase_invoices')->insertGetId([
                'invoice_number' => 'PO-2026-00412',
                'supplier_id' => $supIds['SUP-CARTOTECA'],
                'supplier_name' => 'Cartotecnica Milano S.r.l.',
                'invoice_date' => '2026-11-01',
                'due_date' => '2026-12-01',
                'status' => 'Approved',
                'subtotal' => 2800.00,
                'tax_percent' => 14.00,
                'tax_amount' => 392.00,
                'total_amount' => 3192.00,
                'currency' => '€',
                'notes' => 'Official purchase order for HydraGlow & Anti-Aging outer secondary boxes.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('purchase_invoice_items')->insert([
                'purchase_invoice_id' => $inv3Id,
                'item_id' => $boxItem->id,
                'item_code' => $boxItem->code,
                'item_name' => $boxItem->name,
                'unit' => 'pcs',
                'quantity' => 10000,
                'unit_price' => 0.28,
                'total_price' => 2800.00,
                'received_status' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Invoice 4: Niacinamide active from Lonza
            $inv4Id = DB::table('purchase_invoices')->insertGetId([
                'invoice_number' => 'PO-2026-00711',
                'supplier_id' => $supIds['SUP-LONZA'],
                'supplier_name' => 'Lonza Special Ingredients',
                'invoice_date' => '2026-11-03',
                'due_date' => '2026-12-18',
                'status' => 'Approved',
                'subtotal' => 4640.00,
                'tax_percent' => 14.00,
                'tax_amount' => 649.60,
                'total_amount' => 5289.60,
                'currency' => '€',
                'notes' => 'Vitamin B3 USP Grade 99.9% purity for high-efficacy whitening formulas.',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('purchase_invoice_items')->insert([
                'purchase_invoice_id' => $inv4Id,
                'item_id' => $itemNia ? $itemNia->id : null,
                'item_code' => $itemNia ? $itemNia->code : 'RM-00056',
                'item_name' => $itemNia ? $itemNia->name : 'Niacinamide USP (Vitamin B3)',
                'unit' => 'kg',
                'quantity' => 320,
                'unit_price' => 14.50,
                'total_price' => 4640.00,
                'received_status' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
