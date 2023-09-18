<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class User_Client extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'user_id';
    protected $casts = [
        'health_conditions' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function diet_trainer(): HasOne
    {
        return $this->hasOne(User::class, 'id','diet_trainer_id');
    }

    public function physical_trainer(): HasOne
    {
        return $this->hasOne(User::class, 'id','physical_trainer_id');
    }

    public function requests():HasMany
    {
        return $this->hasMany(ClientRequests::class,'client_id','user_id');
    }

    public function subscription():HasOneThrough
    {
        return $this->hasOneThrough(
            UserMainSubscriptionData::class,
            User::class,
            'id',
            'user_id',
        );
    }
}
