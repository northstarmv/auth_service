<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDoctorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user__doctors', function (Blueprint $table) {
            $table->foreignIdFor(User::class,'user_id')->primary()->constrained()->cascadeOnDelete();
            $table->String('speciality');
            $table->double('hourly_rate');
            $table->boolean('online')->default(false);
            $table->boolean('can_prescribe')->default(false);
            $table->enum('title',['Dr','Mr','Mrs','Ms']);
            $table->enum('charge_type',['SESSION','TIME'])->default('SESSION');

            $table->string('signature',512)->nullable();
            $table->string('seal',512)->nullable();
            $table->boolean('approved')->default(false);
            $table->boolean('is_new')->default(true);
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
        Schema::dropIfExists('user__doctors');
    }
}
