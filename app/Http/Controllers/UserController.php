<?php namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Code;
use App\Wechat;
use Session;
use Request;
use Redirect;
use Cache;

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
                    'teacher_prize'     =>  $teacher->prize,
                    'answer_number'     =>  $teacher->answernum,
                );
                $datas[] = $data;
            }
            return Code::response(0, $datas);
        } else {
            return Code::response(100);
        }
    }

    public function getUserinfo() {
        if(Request::has('id')) {
            $id = Request::get('id');
            $user = User::with('teacher')->where('id', $id)->first();
            if(isset($user)) {
                if($user->isteacher) {
                    $data = array(
                        'is_teacher'        =>  $user->isteacher,
                        'user_id'           =>  $user->id,
                        'user_name'         =>  $user->wechat,
                        'user_title'        =>  $user->title,
                        'user_face'         =>  $user->face,
                        'user_introduction' =>  $user->introduction,
                        'teacher_income'    =>  $user->teacher->income,
                        'teacher_prize'     =>  $user->teacher->prize,
                        'listen_num'        =>  $user->teacher->listennum,
                        'answer_num'        =>  $user->teacher->answernum,
                    );
                } else {
                    $data = array(
                        'is_teacher'        =>  $user->isteacher,
                        'user_id'           =>  $user->id,
                        'user_name'         =>  $user->wechat,
                        'user_title'        =>  $user->title,
                        'user_face'         =>  $user->face,
                        'user_introduction' =>  $user->introduction,
                    );
                }
                return Code::response(0, $data);
            } else {
                return Code::response(100);
            }
        }
    }

    public function getUsernow() {
        $id = 1;
        $user = User::with('teacher')->where('id', $id)->first();
        if(isset($user)) {
            if($user->isteacher) {
                $data = array(
                    'is_teacher'        =>  $user->isteacher,
                    'user_id'           =>  $user->id,
                    'user_name'         =>  $user->wechat,
                    'user_title'        =>  $user->title,
                    'user_face'         =>  $user->face,
                    'user_introduction' =>  $user->introduction,
                    'teacher_income'    =>  $user->teacher->income,
                    'teacher_prize'     =>  $user->teacher->prize,
                    'listen_num'        =>  $user->teacher->listennum,
                    'answer_num'        =>  $user->teacher->answernum,
                );
            } else {
                $data = array(
                    'is_teacher'        =>  $user->isteacher,
                    'user_id'           =>  $user->id,
                    'user_name'         =>  $user->wechat,
                    'user_title'        =>  $user->title,
                    'user_face'         =>  $user->face,
                    'user_introduction' =>  $user->introduction,
                );
            }
        }
        return Code::response(0, $data);
    }

    public function auth() {
        $wechat = new Wechat;
        if(Request::has('redirect')) {
            $redirect = Request::get('redirect');
        } else {
            $redirect = "http://www.baidu.com";
        }
        Session::put('redirect', $redirect);
        return $wechat->loginWechat("http://h5app.7dyk.com/dev/wq/public/api/v1/code");
    }

    public function code() {
        $wechat = new Wechat;
        $redirect = Session::get('redirect');
        if(Request::has('code')) {
            $code = Request::get('code');
            $openid = $wechat->getOpenid($code);
            $user = User::where('openid', $openid)->first();
            if(isset($user)) {
                Session::put('user_id', $user->id);
            } else {
                $access_token = Cache::get('access_token');
                $info = $wechat->getUserinfo($access_token, $openid);
                $user = new User;
                $user->face = $info->headimgurl;
                $user->wechat = $info->nickname;
                $user->title = "";
                $user->introduction = "";
                $user->regist_time = date("Y-m-d H:i:s", time());
                $user->login_time = date("Y-m-d H:i:s", time());
                $user->openid = $openid;
                $user->isteacher = 0;
                $user->save();
                Session::put('user_id', $user->id);
            }
        }
        return Redirect::to($redirect);
    }

}
