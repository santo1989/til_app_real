<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdpMilestonesTable extends Migration
{
    public function up()
    {
        Schema::create('idp_milestones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('idp_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('resource_required')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('progress', 5, 2)->default(0);
            $table->enum('status', ['open', 'in_progress', 'completed', 'blocked'])->default('open');
            $table->timestamps();

            $table->foreign('idp_id')->references('id')->on('idps')->onDelete('cascade');
            $table->index('idp_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('idp_milestones');
    }
}
