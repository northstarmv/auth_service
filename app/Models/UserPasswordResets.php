<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin Builder
 */
class UserPasswordResets extends Model
{
    protected $guarded = [];

    public function user():HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
