<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class user_therapy extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'id';
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function therapy_working_hours():HasMany
    {
        return $this->hasMany(therapy_working_hours::class,'therapy_id','therapy_Id');
    }

    public function therapy__qualifications():HasMany
    {
        return $this->hasMany(Therapy_Qualification::class,'therapy_id','therapy_Id');
    }
}
