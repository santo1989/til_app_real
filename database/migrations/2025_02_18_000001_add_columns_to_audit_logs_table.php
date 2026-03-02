<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAuditLogsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                if (!Schema::hasColumn('audit_logs', 'table_name')) {
                    $table->string('table_name')->nullable()->after('action');
                }
                if (!Schema::hasColumn('audit_logs', 'record_id')) {
                    $table->unsignedBigInteger('record_id')->nullable()->after('table_name');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'table_name')) {
                $table->dropColumn('table_name');
            }
            if (Schema::hasColumn('audit_logs', 'record_id')) {
                $table->dropColumn('record_id');
            }
        });
    }
}
