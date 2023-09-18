<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTherapyWorkingHours extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapy_working_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapy_id')->constrained('user_therapies', 'id')->cascadeOnDelete();
            $table->tinyInteger('day');
            $table->boolean('rest_day');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
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
        Schema::dropIfExists('therapy_working_hours');
    }
}
