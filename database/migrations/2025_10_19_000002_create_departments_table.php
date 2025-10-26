<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('head_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // foreign key to users will be added in a later migration to avoid circular dependency
        });
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
}
