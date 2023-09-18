<?php
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateUserTherapiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_therapies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class,'user_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('working_type');
            $table->tinyInteger('paying_type');
            $table->double('hourly_rate')->nullable();
            $table->double('session_rate')->nullable();
            $table->integer('session_duration')->nullable();
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
        Schema::dropIfExists('user_therapies');
    }
}