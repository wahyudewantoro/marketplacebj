<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use App\LogService;

class TestCon extends Controller
{
    //
    public function index(){
        $data=LogService::get();

        dd($data);
    }
}
