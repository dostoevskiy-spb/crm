<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->uuid('uid')->primary();

            // Embeddables
            $table->string('name', 100);

            // Scalars
            $table->string('status', 20);
            $table->string('prev_status', 20)->nullable();

            // Relations by UID
            $table->uuid('transport_uid')->nullable();
            $table->string('warehouse', 100)->nullable();
            $table->uuid('issued_to_uid')->nullable();

            // Documents and related UIDs
            $table->uuid('purchase_invoice_uid')->nullable();
            $table->uuid('supplier_uid')->nullable();
            $table->uuid('issue_doc_uid')->nullable();
            $table->date('mounting_date')->nullable();
            $table->uuid('shipment_invoice_uid')->nullable();
            $table->uuid('customer_uid')->nullable();

            // SKZI dates
            $table->date('skzi_from')->nullable();
            $table->date('skzi_to')->nullable();

            // Audit
            $table->timestampTz('created_at');
            $table->uuid('creator_uid')->nullable();
            $table->timestampTz('updated_at')->nullable();
            $table->uuid('updated_by_uid')->nullable();

            // Indexes
            $table->index(['status']);
            $table->index(['name']);
            $table->index(['transport_uid']);
            $table->index(['warehouse']);
            $table->index(['issued_to_uid']);
            $table->index(['purchase_invoice_uid']);
            $table->index(['supplier_uid']);
            $table->index(['issue_doc_uid']);
            $table->index(['shipment_invoice_uid']);
            $table->index(['customer_uid']);
            $table->index(['creator_uid']);
            $table->index(['updated_by_uid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
