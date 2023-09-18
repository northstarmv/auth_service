<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
/**
 * @mixin Builder
 */
class CallHistory extends Model
{
    protected $guarded = [];

    public function receiver():HasOne
    {
        return $this->hasOne(User::class,'id','receiver_id');
    }
}
