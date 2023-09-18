<?php

use App\Models\User_Trainer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePublicTrainerRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('public_trainer_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User_Trainer::class,'trainer_id');
            $table->json('request_data');
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
        Schema::dropIfExists('public_trainer_requests');
    }
}
