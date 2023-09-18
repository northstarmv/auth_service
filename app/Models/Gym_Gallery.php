<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gym_Gallery extends Model
{
    protected $guarded = [];
    protected $casts = [
        'gym_gallery'=>'json'
    ];

    public function gym():BelongsTo
    {
        return $this->belongsTo(User_Gym::class,'gym_id','id');
    }
}
