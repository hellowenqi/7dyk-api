<?php namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Request;

class UserController extends Controller {

    public function __construct() {
        return;
    }

    public function getTeacher() {
        if(Request::has('page') && Request::has('number')) {
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page-1)*$number;
            $teachers = Teacher::with('user')->skip($index)->take($number)->get();
            $datas = array();
            foreach($teachers as $key => $teacher) {
                $data = array(
                    'user_id'           =>  $teacher->user->id,
                    'user_name'         =>  $teacher->user->wechat,
                    'user_title'        =>  $teacher->user->title,
                    'user_face'         =>  $teacher->user->face,
                    'user_prize'        =>  $teacher->prize,
                    'answer_number'     =>  $teacher->answernum,
                );
                $datas[] = $data;
            }
            return $this->response(0, $datas);
        } else {
            return $this->response(100);
        }
    }

    public function getUserinfo() {
        if(Request::has('id')) {
            $id = Request::get('id');
            $user = User::with('teacher')->where('id', $id)->get();
            dd($user);
        }
    }

    public function response($errCode, $datas = array()) {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");
        header("Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With");
        if($errCode == 100) {
            $json = array(
                'errCode'   =>  100,
                'msg'       =>  '参数错误',
                'data'      =>  $datas,
            );
        } else {
            $json = array(
                'errCode'   =>  0,
                'msg'       =>  'ok',
                'data'      =>  $datas,
            );
        }
        return json_encode($json, JSON_UNESCAPED_UNICODE+JSON_UNESCAPED_SLASHES);
    }

}
