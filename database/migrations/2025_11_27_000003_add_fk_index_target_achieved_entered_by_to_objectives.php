<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('objectives', function (Blueprint $table) {
            // add index if not exists
            $indexName = 'objectives_target_achieved_entered_by_index';
            if (!Schema::hasColumn('objectives', 'target_achieved_entered_by')) {
                // nothing to do if column missing
                return;
            }
            // create index if not exists
            // Note: Blueprint doesn't provide a hasIndex method, wrap in try to avoid exceptions on re-run
            try {
                $table->index('target_achieved_entered_by', $indexName);
            } catch (\Throwable $e) {
                // ignore if index exists
            }
            // add foreign key constraint to users(id)
            try {
                $fkName = 'objectives_target_achieved_entered_by_foreign';
                $table->foreign('target_achieved_entered_by', $fkName)
                    ->references('id')
                    ->on('users')
                    ->onDelete('set null');
            } catch (\Throwable $e) {
                // ignore if foreign key exists or DB doesn't support it
            }
        });
    }

    public function down()
    {
        Schema::table('objectives', function (Blueprint $table) {
            try {
                $table->dropForeign('objectives_target_achieved_entered_by_foreign');
            } catch (\Throwable $e) {
                // ignore
            }
            try {
                $table->dropIndex('objectives_target_achieved_entered_by_index');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};
