<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Therapy_Meeting extends Model
{
    protected $guarded = [];
    public function therapy(): HasOne
    {
        return $this->hasOne(user_therapy::class, 'therapy_id', 'id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'user_id', 'id');
    }
}
