<?php namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Answer;
use App\Models\Teacher;
use App\Models\User;
use App\Wechat;
use Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;


class QuestionController extends Controller {

    public function __construct() {
        return;
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
        } else if($errCode == 201) {
            $json = array(
                'errCode'   =>  201,
                'msg'       =>  '没有查询到问题',
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
    public function test() {
        $wechat = new Wechat();
        $signPackage = $wechat->getSignPackage();
        return $this->response(0, $signPackage);
    }

    public function getTopic() {
        if(Request::has('page') && Request::has('number')) {
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page-1)*$number;
            $questions = Question::where('isanswered', 1)->
                with('answer')->with('teacher')->orderBy('weight', 'desc')->skip($index)->take($number)->get();
            $datas = array();
            foreach($questions as $key => $question) {
                $teacher = Teacher::where('user_id', $question->teacher->id)->first();
                if(isset($teacher)) {
                    $prize = $teacher->prize;
                }
                $data = array(
                    'question_id'           =>  $question->id,
                    'question_content'      =>  $question->content,
                    'question_prize'        =>  $question->prize,
                    'teacher_id'             =>  $question->teacher->id,
                    'teacher_name'          =>  $question->teacher->wechat,
                    'teacher_title'         =>  $question->teacher->title,
                    'teacher_face'          =>  $question->teacher->face,
                    'teacher_prize'         =>  $prize,
                    'answer_listen'         =>  $question->answer->listen,
                    'answer_dislike'        =>  $question->answer->dislike,
                    'answer_audio'          =>  $question->answer->audio,
                );
                $datas[] = $data;
            }
            return $this->response(0, $datas);
        } else {
            return $this->response(100);
        }
    }

    public function getQuestion() {
        if(Request::has('id')) {
            $question_id = Request::get('id');
            $question = Question::with('user')->with('teacher')->where('isanswered', 1)->where('id', $question_id)->first();
            if(isset($question)) {
                $teacher = Teacher::where('user_id', $question->teacher->id)->first();
                $data = array(
                    'question_id'           =>  $question->id,
                    'question_content'      =>  $question->content,
                    'question_prize'        =>  $question->prize,
                    'user_id'               =>  $question->user->id,
                    'user_face'             =>  $question->user->face,
                    'teacher_id'            =>  $question->teacher->id,
                    'teacher_name'          =>  $question->teacher->wechat,
                    'teacher_title'         =>  $question->teacher->title,
                    'teacher_face'          =>  $question->teacher->face,
                    'teacher_prize'         =>  $teacher->prize,
                    'answer_listen'         =>  $question->answer->listen,
                    'answer_dislike'        =>  $question->answer->dislike,
                    'answer_audio'          =>  $question->answer->audio,
                );
                return $this->response(0, $data);
            } else {
                return $this->response(201);
            }
        } else {
            return $this->response(100);
        }
    }

    public function addQuestion()
    {

        $data['prize'] = Request::input('prize');//接值
        $data['content'] = Request::input('content');
        $data['time'] = Request::input('time');
        $data['isanswered'] = Request::input('isanswered');
        $data['weight'] = Request::input('weight');
        $data['answer_id'] =  Request::input('answer_id');
        $data['answer_user_id'] =  Request::input('answer_user_id');
        $data['question_user_id'] =  Request::input('question_user_id');
        $re = DB::table('question')->insert($data);
        //返回结果
        if($re)
        {
            return $this->response(0,$data);
        }else
        {
            return $this->response(100);
        }
    }
    //我问的问题
    public function myQuestion()
    {
        if(Request::has('page') && Request::has('number')) {
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page - 1) * $number;

            $user_id = 1;
            //$user_id = session("user_id");//接值
            $arr = DB::table('question')->where('question_user_id', $user_id)->skip($index)->take($number)->get();//打印数组
            $myquestion = json_encode($arr);//josn格式
            //返回结果
            if ($myquestion) {
                return $this->response(0);
                {
                    return $this->response(100);
                }

            }
        }
    }
    //问我的问题
    public function myAnswer()
    {
        if(Request::has('page') && Request::has('number')) {
            $page = Request::get('page');
            $number = Request::get('number');
            $index = ($page - 1) * $number;

            $user_id = 1;
            //$user_id = session("user_id");//接值

            $arr = DB::table('question')->where('question_user_id', $user_id)->skip($index)->take($number)->get();//打印数组

            //返回结果
            if ($arr) {
                echo json_encode($arr);//josn格式
                return $this->response(0);
            } else {
                return $this->response(100);
            }
        }
    }

    //听过回答的人数
    public  function myListen()
    {
        $user_id = session("user_id");
        $where['answer_user_id']=$user_id;
        $where['listen']=1;

        $listen_nums = DB::table('answer')->where($where)->sum('listen');
        if(empty($listen))
        {
            return $this->response(0);
        }else
        {
            return $this->response(100);
        }
    }







}
