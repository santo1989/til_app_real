<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            if (!Schema::hasColumn('appraisals', 'signed_by_hr')) {
                $table->boolean('signed_by_hr')->default(false)->after('signed_by_supervisor');
            }
            if (!Schema::hasColumn('appraisals', 'hr_signed_at')) {
                $table->timestamp('hr_signed_at')->nullable()->after('signed_by_hr');
            }
            if (!Schema::hasColumn('appraisals', 'hr_signed_by_name')) {
                $table->string('hr_signed_by_name')->nullable()->after('hr_signed_at');
            }
            if (!Schema::hasColumn('appraisals', 'hr_signature_path')) {
                $table->string('hr_signature_path')->nullable()->after('hr_signed_by_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            if (Schema::hasColumn('appraisals', 'hr_signature_path')) {
                $table->dropColumn('hr_signature_path');
            }
            if (Schema::hasColumn('appraisals', 'hr_signed_by_name')) {
                $table->dropColumn('hr_signed_by_name');
            }
            if (Schema::hasColumn('appraisals', 'hr_signed_at')) {
                $table->dropColumn('hr_signed_at');
            }
            if (Schema::hasColumn('appraisals', 'signed_by_hr')) {
                $table->dropColumn('signed_by_hr');
            }
        });
    }
};
