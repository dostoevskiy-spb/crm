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
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 20);
            $table->string('last_name', 20);
            $table->string('middle_name', 20);
            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->string('login', 50)->nullable()->unique();
            $table->boolean('is_company_employee')->default(false);
            $table->unsignedBigInteger('creator_id');
            $table->timestamps();

            // Indexes for better performance
            $table->index(['last_name', 'first_name', 'middle_name']);
            $table->index('status_id');
            $table->index('position_id');
            $table->index('creator_id');
            $table->index('is_company_employee');
            $table->index('created_at');

            // Foreign keys will be added when related tables are created
            // $table->foreign('position_id')->references('id')->on('positions')->onDelete('set null');
            // $table->foreign('status_id')->references('id')->on('statuses')->onDelete('restrict');
            // $table->foreign('creator_id')->references('id')->on('persons')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
