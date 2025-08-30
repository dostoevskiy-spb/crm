<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_entity', function (Blueprint $table) {
            $table->uuid('uid')->primary();
            $table->string('name_short_name', 20);
            $table->string('name_full_name', 255);
            $table->string('tax_ogrn', 13);
            $table->string('tax_inn', 10)->unique();
            $table->string('tax_kpp', 9);
            $table->text('legal_address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->timestampTz('created_at');
            $table->uuid('creator_uid')->nullable();
            $table->uuid('curator_uid')->nullable();
            
            $table->index(['tax_inn']);
            $table->index(['name_short_name']);
            $table->index(['creator_uid']);
            $table->index(['curator_uid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_entity');
    }
};
