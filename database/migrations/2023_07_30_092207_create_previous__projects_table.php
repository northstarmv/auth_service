<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePreviousProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('previous__projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('desc', 2000);
            $table->string('address', 500);
            $table->string('phone', 20);
            $table->string('image_1', 255);
            $table->string('image_2', 255);
            $table->string('image_3', 255);
            $table->smallInteger('status');
            $table->integer('added_by')->length(11);
            $table->timestamp('added_time')->useCurrent();
            $table->integer('modified_by')->length(11)->nullable();
            $table->timestamp('modified_time')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('previous__projects');
    }
}
