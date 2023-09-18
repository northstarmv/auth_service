<?php

use App\Models\User;
use App\Models\User_Trainer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user__clients', function (Blueprint $table) {
            $table->foreignIdFor(User::class,'user_id')->primary()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User_Trainer::class,'physical_trainer_id')->nullable();
            $table->foreignIdFor(User_Trainer::class,'diet_trainer_id')->nullable();

            $table->enum('marital_status',['single','married','divorced'])->nullable();
            $table->integer('children')->nullable();

            $table->json('health_conditions')->nullable();
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');

            $table->boolean('is_complete')->default(false);
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
        Schema::dropIfExists('user__clients');
    }
}
