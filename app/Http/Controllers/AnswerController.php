<?php namespace App\Http\Controllers;

use Request;
use Cache;
use App\Code;
use App\Wechat;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Teacher;
use App\Models\Listen;
use App\Wechat\WxPayConfig;
use App\Wechat\WxPayApi;
use App\Wechat\WxPayNotify;
use App\Wechat\JsApiPay;
use App\Wechat\WxPayUnifiedOrder;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Format\Audio\Wav;
use Qiniu\Storage\UploadManager;

class AnswerController extends Controller {

    public function __construct() {
        return;
    }

    public function listen() {
        if(Request::has('answer_id')) {
            //$user_id = Session::get('user_id');
            $user_id = 1;
            $answer_id = Request::get('answer_id');
            $listen = Listen::with('answer')->where('user_id', $user_id)->where('answer_id', $answer_id)->first();
            if(isset($listen)) {
                return Code::response(0);
                $name = $listen->answer->audio;
                $mp3 = file_get_contents("audio/$name.mp3");
                header("Content-type:audio/mp3");
                echo $mp3;
                return;
            } else {
                $time = time();
                $name = md5("$user_id$time");
                $tools = new JsApiPay();
                date_default_timezone_set('PRC');
                $input = new WxPayUnifiedOrder();
                $input->SetBody("body");
                $input->SetAttach($name);
                $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
                $input->SetTotal_fee(1);
                $input->SetTime_start(date("YmdHis", time()));
                $input->SetTime_expire(date("YmdHis", time() + 600));
                $input->SetGoods_tag("tag");
                $input->SetNotify_url("http://api.7dyk.com/api/v1/answer/notify");
                $input->SetTrade_type("JSAPI");
                $input->SetOpenid("on7Ogwj04PIfSCxa2ypeMrGuvAGU");
                $order = WxPayApi::unifiedOrder($input);
                $jsApiParameters = json_decode($tools->GetJsApiParameters($order));

                $listen = new Question();
                $listen->answer_id = $answer_id;
                $listen->user_id = $user_id;
                $listen->time = date("Y-m-d H:i:s", time());
                Cache::put($name, $listen, 10);
                return Code::response(0, $jsApiParameters);
            }
        } else {
            return Code::response(100);
        }
    }

    public function answer() {
        if(Request::has('server_id') && Request::has('question_id')) {
            $user_id = 1;
            $teacher = Teacher::where('user_id', $user_id)->first();
            if(!isset($teacher)) {
                return Code::response(303);
            }
            //取得用户
            
            $media_id = Request::get('server_id');
            $question_id = Request::get('question_id');

            $question = Question::with('answer')->where('id', $question_id)->first();
            if(!isset($question)) {
                return Code::response(201);
            }
            if($question->isanswered == 1) {
                return Code::response(301, array('url' => $question->answer->audio));
            } else {
                $answer = new Answer();
                $answer->prize = $question->prize;
                $answer->time = date("Y-m-d H:i:s", time());
                $answer->listen = 0;
                $answer->dislike = 0;
                $answer->question_id = $question->id;
                $answer->question_user_id = $question->question_user_id;
                $answer->answer_user_id = $user_id;
                $answer->save();
                $question->isanswered = 1;
                $question->answer_id = $answer->id;
                $question->answer_user_id = $user_id;
                $question->save();
                $teacher->answernum++;
                $teacher->income+=$question->prize;
                $teacher->save();
            }
            //判断问题是否存在且未被回答，并创建答案

            $wechat = new Wechat();
            $audio = $wechat->getFile($media_id);
            $msg = json_decode($audio);
            if(isset($obj->errcode)) {
                return Code::response(302);
            }
            $time = time();
            $name = md5("$user_id$time");
            $amr_result = file_put_contents("audio/$name.amr",$audio);
            if($amr_result === FALSE) {
                return Code::response(303);
            }
            $ffmpeg = FFMpeg::create(array(
                'ffmpeg.binaries'  => '/usr/local/bin/ffmpeg',
                'ffprobe.binaries' => '/usr/local/bin/ffprobe',
                'timeout'          => 3600, // The timeout for the underlying process
                'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use
            ));
            $audio = $ffmpeg->open("audio/$name.amr");
            $format = new Mp3();
            $format->setAudioChannels(1);
            $audio->filters()->resample('8000');
            $audio->save($format, "audio/$name.mp3");
            unlink("audio/$name.amr");
            $answer->audio = $name;
            $answer->save();
            return Code::response(0, array('answer' => $answer));
        } else {
            return Code::response(100);
        }
    }

    public function prepay() {
        $question = new Question();
        $question->prize = 1;
        $question->content = "123";
        $question->answer_user_id = 1;
        $question->question_user_id = 1;
        $question->isanswered = 0;
        $question->weight = 0;
        $question->answer_id = 0;
        $question->time = date("Y-m-d H:i:s", time());
        Cache::put("test", $question, 10);
        $a = Cache::get("test");
        $class = get_class($a);
        dd($class);
        /*$mp3 = file_get_contents("audio/ddd7c2f15f2ee07d47df212091903c7f.mp3");
        header("Content-type:audio/mp3");
        echo $mp3;*/
    }

    public function notify() {
        $notify = new WxPayNotify();
        $notify->Handle(false);
    }
}
