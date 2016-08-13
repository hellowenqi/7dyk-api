<?php namespace App\Http\Middleware;

use Closure;
use Session;
use Redirect;
use App\Code;

class Adminauth {
    public function handle($request, Closure $next) {
        if(!Session::get('adminId')){
            return Code::response(101);
        }

        return $next($request);
    }
}
