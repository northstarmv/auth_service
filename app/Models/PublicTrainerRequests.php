<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
/**
 * @mixin Builder
 */
class PublicTrainerRequests extends Model
{
    protected $guarded = [];

    protected $casts = [
      'request_data' => 'json'
    ];
}
