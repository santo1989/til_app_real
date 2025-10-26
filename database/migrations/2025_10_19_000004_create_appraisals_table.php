<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppraisalsTable extends Migration
{
    public function up()
    {
        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['objective_setting', 'midterm', 'year_end']);
            $table->enum('status', ['draft', 'pending', 'in_progress', 'completed', 'approved'])->default('draft');
            $table->date('date')->nullable();
            $table->decimal('achievement_score', 5, 2)->nullable();
            $table->text('comments')->nullable();
            $table->text('action_points')->nullable();
            $table->decimal('total_score', 6, 2)->nullable();
            $table->enum('rating', ['outstanding', 'good', 'average', 'below'])->nullable();
            $table->boolean('signed_by_employee')->default(false);
            $table->boolean('signed_by_manager')->default(false);
            $table->unsignedBigInteger('conducted_by')->nullable();
            $table->string('financial_year')->default('2025-26');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'type', 'financial_year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('appraisals');
    }
}
