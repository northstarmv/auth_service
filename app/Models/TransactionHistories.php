<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin Builder
 */
class TransactionHistories extends Model
{
    protected $guarded = [];

    public function user():HasOne
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function payee():HasOne
    {
        return $this->hasOne(User::class,'id','payee_id');
    }
}
