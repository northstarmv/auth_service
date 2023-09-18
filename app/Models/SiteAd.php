<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteAd extends Model
{
    protected $table = 'site_ads';

    protected $fillable = ['name','ad_url', 'duration', 'order', 'ad_img', 'status','added_by', 'added_time', 'modified_by', 'modified_time'];
}
