<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttainmentFieldsToIdpMilestones extends Migration
{
    public function up()
    {
        Schema::table('idp_milestones', function (Blueprint $table) {
            if (!Schema::hasColumn('idp_milestones', 'attainment')) {
                $table->boolean('attainment')->nullable()->after('status');
            }
            if (!Schema::hasColumn('idp_milestones', 'visible_demonstration')) {
                $table->text('visible_demonstration')->nullable()->after('attainment');
            }
            if (!Schema::hasColumn('idp_milestones', 'hr_input')) {
                $table->text('hr_input')->nullable()->after('visible_demonstration');
            }
            if (!Schema::hasColumn('idp_milestones', 'attained_by_id')) {
                $table->unsignedBigInteger('attained_by_id')->nullable()->after('hr_input');
                $table->timestamp('attained_at')->nullable()->after('attained_by_id');
            }
        });
    }

    public function down()
    {
        Schema::table('idp_milestones', function (Blueprint $table) {
            if (Schema::hasColumn('idp_milestones', 'attained_at')) {
                $table->dropColumn('attained_at');
            }
            if (Schema::hasColumn('idp_milestones', 'attained_by_id')) {
                $table->dropColumn('attained_by_id');
            }
            if (Schema::hasColumn('idp_milestones', 'hr_input')) {
                $table->dropColumn('hr_input');
            }
            if (Schema::hasColumn('idp_milestones', 'visible_demonstration')) {
                $table->dropColumn('visible_demonstration');
            }
            if (Schema::hasColumn('idp_milestones', 'attainment')) {
                $table->dropColumn('attainment');
            }
        });
    }
}
