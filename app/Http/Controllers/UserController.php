<?php namespace App\Http\Controllers;

use App\Models\Listen;
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

use Illuminate\Support\Facades\DB;

class UserController extends Controller {

    public function __construct() {
        return;
    }

    //导师按照设定好的顺序排序
    public function getTeacher() {
        DB::enableQueryLog();
        if(Request::has('page') && Request::has('number')) {
            $page = Request::get('page');
            $number = Request::get('number');
            $search = Request::get('search');
            $index = ($page-1)*$number;
            $query1 = Teacher::with('user');
            $query2 = Teacher::with('user');
            $query3 = Teacher::with('user');
            if($search){
                $query1->whereHas('user', function($query) use($search){
                    $query->where(function($query) use($search) {
                        $query->where('wechat', 'like', "%$search%")
                            ->orWhere('company', 'like', "%$search%")
                            ->orWhere('position', 'like', "%$search%");
                    });
                });
                $query2->whereHas('user', function($query) use($search){
                    $query->where(function($query) use($search) {
                        $query->where('wechat', 'like', "%$search%")
                            ->orWhere('company', 'like', "%$search%")
                            ->orWhere('position', 'like', "%$search%");
                    });
                });
                $query3->whereHas('user', function($query) use($search){
                    $query->where(function($query) use($search) {
                        $query->where('wechat', 'like', "%$search%")
                            ->orWhere('company', 'like', "%$search%")
                            ->orWhere('position', 'like', "%$search%");
                    });
                });
            }
            //排过序的导师
            $queryOrdered = $query1->where('order', '>', $index)->where('order', '<=', $index + $number);
            $countOrdered = $queryOrdered->count();
//            var_dump(DB::getQueryLog());
//            exit;
            $teacherOrdered = $countOrdered > 0 ? $queryOrdered->with('user')->orderBy('order', 'asc')->get() : array();
            //之前排过序的个数
            $indexOrdered = $query2->where('order', '<=', $index)->count();
            $teacherUnOrdered = array();
            $countUnordered = $number - $countOrdered;
            if($countUnordered > 0){
                $queryUnOrdered = $query3->where('order', null)->with('user')->take($countUnordered)->skip($index - $indexOrdered);
                $teacherUnOrdered = $queryUnOrdered->get();
            }
            $countUnordered = count($teacherUnOrdered);
            $oi = 0; $ui = 0;
            $i = 0;
            $teachers = array();
            while($oi < $countOrdered && $ui < $countUnordered){
                if($teacherOrdered[$oi]->order - $index - 1 == $i){
                    array_push($teachers, $teacherOrdered[$oi]);
                    $oi++;
                }else{
                    array_push($teachers, $teacherUnOrdered[$ui]);
                    $ui++;
                }
                $i++;
            }
            if($oi == $countOrdered) while($ui < $countUnordered){array_push($teachers, $teacherUnOrdered[$ui++]);};
            if($ui == $countUnordered) while($oi < $countOrdered){array_push($teachers, $teacherOrdered[$oi++]);;};

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
            $user_id = Session::get('user_id');
            $user = User::with('teacher')->where('id', $id)->first();
            if(isset($user)) {
                if($id == $user_id) {
                    $same = 1;
                } else {
                    $same = 0;
                }
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
                        'user_same'         =>  $same,
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
                        'user_same'         =>  $same,
                    );
                }
                return Code::response(0, $data);
            } else {
                return Code::response(100);
            }
        }
    }

    public function editUsernow() {
        $id = Session::get('user_id');
//        $id = 33;
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
        if(Request::has("prize")) {
            $teacher = Teacher::where('user_id', $id)->first();
            if(!$teacher){
                return Code::response(103);
            }
            $teacher->prize = Request::get('prize');
            $teacher->save();
        }
        $user->save();
        return Code::response(0, $user);

    }

    public function getUsernow() {
        $id = Session::get('user_id');
//        $id = 33;
        $user = User::with('teacher')->where('id', $id)->first();
        $data = '';
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
                    'listen_num'        =>  ($user->teacher->listennum_virtual == 0) ? $user->teacher->listennum : $user->teacher->listennum_virtual,
                    'answer_num'        =>  ($user->teacher->answernum_virtual == 0) ? $user->teacher->answernum : $user->teacher->answernum_virtual,
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
            $self_id = Session::get('user_id');
//            $self_id = 33;
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page-1)*$number;
            $questions = Question::with('answer')->where('answer_user_id', $user_id)->where('isanswered', 1)->skip($index)->take($number)->get();
            $datas = array();
            foreach($questions as $key => $question) {
                if($question->user_id == $self_id || $question->answer_user_id == $self_id) $data['is_payed'] = 1;
                else {
                    $listen = Listen::where('answer_id', $question->answer_id)->where('user_id', $self_id)->first();
                    if($listen) $data['is_payed'] = 1;
                    else $data['is_payed'] = 0;
                }
                $data['id'] = $question->id;
                $data['prize'] = $question->prize;
                $data['content'] = $question->content;
                $data['time'] = strtotime($question->answer->time);
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
            $user_id = Session::get('user_id');
            $invite = Invite::where("invite", $invite_code)->first();
            if(!isset($invite)) {
                return Code::response(401);
            }
            $user = User::find($user_id);
            $user->isteacher = 1;
            $user->save();
            $teacher = new Teacher();
            $teacher->prize = $prize;
            $teacher->answernum = 0;
            $teacher->listennum = 0;
            $teacher->income = 0;
            $teacher->user_id = $user_id;
            $teacher->save();
            $invite->delete();
            return Code::response(0, $invite->user);
        } else {
            return Code::response(100);
        }
    }

    public function auth() {
        $wechat = new Wechat;
        if(Request::has('redirect_url')) {
            $redirect = Request::get('redirect_url');
        } else {
            $redirect = "http://www.baidu.com";
        }
        Session::put('redirect', $redirect);
        return $wechat->loginWechat("http://h5app.7dyk.com/ama/api/public/api/v1/code");
    }

    public function code() {
        $wechat = new Wechat;
        $redirect = Session::get('redirect');
//        $redirect = "http://h5app.7dyk.com/ama/7dyk/";
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
