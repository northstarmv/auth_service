<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTherapyQualificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapy__qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapy_id')->constrained('user_therapies', 'id')->cascadeOnDelete();
            $table->string('title', 100);
            $table->string('description', 500);
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
        Schema::dropIfExists('therapy__qualifications');
    }
}
