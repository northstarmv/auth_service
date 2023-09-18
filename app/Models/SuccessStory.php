<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessStory extends Model
{
    protected $table = 'success_stories';

    protected $fillable = ['name', 'age', 'desc', 'point_1', 'ponit_2', 'point_3', 'ponit_4', 'before_img', 'after_img', 'status','added_by', 'added_time', 'modified_by', 'modified_time'];
}
