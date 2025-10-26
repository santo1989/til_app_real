<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->timestamp('employee_signed_at')->nullable()->after('signed_by_employee');
            $table->string('employee_signed_by_name')->nullable()->after('employee_signed_at');
            $table->timestamp('manager_signed_at')->nullable()->after('signed_by_manager');
            $table->string('manager_signed_by_name')->nullable()->after('manager_signed_at');
            $table->timestamp('supervisor_signed_at')->nullable()->after('manager_signed_by_name');
            $table->string('supervisor_signed_by_name')->nullable()->after('supervisor_signed_at');
        });
    }

    public function down()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            $table->dropColumn([
                'employee_signed_at',
                'employee_signed_by_name',
                'manager_signed_at',
                'manager_signed_by_name',
                'supervisor_signed_at',
                'supervisor_signed_by_name'
            ]);
        });
    }
};
