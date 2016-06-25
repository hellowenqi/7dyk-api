<?php namespace App\Http\Middleware;

use Closure;
use Session;
use Redirect;
use App\Code;
use App\Wechat;

class Wechatauth {

    public function handle($request, Closure $next) {
        if(!Session::has('user_id')) {
            $wechat = new Wechat();
            return Code::response(101);
        }

        return $next($request);
    }
}
