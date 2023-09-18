<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserGymsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user__gyms', function (Blueprint $table) {
            $table->foreignIdFor(User::class,'user_id')->primary()->constrained()->cascadeOnDelete();
            $table->enum('gym_type',['normal','exclusive'])->default('normal');

            $table->string('gym_name');
            $table->string('gym_phone');
            $table->string('gym_email');

            $table->string('gym_address');
            $table->string('gym_city');
            $table->string('gym_country');

            $table->json('gym_facilities');

            $table->integer('capacity');

            $table->float('monthly_charge')->default(0);
            $table->float('weekly_charge')->default(0);
            $table->float('daily_charge')->default(0);

            //Deprecated
            $table->double('hourly_rate');



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
        Schema::dropIfExists('user__gyms');
    }
}
