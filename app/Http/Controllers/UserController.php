<?php namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Question;
use App\Models\Invite;
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
                    'user_company'      =>  $teacher->user->company,
                    'user_position'     =>  $teacher->user->position,
                    'user_experience'   =>  $teacher->user->experience,
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
                        'user_company'      =>  $user->company,
                        'user_position'     =>  $user->position,
                        'user_experience'   =>  $user->experience,
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
                        'user_company'      =>  $user->company,
                        'user_position'     =>  $user->position,
                        'user_experience'   =>  $user->experience,
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

    public function editUsernow() {
        $id = 1;
        $user = User::where('id', $id)->first();
        if(Request::has("company")) {
            $company = Request::get('company');
            $user->company = $company;
        }
        if(Request::has("position")) {
            $position = Request::get('position');
            $user->position = $position;
        }
        if(Request::has("experience")) {
            $experience = Request::get('experience');
            $user->experience = $experience;
        }
        if(Request::has("introduction")) {
            $introduction = Request::get('introduction');
            $user->introduction = $introduction;
        }
        $user->save();
        return Code::response(0, $user);

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
                    'user_company'      =>  $user->company,
                    'user_position'     =>  $user->position,
                    'user_experience'   =>  $user->experience,
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
                    'user_company'      =>  $user->company,
                    'user_position'     =>  $user->position,
                    'user_experience'   =>  $user->experience,
                    'user_face'         =>  $user->face,
                    'user_introduction' =>  $user->introduction,
                );
            }
        }
        return Code::response(0, $data);
    }

    public function getTeacheranswer() {
        if(Request::has("user_id") && Request::has('page') && Request::has('number')) {
            $user_id = Request::get('user_id');
            $user = User::where('id', $user_id)->first();
            if(!isset($user)) {
                Code::response(102);
            } else if($user->isteacher != 1) {
                Code::response(103);
            }
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page-1)*$number;
            $questions = Question::with('answer')->where('answer_user_id', $user_id)->where('isanswered', 1)->skip($index)->take($number)->get();
            $datas = array();
            foreach($questions as $key => $question) {
                $data['id'] = $question->id;
                $data['prize'] = $question->prize;
                $data['content'] = $question->content;
                $data['time'] = $question->time;
                $data['isanswered'] = $question->isanswered;
                $data['answer_id'] = $question->answer_id;
                $data['listen'] = $question->answer->listen;
                $data['dislike'] = $question->answer->dislike;
                $datas[] = $data;
            }
            return Code::response(0, $datas);
        } else {
            return Code::response(100);
        }
    }

    public function beTeacher() {
        if(Request::has('invite') && Request::has('prize')) {
            $invite_code = Request::get('invite');
            $prize = Request::get('prize');
            $invite = Invite::with('user')->where("invite", $invite_code)->first();
            if(!isset($invite)) {
                return Code::response(401);
            }
            $invite->user->isteacher = 1;
            $invite->user->save();
            $teacher = new Teacher();
            $teacher->prize = $prize;
            $teacher->answernum = 0;
            $teacher->listennum = 0;
            $teacher->income = 0;
            $teacher->user_id = $invite->user->id;
            $teacher->save();
            $invite->delete();
            return Code::response(0, $invite->user);
        } else {
            return Code::response(100);
        }
    }

    public function auth() {
        $wechat = new Wechat;
        if(Request::has('redirect')) {
            $redirect = Request::get('redirect');
        } else {
            $redirect = "http://www.baidu.com";
        }
        Session::put('redirect', $redirect);
        return $wechat->loginWechat("http://h5app.7dyk.com/api/public/api/v1/code");
    }

    public function code() {
        $wechat = new Wechat;
        //$redirect = Session::get('redirect');
        $redirect = "http://h5app.7dyk.com/ama/7dyk/";
        if(Request::has('code')) {
            $code = Request::get('code');
            $openid = $wechat->getOpenid($code);
            $user = User::where('openid', $openid)->first();
            if(isset($user)) {
                Session::put('user_id', $user->id);
                Session::put('openid', $user->openid);
            } else {
                $access_token = Cache::get('access_token');
                $info = $wechat->getUserinfo($access_token, $openid);
                $user = new User;
                $user->face = $info->headimgurl;
                $user->wechat = $info->nickname;
                $user->company = "";
                $user->position = "";
                $user->experience = "";
                $user->introduction = "";
                $user->regist_time = date("Y-m-d H:i:s", time());
                $user->login_time = date("Y-m-d H:i:s", time());
                $user->openid = $openid;
                $user->isteacher = 0;
                $user->save();
                Session::put('user_id', $user->id);
                Session::put('openid', $user->openid);
            }
        }
        return Redirect::to($redirect);
    }
}
