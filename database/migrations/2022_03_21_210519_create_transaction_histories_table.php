<?php

use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class,'user_id');
            $table->foreignIdFor(UserWallet::class,'wallet_id');
            $table->unsignedBigInteger('payee_id')->nullable();
            $table->enum('type',['Credit','Debit']);
            $table->double('amount');
            $table->text('transactionId')->default('INTERNAL_WALLET');
            $table->text('description')->default('Adjustments');
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
        Schema::dropIfExists('transaction_histories');
    }
}
