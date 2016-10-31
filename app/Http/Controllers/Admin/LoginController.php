<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Gregwar\Captcha\CaptchaBuilder;
use Session;
use Request;
use Cookie;
use Crypt;
use App\Code;
use DB;

class LoginController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

	public function  login(){
        $username = Request::get('username');
		$password = Request::get("password");
		$captcha = Request::get("captcha");
		$aa = Session::get('captcha');
		if($captcha && $captcha == Session::get('captcha')){
			Session::put('captcha', '');
		}else{
			return Code::response(404, "验证码错误");
		}
		if($username && $password){
			DB::enableQueryLog();
			$admin = Admin::where('username', $username)->where('password',md5($password))->first();
			if($admin){
				Session::put('adminId', $admin->id);
				return Code::response(0);
			}else{
				return Code::response(404,'用户名或密码错误');
			}
		}else{
			return Code::response(100);
		}
    }

	//生成验证码
    public function code(){
        $test=new CaptchaBuilder;
        $test->build();
        $phrase=$test->getPhrase();
        Session::put('captcha', $phrase);
		header('Content-type: image/jpeg');
        $test->output();
    }
}



