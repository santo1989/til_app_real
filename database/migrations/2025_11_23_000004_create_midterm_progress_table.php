<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('midterm_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('objective_id')->nullable();
            $table->string('financial_year')->nullable();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            // Avoid specifying cascade behavior for objective_id to prevent SQL Server
            // "multiple cascade paths" errors. Use the DB default (NO ACTION).
            $table->foreign('objective_id')->references('id')->on('objectives');
            $table->index(['user_id', 'financial_year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('midterm_progress');
    }
};
