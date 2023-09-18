<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin Builder
 */
class ClientRequests extends Model
{
    protected $guarded = [];

    public function trainer(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'trainer_id');
    }
}
