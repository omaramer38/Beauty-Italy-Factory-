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
        // 1. Suppliers Master Table
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. SUP-001, SUP-BASF
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->text('materials_supplied')->nullable(); // e.g. "مواد خام كيميائية وزيوت", "علب كرتون وملصقات وعبوات زجاجية"
            $table->string('payment_terms')->default('Net 30 Days');
            $table->string('tax_number')->nullable();
            $table->enum('status', ['Active', 'Under Review', 'Suspended'])->default('Active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Purchase Invoices / Orders Table
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // e.g. INV-2026-00842, PO-2026-019
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->string('supplier_name');
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['Draft', 'Approved', 'Received', 'Paid'])->default('Approved');
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('tax_percent', 5, 2)->default(14.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->string('currency')->default('€');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Purchase Invoice Line Items
        Schema::create('purchase_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('item_code');
            $table->string('item_name');
            $table->string('unit')->default('kg');
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->boolean('received_status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_items');
        Schema::dropIfExists('purchase_invoices');
        Schema::dropIfExists('suppliers');
    }
};
