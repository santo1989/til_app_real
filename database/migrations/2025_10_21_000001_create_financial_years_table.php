<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancialYearsTable extends Migration
{
    /**
     * Run the migrations.
     * Creates a canonical `financial_years` table with support for legacy fields kept where useful.
     */
    public function up()
    {
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            // canonical label for UI (e.g. "2025-26")
            $table->string('label')->unique();
            // keep legacy name for backwards compatibility if needed (nullable)
            $table->string('name')->nullable()->unique()->comment('legacy name column - nullable');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('revision_cutoff')->nullable()->comment('optional: 9-month cutoff');
            $table->boolean('is_active')->default(false);
            $table->enum('status', ['upcoming', 'active', 'closed'])->default('upcoming');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('financial_years');
    }
}
