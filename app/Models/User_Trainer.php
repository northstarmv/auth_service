<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin Builder
 */
class User_Trainer extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_insured' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function qualifications():HasMany
    {
        return $this->hasMany(Qualification::class,'user_id','user_id');
    }



}
