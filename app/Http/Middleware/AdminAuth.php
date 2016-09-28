<?php namespace App\Http\Middleware;

use Closure;
use Session;
use App\Code;
use Config;

class Adminauth {
    public function handle($request, Closure $next) {
        if(!Session::get('adminId')){
            return Code::response(101,['login_url'=> Config::get('urls.adminLogin')]);
        }
        return $next($request);
    }
}
