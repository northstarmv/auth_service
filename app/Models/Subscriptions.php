<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
/**
 * @mixin Builder
 */
class Subscriptions extends Model
{
    protected $guarded = [];
    protected $casts = [
        'discounted' => 'boolean',
    ];
}
