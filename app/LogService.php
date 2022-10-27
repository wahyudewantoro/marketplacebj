<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogService extends Model
{
     protected $table = "log_service";
     protected $guarded = [''];
    public $incrementing = false;
    public $timestamps = false;
}
