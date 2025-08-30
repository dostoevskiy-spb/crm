<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product', function (Blueprint $table) {
            $table->uuid('uid')->primary();

            // Embeddables
            $table->string('name', 50);
            $table->string('unit', 20);

            // Scalars
            $table->string('status', 16);
            $table->string('type', 16);
            $table->string('group_name', 50)->nullable();
            $table->string('subgroup_name', 50)->nullable();
            $table->string('code_1c', 50)->nullable();
            $table->string('sku', 50)->unique();

            // Prices (use decimal for currency, 12,2)
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('avg_purchase_cost_year', 12, 2)->nullable();
            $table->decimal('last_purchase_cost', 12, 2)->nullable();

            // Audit
            $table->timestampTz('created_at');
            $table->uuid('creator_uid')->nullable();
            $table->timestampTz('updated_at')->nullable();
            $table->uuid('updated_by_uid')->nullable();

            // Indexes
            $table->index(['status']);
            $table->index(['type']);
            $table->index(['name']);
            $table->index(['unit']);
            $table->index(['group_name']);
            $table->index(['subgroup_name']);
            $table->index(['code_1c']);
            $table->index(['creator_uid']);
            $table->index(['updated_by_uid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
