<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    public function payment(Request $request)
    {
        $client = new Client;
        $response = $client->request('POST', route('payment'), [
            'form_params' => [
                'Nop' => $request->Nop,
                'Merchant' => $request->Merchant,
                'DateTime' => $request->DateTime,
                'TotalBayar' =>$request->TotalBayar,
                'Tagihan' =>$request->Tagihan
            ]
        ]);
        return $response;
    }
}
