<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    //
    protected $table = 'WS_PEMBAYARAN';
    protected $guarded = [];
    public $incrementing = true;
    public $timestamps = true;
}
