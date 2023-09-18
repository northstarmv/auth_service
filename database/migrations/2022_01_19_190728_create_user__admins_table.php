<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user__admins', function (Blueprint $table) {
            $table->foreignIdFor(User::class,'user_id')->primary()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Insert default user data here
        DB::table('user__admins')->insert([
            'user_id' => '1'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user__admins');
    }
}
