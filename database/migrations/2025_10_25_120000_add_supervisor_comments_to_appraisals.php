<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            if (!Schema::hasColumn('appraisals', 'supervisor_comments')) {
                $table->text('supervisor_comments')->nullable()->after('comments');
            }
        });
    }

    public function down()
    {
        Schema::table('appraisals', function (Blueprint $table) {
            if (Schema::hasColumn('appraisals', 'supervisor_comments')) {
                $table->dropColumn('supervisor_comments');
            }
        });
    }
};
