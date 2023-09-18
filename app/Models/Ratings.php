<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin Builder
 */
class Ratings extends Model
{
    protected $primaryKey = 'reviewer';
    protected $guarded = [];

    public function reviewer():HasOne
    {
        return $this->hasOne(User::class, 'id', 'reviewer');
    }

    public function reviewee():HasOne
    {
        return $this->hasOne(User::class, 'id', 'reviewee');
    }

}
