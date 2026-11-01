<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Items Master Data
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. RM-00142, PK-00414, FG-250415
            $table->string('name');
            $table->enum('type', ['raw_material', 'packaging', 'bulk_semi_finished', 'finished_good']);
            $table->string('inci_name')->nullable();
            $table->string('technical_name')->nullable();
            $table->string('function')->nullable(); // e.g. Humectant, Active, Emulsifier
            $table->string('unit')->default('kg');
            $table->timestamps();
        });

        // 2. Inventory Lots
        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->string('lot_number')->unique(); // e.g. GLY-8821-IT
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->enum('status', ['Quarantine', 'Approved', 'Rejected'])->default('Quarantine');
            $table->decimal('quantity', 12, 3)->default(0);
            $table->string('unit')->default('kg');
            $table->string('storage_location'); // e.g. WH-A · A03 · S12
            $table->date('expiry_date')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('source_document')->nullable(); // e.g. GR-2025-00842
            $table->timestamps();
        });

        // 3. Append-only Inventory Movement Ledger (GMP Compliance)
        Schema::create('inventory_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lot_id')->constrained('lots')->cascadeOnDelete();
            $table->string('transaction_type'); // receipt, qc_release, qc_reject, dispensing_consume, finished_output
            $table->decimal('quantity_change', 12, 3);
            $table->decimal('running_balance', 12, 3);
            $table->string('source_document');
            $table->string('user_name');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 4. Formulations
        Schema::create('formulations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. FRM-SC-0042
            $table->string('product_name');
            $table->string('version')->default('v1.0');
            $table->enum('status', ['Draft', 'Under Review', 'Approved', 'Obsolete'])->default('Approved');
            $table->decimal('target_batch_size', 10, 2)->default(1000.00);
            $table->string('author_name');
            $table->date('effective_date')->nullable();
            $table->timestamps();
        });

        // 5. Formulation Lines
        Schema::create('formulation_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulation_id')->constrained('formulations')->cascadeOnDelete();
            $table->string('phase', 2); // A, B, C
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('percentage', 8, 4); // % w/w
            $table->string('function');
            $table->decimal('scale_qty', 10, 3);
            $table->timestamps();
        });

        // 6. Formulation Approvals
        Schema::create('formulation_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formulation_id')->constrained('formulations')->cascadeOnDelete();
            $table->string('role_title'); // Formulator, Lab Supervisor, QA Head
            $table->string('approver_name');
            $table->string('initials', 4);
            $table->timestamp('signed_at');
        });

        // 7. Production Batches (MES)
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique(); // e.g. B-25-0415
            $table->string('product_name');
            $table->string('line_code'); // L01, L02, L03, L04
            $table->string('start_time')->default('08:00');
            $table->string('end_time')->default('17:00');
            $table->integer('progress')->default(0);
            $table->enum('status', ['Scheduled', 'Running', 'QC Hold', 'Changeover', 'Completed', 'Released'])->default('Running');
            $table->timestamps();
        });

        // 8. Batch Dispensing Steps (Shopfloor Tablet Weighing)
        Schema::create('batch_dispense_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('production_batches')->cascadeOnDelete();
            $table->integer('step_number');
            $table->string('phase', 2);
            $table->string('material_name');
            $table->string('item_code');
            $table->string('scanned_lot_number')->nullable();
            $table->decimal('target_weight', 10, 3);
            $table->decimal('actual_weight', 10, 3)->nullable();
            $table->decimal('tolerance_percent', 5, 2)->default(0.50);
            $table->string('scale_id')->default('WS-02');
            $table->string('operator_name')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });

        // 9. QC Samples & Test Results (LIMS)
        Schema::create('qc_samples', function (Blueprint $table) {
            $table->id();
            $table->string('sample_number')->unique(); // QC-S-004182
            $table->string('batch_number');
            $table->string('product_name');
            $table->enum('status', ['Pending QA release', 'Approved', 'Rejected'])->default('Pending QA release');
            $table->string('analyst_name')->default('Luca Bellini');
            $table->string('coa_filename')->nullable();
            $table->timestamps();
        });

        Schema::create('qc_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qc_sample_id')->constrained('qc_samples')->cascadeOnDelete();
            $table->string('parameter_name');
            $table->string('min_spec')->nullable();
            $table->string('max_spec')->nullable();
            $table->string('actual_result');
            $table->enum('status', ['Pass', 'Fail'])->default('Pass');
            $table->timestamps();
        });

        // 10. QA Releases (21 CFR Part 11 Electronic Signature)
        Schema::create('qa_releases', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number');
            $table->enum('disposition', ['Released', 'Rejected', 'Conditional Hold'])->default('Released');
            $table->string('qa_manager_name');
            $table->string('approval_reason');
            $table->text('signature_statement');
            $table->timestamp('released_at')->useCurrent();
        });

        // 11. Traceability Genealogy
        Schema::create('traceability_records', function (Blueprint $table) {
            $table->id();
            $table->string('finished_lot');
            $table->string('supplier_po');
            $table->string('supplier_name');
            $table->json('raw_materials');
            $table->string('production_order');
            $table->string('bulk_batch');
            $table->json('customer_deliveries');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traceability_records');
        Schema::dropIfExists('qa_releases');
        Schema::dropIfExists('qc_test_results');
        Schema::dropIfExists('qc_samples');
        Schema::dropIfExists('batch_dispense_steps');
        Schema::dropIfExists('production_batches');
        Schema::dropIfExists('formulation_approvals');
        Schema::dropIfExists('formulation_lines');
        Schema::dropIfExists('formulations');
        Schema::dropIfExists('inventory_ledger');
        Schema::dropIfExists('lots');
        Schema::dropIfExists('items');
    }
};
