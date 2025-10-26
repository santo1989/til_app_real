<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRatingsAndSignatureImagesToAppraisals extends Migration
{
    public function up()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            if (!Schema::hasColumn('appraisals', 'ratings')) {
                $table->json('ratings')->nullable()->after('comments');
            }
            if (!Schema::hasColumn('appraisals', 'employee_signature_path')) {
                $table->string('employee_signature_path')->nullable()->after('employee_signed_by_name');
            }
            if (!Schema::hasColumn('appraisals', 'manager_signature_path')) {
                $table->string('manager_signature_path')->nullable()->after('manager_signed_by_name');
            }
            if (!Schema::hasColumn('appraisals', 'supervisor_signature_path')) {
                $table->string('supervisor_signature_path')->nullable()->after('supervisor_signed_by_name');
            }
        });
    }

    public function down()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropColumn(['ratings', 'employee_signature_path', 'manager_signature_path', 'supervisor_signature_path']);
        });
    }
}
