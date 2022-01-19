<?php

namespace App\Http\Middleware;

use App\UserService;
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
        if ($has_supplied_credentials) {
            $user = $_SERVER['PHP_AUTH_USER'];
            $pass = $_SERVER['PHP_AUTH_PW'];
            $cek = UserService::where('username', $user)->where('password_md5',$pass)->first();

            // $cek=DB::select("select * from ws_user username=''")
            /* Log::info('username '.$user);
            Log::info('password_md5 '.$pass);
            Log::info(count($cek)); */
            if ($cek != null) {
                $res = true;
            }

        }

        if ($res == false) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            exit;
        }

        // batas
/* 
        $is_not_authenticated = (!$has_supplied_credentials ||
            $_SERVER['PHP_AUTH_USER'] != $AUTH_USER ||
            $_SERVER['PHP_AUTH_PW']   != $AUTH_PASS);
        if ($is_not_authenticated) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            exit;
        } */
        return $next($request);
    }
}
