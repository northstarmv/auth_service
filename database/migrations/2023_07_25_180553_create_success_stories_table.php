<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuccessStoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('success_stories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->smallInteger('age')->unsigned()->length(3);
            $table->string('desc', 2000);
            $table->string('point_1', 100);
            $table->string('point_2', 100);
            $table->string('point_3', 100);
            $table->string('point_4', 100);
            $table->string('before_img', 255);
            $table->string('after_img', 255);
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
        Schema::dropIfExists('success_stories');
    }
}
