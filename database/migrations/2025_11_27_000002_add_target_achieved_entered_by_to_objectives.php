<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('objectives', function (Blueprint $table) {
            if (!Schema::hasColumn('objectives', 'target_achieved_entered_by')) {
                $table->unsignedBigInteger('target_achieved_entered_by')->nullable()->after('target_achieved');
            }
            if (!Schema::hasColumn('objectives', 'target_achieved_entered_at')) {
                $table->timestamp('target_achieved_entered_at')->nullable()->after('target_achieved_entered_by');
            }
        });
    }

    public function down()
    {
        Schema::table('objectives', function (Blueprint $table) {
            if (Schema::hasColumn('objectives', 'target_achieved_entered_at')) {
                $table->dropColumn('target_achieved_entered_at');
            }
            if (Schema::hasColumn('objectives', 'target_achieved_entered_by')) {
                $table->dropColumn('target_achieved_entered_by');
            }
        });
    }
};
