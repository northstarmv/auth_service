<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Previous_Projects extends Model
{
    protected $table = 'previous__projects';

    protected $fillable = ['name','desc', 'address', 'phone', 'image_1', 'image_2', 'image_3', 'status','added_by', 'added_time', 'modified_by', 'modified_time'];
}
