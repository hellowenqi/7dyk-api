<?php namespace App\Http\Middleware;

use Closure;
use Session;
use Redirect;
use App\Code;
use App\Wechat;

class Wechatauth {

    public function handle($request, Closure $next) {
        if(!Session::has('user_id')) {
            //Session::put("user_id", 30);
            //Session::put("openid", 'on7Ogwj04PIfSCxa2ypeMrGuvAGU');
            $wechat = new Wechat();
            $data['login_url'] = "http://h5app.7dyk.com/ama/api/public/api/v1/auth";
            return Code::response(101, $data);
        }

        return $next($request);
    }
}
