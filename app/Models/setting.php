<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['key', 'value', 'updated_at'];
}
