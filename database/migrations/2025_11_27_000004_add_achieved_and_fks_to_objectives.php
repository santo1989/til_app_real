<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('objectives', function (Blueprint $table) {
            if (!Schema::hasColumn('objectives', 'midterm_achieved')) {
                $table->decimal('midterm_achieved', 8, 2)->nullable()->after('target');
            }
            if (!Schema::hasColumn('objectives', 'yearend_achieved')) {
                $table->decimal('yearend_achieved', 8, 2)->nullable()->after('midterm_achieved');
            }
            if (Schema::hasColumn('objectives', 'approved_by')) {
                try {
                    $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
                } catch (\Throwable $e) {
                    // ignore if FK exists or DB doesn't support
                }
            }

            // ensure index for financial queries
            try {
                $table->index(['department_id', 'financial_year'], 'objectives_department_financial_year_index');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }

    public function down()
    {
        Schema::table('objectives', function (Blueprint $table) {
            if (Schema::hasColumn('objectives', 'midterm_achieved')) {
                $table->dropColumn('midterm_achieved');
            }
            if (Schema::hasColumn('objectives', 'yearend_achieved')) {
                $table->dropColumn('yearend_achieved');
            }
            try {
                $table->dropForeign(['approved_by']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropIndex('objectives_department_financial_year_index');
            } catch (\Throwable $e) {
            }
        });
    }
};
