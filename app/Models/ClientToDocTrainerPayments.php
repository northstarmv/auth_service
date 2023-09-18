<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin Builder
 */
class ClientToDocTrainerPayments extends Model
{
    protected $guarded = [];

    public function client():HasOne
    {
        return $this->hasOne(User::class, 'id', 'client_id');
    }
}
