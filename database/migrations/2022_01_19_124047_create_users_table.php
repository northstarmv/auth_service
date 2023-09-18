<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role',['admin','moderator','gym','trainer','doctor','client','therapy'])->default('client');

            //Common Personal Info
            $table->string('phone')->unique();
            $table->string('nic')->unique();
            $table->enum('gender',['male','female']);
            $table->date('birthday');

            $table->string('address')->default(' ');
            $table->string('country_code');
            $table->string('currency');

            //Avatar
            $table->string('avatar_url')->default('default.jpg');

            //Trial
            $table->boolean('trial_used')->default(false);

            //Health Data Consent
            $table->boolean('health_data')->default(false);

            $table->timestamps();
        });

        // Insert default user data here
        DB::table('users')->insert([
            'id' => '1',
            'name' => 'admin',
            'email' => 'admin@similater.com',
            'phone' => '070000000',
            'nic' => 'N/A',
            'gender' => 'male',
            'birthday' => '1990-01-01',
            'country_code' => 'N/A',
            'currency'=> 'N/A',
            'role' => 'admin',
            'password' => '$2b$15$ONxxXs9i3wb9seagn1CFUuMOofbsO3nFJfF/FV1r1jK1x9eD0bvyu',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
