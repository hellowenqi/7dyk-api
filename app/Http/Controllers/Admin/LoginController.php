<?php namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Facades\Session;
require_once 'code/Code.class.php';

class LoginController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */

	public function  login(){
        if($input=Input::all()){
            $code=Session::get('milkcaptcha');
            if($input['code']!=$code){
                return back()->with('msg','验证码错误');
            }else {
                echo '验证码正确，成功登陆';
            }
        }else{
            return view('admin.login');
        }
    }

    public function code(){
        $test=new CaptchaBuilder;
        $test->build();
        $phrase=$test->getPhrase();
        Session::flash('milkcaptcha', $phrase);
        $test->output();
    }


	public function index()
	{
		//
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}



