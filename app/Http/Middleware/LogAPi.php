<?php

namespace App\Http\Middleware;

use App\LogService;
use  \Carbon\Carbon;
use Closure;

class LogAPi
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
        return $next($request);
    }

    
    public function terminate($request, $response)
    {
        $logEntry = new LogService();
        $logEntry->konten = $response->getContent();
        $logEntry->ip_address = $request->ip();
        $logEntry->tanggal = Carbon::now();
        $logEntry->url = $request->fullUrl();
        $logEntry->http_method =$request->method();
        $logEntry->status_code= $response->getStatusCode();
        $logEntry->save();
    }
}
