<?php

namespace App\Http\Middleware;

use App\LogService;
use App\UserService;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Log;

class BasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // return $next($request);
        // $user=UserService::where


        /* $AUTH_USER = 'admin';
        $AUTH_PASS = 'admin'; */
        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));

        $res = false;
        /* $response = $next($request);
        $logEntry = new LogService(); */

        if ($has_supplied_credentials) {
            $user = $_SERVER['PHP_AUTH_USER'];
            $pass = $_SERVER['PHP_AUTH_PW'];
            $cek = UserService::where('username', $user)->where('password_md5', $pass)->first();

            if ($cek != null) {
                /* $logEntry->konten = $response->getContent();
                $logEntry->ip_address = $request->ip();
                $logEntry->tanggal = Carbon::now();
                $logEntry->url = $request->fullUrl();
                $logEntry->kode_bank = $cek->kode_bank;
                $logEntry->http_method = $request->method();
                $logEntry->status_code = $response->getStatusCode();
                $logEntry->save(); */
                
                $res = true;
            }
        }

        if ($res == false) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');

            $res = [
                "Status" => [
                    "IsError" => "True",
                    "ResponseCode" => "401",
                    "ErrorDesc" => "Unauthorized "
                ]
            ];

          /*   $logEntry = new LogService();
            $logEntry->konten = json_encode($res);
            $logEntry->ip_address = $request->ip();
            $logEntry->tanggal = Carbon::now();
            $logEntry->url = $request->fullUrl();
            $logEntry->http_method = $request->method();
            $logEntry->status_code = '401';
            $logEntry->save(); */

            return response()->json($res, 401);
            exit;
        }


        return $next($request);
    }
}
