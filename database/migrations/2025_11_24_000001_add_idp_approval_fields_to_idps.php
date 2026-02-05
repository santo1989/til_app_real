<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdpApprovalFieldsToIdps extends Migration
{
    public function up()
    {
        Schema::table('idps', function (Blueprint $table) {
            if (!Schema::hasColumn('idps', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('accomplishment');
            }
            if (!Schema::hasColumn('idps', 'approved_by_id')) {
                $table->unsignedBigInteger('approved_by_id')->nullable()->after('is_approved');
                $table->timestamp('approved_at')->nullable()->after('approved_by_id');
                $table->string('approved_by_role')->nullable()->after('approved_at');
                // do not add foreign key to keep migrations safe across environments
            }
        });
    }

    public function down()
    {
        Schema::table('idps', function (Blueprint $table) {
            if (Schema::hasColumn('idps', 'approved_by_role')) {
                $table->dropColumn('approved_by_role');
            }
            if (Schema::hasColumn('idps', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('idps', 'approved_by_id')) {
                $table->dropColumn('approved_by_id');
            }
            if (Schema::hasColumn('idps', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
        });
    }
}
