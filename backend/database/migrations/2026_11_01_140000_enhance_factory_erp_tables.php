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
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('cost_per_unit', 10, 2)->default(0)->after('unit');
            $table->string('cas_number')->nullable()->after('technical_name');
            $table->string('allergens')->nullable()->default('None')->after('cas_number');
        });

        Schema::table('formulations', function (Blueprint $table) {
            $table->string('category')->default('Skincare')->after('product_name');
            $table->string('ph_spec')->default('5.50 - 6.50')->after('author_name');
            $table->string('viscosity_spec')->default('18,000 - 24,000 cP')->after('ph_spec');
            $table->string('density_spec')->default('0.98 - 1.02 g/ml')->after('viscosity_spec');
            $table->json('method_instructions')->nullable()->after('density_spec');
        });

        Schema::table('formulation_lines', function (Blueprint $table) {
            $table->string('target_temp')->nullable()->after('scale_qty');
            $table->string('notes')->nullable()->after('target_temp');
        });

        Schema::table('production_batches', function (Blueprint $table) {
            $table->foreignId('formulation_id')->nullable()->after('id')->constrained('formulations')->nullOnDelete();
            $table->decimal('target_size', 10, 2)->default(1000.00)->after('product_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_batches', function (Blueprint $table) {
            $table->dropForeign(['formulation_id']);
            $table->dropColumn(['formulation_id', 'target_size']);
        });

        Schema::table('formulation_lines', function (Blueprint $table) {
            $table->dropColumn(['target_temp', 'notes']);
        });

        Schema::table('formulations', function (Blueprint $table) {
            $table->dropColumn(['category', 'ph_spec', 'viscosity_spec', 'density_spec', 'method_instructions']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['cost_per_unit', 'cas_number', 'allergens']);
        });
    }
};
