<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSignatureFieldsToIdpsAndAppraisals extends Migration
{
    public function up()
    {
        Schema::table('idps', function (Blueprint $table) {
            if (!Schema::hasColumn('idps', 'employee_signed_by_name')) {
                $table->string('employee_signed_by_name')->nullable()->after('signed_by_employee');
                $table->timestamp('employee_signed_at')->nullable()->after('employee_signed_by_name');
                $table->string('employee_signature_path')->nullable()->after('employee_signed_at');

                $table->string('manager_signed_by_name')->nullable()->after('signed_by_manager');
                $table->timestamp('manager_signed_at')->nullable()->after('manager_signed_by_name');
                $table->string('manager_signature_path')->nullable()->after('manager_signed_at');
            }
        });

        Schema::table('appraisals', function (Blueprint $table) {
            if (!Schema::hasColumn('appraisals', 'employee_signed_by_name')) {
                $table->string('employee_signed_by_name')->nullable()->after('signed_by_employee');
                $table->timestamp('employee_signed_at')->nullable()->after('employee_signed_by_name');
                $table->string('employee_signature_path')->nullable()->after('employee_signed_at');

                $table->string('manager_signed_by_name')->nullable()->after('signed_by_manager');
                $table->timestamp('manager_signed_at')->nullable()->after('manager_signed_by_name');
                $table->string('manager_signature_path')->nullable()->after('manager_signed_at');

                $table->string('supervisor_signed_by_name')->nullable()->after('manager_signature_path');
                $table->timestamp('supervisor_signed_at')->nullable()->after('supervisor_signed_by_name');
                $table->string('supervisor_signature_path')->nullable()->after('supervisor_signed_at');
            }
        });
    }

    public function down()
    {
        Schema::table('idps', function (Blueprint $table) {
            $cols = ['employee_signature_path', 'employee_signed_at', 'employee_signed_by_name', 'manager_signature_path', 'manager_signed_at', 'manager_signed_by_name'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('idps', $c)) {
                    $table->dropColumn($c);
                }
            }
        });

        Schema::table('appraisals', function (Blueprint $table) {
            $cols = ['employee_signature_path', 'employee_signed_at', 'employee_signed_by_name', 'manager_signature_path', 'manager_signed_at', 'manager_signed_by_name', 'supervisor_signature_path', 'supervisor_signed_at', 'supervisor_signed_by_name'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('appraisals', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
}
