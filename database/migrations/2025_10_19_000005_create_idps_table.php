<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdpsTable extends Migration
{
    public function up()
    {
        Schema::create('idps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('description');
            $table->text('progress_till_dec')->nullable();
            $table->text('revised_description')->nullable();
            $table->text('accomplishment')->nullable();
            $table->date('review_date')->nullable();
            $table->boolean('signed_by_employee')->default(false);
            $table->boolean('signed_by_manager')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('idps');
    }
}
