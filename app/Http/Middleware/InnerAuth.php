<?php namespace App\Http\Middleware;

use Closure;
use Session;
use Redirect;
use App\Code;
use Config;

class InnerAuth {
    public function handle($request, Closure $next) {
        if($request->get('token') != Config::get('inner.token')){
            return Code::response(404, 'token错误');
        }
        return $next($request);
    }
}
