<?php
namespace App\Http\Controllers;

use App\Models\History;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Listen;
use App\Models\Like;
use App\Code;
use App\Wechat;
use App\Wechat\WxPayConfig;
use App\Wechat\WxPayApi;
use App\Wechat\JsApiPay;
use App\Wechat\WxPayUnifiedOrder;
use Request;
use Session;
use Cache;
use Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class QuestionController extends Controller
{

    public function __construct()
    {
        return;
    }

    public function test() {
        if(Request::has('url')) {
            $url = Request::get('url');
        } else {
            $url = "http://h5app.7dyk.com/ama/7dyk/";
        }
        $wechat = new Wechat();
        $signPackage = $wechat->getSignPackage($url);
        return Code::response(0, $signPackage);
    }

    //热点问题
    public function getTopic()
    {
//      DB::enableQueryLog();
        if (Request::has('page') && Request::has('number')) {
            $user_id = Session::get('user_id');
//            $user_id = 33;
            $query1 = Answer::with('question');
            $query2 = Answer::with('question');
            $query3 = Answer::with('question');
            $search = Request::get('search');
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page - 1) * $number;
            if($search && $page == 1){
                $history = new History();
                $history->user_id = $user_id;
                $history->search = $search;
                $history->save();
            }
            if($search) {
                $query1->whereHas('question', function ($query) use ($search) {
                    $query->where('content', 'like', "%$search%");
                });
                $query2->whereHas('question', function ($query) use ($search) {
                    $query->where('content', 'like', "%$search%");
                });
                $query3->whereHas('question', function ($query) use ($search) {
                    $query->where('content', 'like', "%$search%");
                });
            }
            //根据order返回位置
            //本次排过序的
            $queryOrdered = $query1->where('order', '>', $index)->where('order', '<=', $index + $number);
            $countOrdered = $queryOrdered->count();
            $answerOrdered = ($countOrdered > 0) ? $queryOrdered->with('teacher.teacher')->orderBy('order','asc')->get(): array();
            //之前有order数目：
            $indexOrdered = $query2->where('order', '<=', $index)->count();
            $answerUnOrdered = array();
            $countUnordered = $number - $countOrdered;
            if($countUnordered > 0){
                $queryUnOrdered = $query3->where('order', null)->with('teacher.teacher')->orderBy('weight', 'desc')->take($countUnordered)->skip($index - $indexOrdered);
                $answerUnOrdered = $queryUnOrdered->get();
            }
            $countUnordered = count($answerUnOrdered);
            $oi = 0; $ui = 0;
            $i = 0;
            $answers = array();
            while($oi < $countOrdered && $ui < $countUnordered){
                if($answerOrdered[$oi]->order - $index - 1 == $i){
                    array_push($answers, $answerOrdered[$oi]);
                    $oi++;
                }else{
                    array_push($answers, $answerUnOrdered[$ui]);
                    $ui++;
                }
                $i++;
            }
            //            var_dump(DB::getQueryLog());
//            exit;
            if($oi == $countOrdered) while($ui < $countUnordered){array_push($answers, $answerUnOrdered[$ui++]);};
            if($ui == $countUnordered) while($oi < $countOrdered){array_push($answers, $answerOrdered[$oi++]);;};
            $datas = array();
            foreach ($answers as $key => $answer) {
                $listen = Listen::where('user_id', $user_id)->where('answer_id', $answer->id)->first();
                if(isset($listen)) {
                    $isPayed = 1;
                } else {
                    $isPayed = 0;
                }
                if($answer->answer_user_id == $user_id || $answer->question_user_id == $user_id) {
                    $isPayed = 1;
                }
                $data = array(
                    'question_id'           =>  $answer->question->id,
                    'question_content'      =>  $answer->question->content,
                    'question_prize'        =>  $answer->question->prize,
                    'teacher_id'            =>  $answer->question->teacher->id,
                    'teacher_name'          =>  $answer->teacher->wechat,
                    'teacher_company'       =>  $answer->teacher->company,
                    'teacher_position'      =>  $answer->teacher->position,
                    'teacher_experience'    =>  $answer->teacher->experience,
                    'teacher_face'          =>  $answer->teacher->face,
                    'teacher_prize'         =>  $answer->teacher->teacher->prize,
                    'answer_id'             =>  $answer->id,
                    'answer_listen'         =>  $answer->listen_virtual == 0 ? $answer->listen : $answer->listen_virtual,
                    'answer_like'           =>  $answer->like_virtual == 0  ? $answer->like : $answer->like_virtual,
                    'answer_audio'          =>  $answer->audio,
                    'answer_ispayed'        =>  $isPayed,
                );
                $datas[] = $data;
            }
            return Code::response(0, $datas);
        } else {
            return Code::response(100);
        }
    }
    //返回问题详情
    public function getQuestion()
    {
        if (Request::has('id')) {
            $user_id = Session::get('user_id');
            $question_id = Request::get('id');
            $question = Question::with('answer')->with('user')->with('teacher.teacher')->where('id', $question_id)->first();
            if (isset($question)) {
                if($question->isanswered == 1) {
                    $like = Like::where('user_id', $user_id)->where('answer_id', $question->answer->id)->first();
                    if(isset($like)) {
                        $isliked = 1;
                    } else {
                        $isliked = 0;
                    }
                    $listen = Listen::where('user_id', $user_id)->where('answer_id', $question->answer->id)->first();
                    if(isset($listen)) {
                        $isPayed = 1;
                    } else {
                        $isPayed = 0;
                    }
                    if($question->answer_user_id == $user_id || $question->question_user_id == $user_id) {
                        $isPayed = 1;
                    }
                    $data = array(
                        'question_id' => $question->id,
                        'question_content' => $question->content,
                        'question_prize' => $question->prize,
                        'question_time' => $question->time,
                        'user_id' => $question->user->id,
                        'user_face' => $question->user->face,
                        'user_name' => $question->user->wechat,
                        'teacher_id' => $question->teacher->id,
                        'teacher_name' => $question->teacher->wechat,
                        'teacher_company' =>  $question->teacher->company,
                        'teacher_position' =>  $question->teacher->position,
                        'teacher_experience' =>  $question->teacher->experience,
                        'teacher_face' => $question->teacher->face,
                        'teacher_prize' => $question->teacher->teacher->prize,
                        'answer_id' => $question->answer->id,
                        'answer_listen' => $question->answer->listen_virtual == 0 ? $question->answer->listen : $question->answer->listen_virtual,
                        'answer_like' => $question->answer->like_virtual == 0 ? $question->answer->like : $question->answer->like_virtual,
                        'answer_audio' => $question->answer->audio,
                        'answer_time' => $question->answer->time,
                        'answer_ispayed' => $isPayed,
                        'answer_isliked' => $isliked,
                        'isanswered' => $question->isanswered,
                    );
                } else {
                    $data = array(
                        'question_id' => $question->id,
                        'question_content' => $question->content,
                        'question_prize' => $question->prize,
                        'question_time' => $question->time,
                        'user_id' => $question->user->id,
                        'user_face' => $question->user->face,
                        'user_name' => $question->user->wechat,
                        'teacher_id' => $question->teacher->id,
                        'teacher_name' => $question->teacher->wechat,
                        'teacher_company' =>  $question->teacher->company,
                        'teacher_position' =>  $question->teacher->position,
                        'teacher_experience' =>  $question->teacher->experience,
                        'teacher_face' => $question->teacher->face,
                        'teacher_prize' => $question->teacher->teacher->prize,
                        'isanswered' => $question->isanswered,
                    );
                }
                return Code::response(0, $data);
            } else {
                return Code::response(201);
            }
        } else {
            return Code::response(100);
        }
    }
    //添加问题
    public function addQuestion(){
        if (Request::has('prize') && Request::has('content') && Request::has('answer_user_id') && Request::has('question_user_id')) {
            $data['prize'] = Request::input('prize');//接值
            $data['content'] = Request::input('content');
            $data['answer_user_id'] = Request::input('answer_user_id');
            $data['question_user_id'] = Request::input('question_user_id');
            $data['isanswered'] = 0;
            $data['answer_id'] = 0;
            $data['time'] = date("Y-m-d H:i:s", time());

            $re = DB::table('question')->insert($data);
            //返回结果
            return Code::response(0, $data);
        } else {
            return Code::response(100);
        }
    }

    public function testQuestion() {
        if (Request::has('content') && Request::has('answer_user_id')) {
            $user_id = Session::get('user_id');
            $time = time();
            $name = md5("$user_id$time");
            $teacher = Teacher::where('user_id', Request::get('answer_user_id'))->first();
            if(!isset($teacher)) {
                return Code::response(103);
            }

            //$prize = (int)$teacher->prize;
            $prize = Config::get('app.DEV') ? 1 : (int)$teacher->prize;
            $content = Request::input('content');
            $answer_user_id = Request::input('answer_user_id');

            $tools = new JsApiPay();
            $openid = Session::get('openid');

            $input = new WxPayUnifiedOrder();
            $input->SetBody("body");
            $input->SetAttach($name);
            $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
            $input->SetTotal_fee($prize);
            date_default_timezone_set('PRC');
            $input->SetTime_start(date("YmdHis", time()));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag("tag");
            $input->SetNotify_url("http://h5app.7dyk.com/ama/api/public/api/v1/notify");
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($openid);
            $order = WxPayApi::unifiedOrder($input);
            $jsApiParameters = json_decode($tools->GetJsApiParameters($order));

            $question = new Question();
            $question->prize = $prize;
            $question->content = $content;
            $question->answer_user_id = $answer_user_id;
            $question->question_user_id = $user_id;
            $question->isanswered = 0;
            $question->answer_id = 0;
            $question->time = date("Y-m-d H:i:s", time());
            Cache::put($name, $question, 10);
            return Code::response(0, $jsApiParameters);
            //$re = DB::table('question')->insert($data);
        } else {
            return Code::response(100);
        }
    }
    //我问的问题
    public function myQuestion()
    {
        if (Request::has('page') && Request::has('number')) {
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page - 1) * $number;
            $user_id = Session::get('user_id');
//            $user_id = 33;
            $arr = DB::table('question')->where('question_user_id', $user_id)->orderBy('isanswered', 'desc')->orderBy('time', 'desc')->skip($index)->take($number)->get();
            foreach($arr as $key => $data) {
                if($data->isanswered == 1) {
                    $answer = Answer::where('id', $data->answer_id)->first();
                    $arr[$key]->listen = $answer->listen_virtual ==0 ? $answer->listen : $answer->listen_virtual;
                    $arr[$key]->like = $answer->like_virtual == 0  ? $answer->like : $answer->like_virtual;
                    $arr[$key]->time = strtotime($answer->time);
//                    var_dump($arr[$key]->time);
//                    exit;
                }
            }
            //返回结果
            if ($arr) {
                return Code::response(0, $arr);
            } else {
                return Code::response(0);
            }
        } else {
            return Code::response(100);
        }
    }

    //问我的问题
    public function myAnswer()
    {
        if (Request::has('page') && Request::has('number')) {
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page - 1) * $number;
            $user_id = Session::get('user_id');

            $arr = DB::table('question')->where('answer_user_id', $user_id)->orderBy('isanswered', 'asc')->skip($index)->take($number)->get();//打印数组
            foreach($arr as $key => $data) {
                if($data->isanswered == 1) {
                    $answer = Answer::with('user')->where('id', $data->answer_id)->first();
                    $arr[$key]->listen = $answer->listen_virtual ==0 ? $answer->listen : $answer->listen_virtual;
                    $arr[$key]->like = $answer->like_virtual == 0  ? $answer->like : $answer->like_virtual;
                }
                $user = User::where('id' , $data->question_user_id)->first();
                $arr[$key]->user_name = $user->wechat;
                $arr[$key]->user_face = $user->face;
            }

            //返回结果
            if ($arr) {
                return Code::response(0, $arr);
            } else {
                return Code::response(0);
            }
        } else {
            return Code::response(100);
        }
    }

    //听过的问提
    public function myListen()
    {
        if (Request::has('page') && Request::has('number')) {
            $user_id = Session::get('user_id');
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page - 1) * $number;

            $listens = Listen::with("answer.question.teacher")->where("user_id", $user_id)->skip($index)->take($number)->get();
            $datas = array();
            foreach($listens as $key => $listen) {
                $data['id'] = $listen->answer->question->id;
                $data['prize'] = $listen->answer->question->prize;
                $data['content'] = $listen->answer->question->content;
                $data['time'] = $listen->answer->question->time;
                $data['isanswered'] = $listen->answer->question->isanswered;
                $data['answer_id'] = $listen->answer->question->answer_id;
                $data['teacher_id'] = $listen->answer->question->teacher->id;
                $data['teacher_name'] = $listen->answer->question->teacher->wechat;
                $data['teacher_face'] = $listen->answer->question->teacher->face;
                $data['teacher_position'] = $listen->answer->question->teacher->position;
                $data['listen'] = $listen->answer->listen;
                $data['like'] = $listen->answer->like;
                $datas[] = $data;
            }

            //返回结果
            return Code::response(0, $datas);
        } else {
            return Code::response(100);
        }
    }
    //点赞
    public function like(){
        if(Request::has('answer_id')) {
            $id = Request::get('answer_id');
            $user_id = 33;
            $answer = Answer::find($id);
            $like = Like::where('answer_id', $id)->where('user_id', $user_id)->first();
            if(isset($answer) && !isset($like)){
                $answer->like += 1;
                $answer->like_virtual += ($answer->like_virtual == 0) ? 0 : 1;
                $answer->weight += 0.4;
                $answer->save();
                $like = new Like();
                $like->answer_id = $id;
                $like->user_id = $user_id;
                $like->time = date("Y-m-d H:i:s", time());
                $like->save();
                if($answer->like_virtual != 0) $answer->like = $answer->like_virtual;
                if($answer->listen_vitual != 0) $answer->listen = $answer->listen_virtual;
                return Code::response(0, $answer);
            } else {
                return Code::response(100, $like);
            }
        } else {
            return Code::response(100);
        }
    }
    public function cancelLike(){
        if(Request::has('answer_id')) {
            $id = Request::get('answer_id');
            $user_id = Session::get('user_id');
//            $user_id = 33;
            $answer = Answer::find($id);
            $like = Like::where('answer_id', $id)->where('user_id', $user_id)->first();
            if(isset($answer) && isset($like)){
                $answer->like -= 1;
                $answer->like_virtual -= ($answer->like_virtual == 0) ? 0 : 1;
                $answer->weight -= 0.4;
                $answer->save();
                $like->delete();
                if($answer->like_virtual != 0) $answer->like = $answer->like_virtual;
                if($answer->listen_vitual != 0) $answer->listen = $answer->listen_virtual;
                return Code::response(0, $answer);
            } else {
                return Code::response(100, '没有点过赞');
            }
        } else {
            return Code::response(100);
        }
    }
}
