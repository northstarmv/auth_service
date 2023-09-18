<?php

use App\Models\User;
use App\Models\User_Gym;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGymGalleriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gym__galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User_Gym::class,'user_id')->constrained()->cascadeOnDelete();
            $table->string('image_path');
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
        Schema::dropIfExists('gym__galleries');
    }
}
