<?php namespace App\Http\Middleware;

use Closure;
use Response;

class Before {

    public function handle($request, Closure $next) {
        if ($request->getMethod() == "OPTIONS") {
            header("Access-Control-Allow-Origin:*");
            header("Access-Control-Allow-Methods:GET,POST,OPTIONS");
            header("Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With");
            return Response::json(array(), 200);
        }
        return $next($request);
    }

}
