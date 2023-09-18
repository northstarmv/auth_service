<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin Builder
 */
class User_Gym extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'user_id';
    protected $casts = [
        'gym_facilities' => 'json',
        'gym_galley' => 'json',
    ];

    public function gallery():HasMany
    {
        return $this->hasMany(Gym_Gallery::class,'user_id','user_id');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class,'id','user_id');
    }

}
