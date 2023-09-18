<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
/**
 * @mixin Builder
 */
class User_Doctor extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'user_id';
    protected $casts = [
        'online' => 'boolean',
        'can_prescribe' => 'boolean',
        'approved' => 'boolean',
        'is_new' => 'boolean',
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
