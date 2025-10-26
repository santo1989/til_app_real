<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScoresToObjectivesTable extends Migration
{
    public function up()
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->decimal('target_achieved', 8, 2)->nullable()->after('created_by');
            $table->decimal('final_score', 8, 2)->nullable()->after('target_achieved');
        });
    }

    public function down()
    {
        Schema::table('objectives', function (Blueprint $table) {
            $table->dropColumn(['target_achieved', 'final_score']);
        });
    }
}
