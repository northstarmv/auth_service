<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
/**
 * @mixin Builder
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;

    protected $guarded = [];
    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'trial_used' => 'boolean',
        'health_data' => 'boolean',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function admin():HasOne
    {
        return $this->hasOne(User_Admin::class,'user_id','id');
    }

    public function moderator():HasOne
    {
        return $this->hasOne(User_Moderator::class,'user_id','id');
    }

    public function gym():HasOne
    {
        return $this->hasOne(User_Gym::class,'user_id','id');
    }

    public function trainer():HasOne
    {
        return $this->hasOne(User_Trainer::class,'user_id','id');
    }

    public function doctor():HasOne
    {
        return $this->hasOne(User_Doctor::class,'user_id','id');
    }

    public function client():HasOne
    {
        return $this->hasOne(User_Client::class,'user_id','id');
    }

    public function subscriptions():HasOne
    {
        return $this->hasOne(UserMainSubscriptionData::class,'user_id','id');
    }

    public function qualifications():HasMany
    {
        return $this->hasMany(Qualification::class,'user_id','id');
    }

    public function subscription():HasOne
    {
        return $this->hasOne(UserMainSubscriptionData::class,'user_id','id');
    }

    public function wallet():HasOne
    {
        return $this->hasOne(UserWallet::class,'user_id','id');
    }

}
