<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PembayaranTahun extends Model
{
    //
    protected $table = 'WS_PEMBAYARAN_TAHUN';
    protected $guarded = [''];
    public $incrementing = true;
    public $timestamps = true;
}



