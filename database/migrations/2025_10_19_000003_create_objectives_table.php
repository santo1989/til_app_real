<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectivesTable extends Migration
{
    public function up()
    {
        Schema::create('objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->enum('type', ['departmental', 'individual']);
            $table->text('description')->nullable();
            $table->integer('weightage')->nullable(); // store as integer percentage e.g., 10,15,20
            $table->text('target')->nullable();
            $table->boolean('is_smart')->default(false);
            $table->enum('status', ['draft', 'set', 'revised', 'dropped'])->default('draft');
            $table->timestamp('revised_at')->nullable();
            $table->string('financial_year')->default('2025-26');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments');
            $table->index(['user_id', 'financial_year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('objectives');
    }
}
