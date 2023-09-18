<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiteAdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_ads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('ad_url', 500);
            $table->smallInteger('duration')->unsigned()->length(3);
            $table->integer('order');
            $table->string('ad_img', 255);
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
        Schema::dropIfExists('site_ads');
    }
}
