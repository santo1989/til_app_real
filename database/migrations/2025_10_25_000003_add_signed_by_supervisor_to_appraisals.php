<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            if (!Schema::hasColumn('appraisals', 'signed_by_supervisor')) {
                $table->boolean('signed_by_supervisor')->default(false)->after('signed_by_manager');
            }
        });
    }

    public function down()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            if (Schema::hasColumn('appraisals', 'signed_by_supervisor')) {
                $table->dropColumn('signed_by_supervisor');
            }
        });
    }
};
