<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class therapy_working_hours extends Model
{
    protected $guarded = [];


    public function user(): BelongsTo
    {
        return $this->belongsTo(user_therapy::class, 'therapy_id','id');
    }
}
