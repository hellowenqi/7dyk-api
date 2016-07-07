<?php
namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Answer;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Listen;
use App\Code;
use App\Wechat;
use App\Wechat\WxPayConfig;
use App\Wechat\WxPayApi;
use App\Wechat\JsApiPay;
use App\Wechat\WxPayUnifiedOrder;
use Request;
use Session;
use Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class QuestionController extends Controller
{

    public function __construct()
    {
        return;
    }

    public function test() {
        $wechat = new Wechat();
        $signPackage = $wechat->getSignPackage();
        return Code::response(0, $signPackage);
    }

    public function getTopic()
    {
        if (Request::has('page') && Request::has('number')) {
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page - 1) * $number;
            $questions = Question::where('isanswered', 1)->
            with('answer')->with('teacher')->orderBy('weight', 'desc')->skip($index)->take($number)->get();
            $datas = array();
            foreach ($questions as $key => $question) {
                $teacher = Teacher::where('user_id', $question->teacher->id)->first();
                if (isset($teacher)) {
                    $prize = $teacher->prize;
                }
                $data = array(
                    'question_id'           =>  $question->id,
                    'question_content'      =>  $question->content,
                    'question_prize'        =>  $question->prize,
                    'teacher_id'            =>  $question->teacher->id,
                    'teacher_name'          =>  $question->teacher->wechat,
                    'teacher_company'       =>  $question->teacher->company,
                    'teacher_position'      =>  $question->teacher->position,
                    'teacher_experience'    =>  $question->teacher->experience,
                    'teacher_face'          =>  $question->teacher->face,
                    'teacher_prize'         =>  $prize,
                    'answer_listen'         =>  $question->answer->listen,
                    'answer_dislike'        =>  $question->answer->dislike,
                    'answer_audio'          =>  $question->answer->audio,
                );
                $datas[] = $data;
            }
            return Code::response(0, $datas);
        } else {
            return Code::response(100);
        }
    }

    public function getQuestion()
    {
        if (Request::has('id')) {
            $question_id = Request::get('id');
            $question = Question::with('user')->with('teacher.teacher')->where('id', $question_id)->first();
            if (isset($question)) {
                if($question->isanswered == 1) {
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
                        'answer_listen' => $question->answer->listen,
                        'answer_dislike' => $question->answer->dislike,
                        'answer_audio' => $question->answer->audio,
                        'answer_time' => $question->answer->time,
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
            $data['weight'] = 0;
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
            //$user_id = Session::get('user_id');
            $user_id = 1;
            $time = time();
            $name = md5("$user_id$time");
            $teacher = Teacher::where('user_id', Request::get('answer_user_id'))->first();
            if(!isset($teacher)) {
                return Code::response(103);
            }

            //$prize = (int)$teacher->prize;
            $prize = 1;
            $content = Request::input('content');
            $answer_user_id = Request::input('answer_user_id');

            $tools = new JsApiPay();

            $input = new WxPayUnifiedOrder();
            $input->SetBody("body");
            $input->SetAttach($name);
            $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
            $input->SetTotal_fee($prize);
            date_default_timezone_set('PRC');
            $input->SetTime_start(date("YmdHis", time()));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag("tag");
            $input->SetNotify_url("http://api.7dyk.com/api/v1/answer/notify");
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid("on7Ogwj04PIfSCxa2ypeMrGuvAGU");
            $order = WxPayApi::unifiedOrder($input);
            $jsApiParameters = json_decode($tools->GetJsApiParameters($order));

            $question = new Question();
            $question->prize = $prize;
            $question->content = $content;
            $question->answer_user_id = $answer_user_id;
            $question->question_user_id = $user_id;
            $question->isanswered = 0;
            $question->weight = 0;
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
            //$user_id = Session::get('user_id');
            $user_id = 1;

            $arr = DB::table('question')->where('question_user_id', $user_id)->orderBy('time', 'desc')->skip($index)->take($number)->get();
            foreach($arr as $key => $data) {
                if($data->isanswered == 1) {
                    $answer = Answer::where('id', $data->answer_id)->first();
                    $arr[$key]->listen = $answer->listen;
                    $arr[$key]->like = $answer->like;
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
            //$user_id = Session::get('user_id');
            $user_id = 1;

            $arr = DB::table('question')->where('answer_user_id', $user_id)->skip($index)->take($number)->get();//打印数组
            foreach($arr as $key => $data) {
                if($data->isanswered == 1) {
                    $answer = Answer::where('id', $data->answer_id)->first();
                    $arr[$key]->listen = $answer->listen;
                    $arr[$key]->like = $answer->like;
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

    //听过的问提
    public function myListen()
    {
        if (Request::has('page') && Request::has('number')) {
            //$user_id = Session::get('user_id');
            $user_id = 1;
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page - 1) * $number;

            $listens = Listen::with("answer.question")->with("user")->where("user_id", $user_id)->skip($index)->take($number)->get();
            $datas = array();
            foreach($listens as $key => $listen) {
                $data['id'] = $listen->answer->question->id;
                $data['prize'] = $listen->answer->question->prize;
                $data['content'] = $listen->answer->question->content;
                $data['time'] = $listen->answer->question->time;
                $data['isanswered'] = $listen->answer->question->isanswered;
                $data['answer_id'] = $listen->answer->question->answer_id;
                $data['user_face'] = $listen->user->face;
                $data['listen'] = $listen->answer->listen;
                $data['dislike'] = $listen->answer->dislike;
                $datas[] = $data;
            }

            //返回结果
            return Code::response(0, $datas);
        } else {
            return Code::response(100);
        }
    }
    //赞的人数
    public function like(){
        $answer_id = session("id");
        $like_answer = DB::update("update answer set `like`=`like`+1 where id='answer_id'");
        //echo $like_answer;die;
        if (empty($like_answer)) {
            return Code::response(0);
        } else{
            return Code::response(100);
        }

    }
    //不喜欢这个回答的人数
    public function dislike(){
        $answer_id = session("id");
        $like_answer = DB::update("update answer set `dislike`=`dislike`+1 where id=1");
        //echo $like_answer;die;
        if (empty($like_answer)) {
            return Code::response(0);
        } else{
            return Code::response(100);
        }
    }

    //计算问题的权重
    public function weight(){
        $question_id = Request::input('question_id');
        $res1 = DB::select("select * from question left join answer on question.id=answer.question_id where question.id=3");

        $quanzhong = 0.6 * $res1[0]->listen + 0.4 * $res1[0]->like;
        $res = DB::update("update question set weight=weight+$quanzhong where id=3");

        if (empty($res)) {
            return Code::response(0);
        } else {
            return Code::response(100);
        }

    }

    //查询当前问题的权重排序
    public function teacher_question()
    {
        $res = DB::table('question')->orderBy('weight','desc')->get();
        if (empty($res)) {
            return Code::response(0);
        } else {
            return Code::response(100);
        }
    }

}
