<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserService extends Model
{
    //
    protected $table = "WS_USER";
    // protected $fillable = ['nama', 'kelas', 'kelas_alias', 'sort'];
    protected $primaryKey = 'id';
}
