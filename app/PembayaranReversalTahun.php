<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PembayaranReversalTahun extends Model
{
    //
    protected $table = 'WS_REVERSAL_TAHUN';
       protected $guarded = [''];
       public $incrementing = true;
       public $timestamps = true;

}
