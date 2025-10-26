<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('employee_id')->unique();
            $table->string('designation')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->string('tenure_in_current_role')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->text('password_plain')->nullable();
            $table->string('user_image')->nullable();
            $table->enum('role', ['employee', 'line_manager', 'dept_head', 'board', 'hr_admin', 'super_admin'])->default('employee');
            $table->unsignedBigInteger('line_manager_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Avoid department FK here because departments table is created in a later migration.
            // If you need FK, add it in a separate migration after both tables exist.
            // $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('line_manager_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
