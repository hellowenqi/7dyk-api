<?php namespace App\Http\Controllers;

use App\Models\User;
use Request;
use Cache;
use Session;
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
use Config;
use Log;

class AnswerController extends Controller {

    public function __construct() {
        return;
    }

    //收听问题
    public function listen() {
        if(Request::has('answer_id')) {
            $user_id = Session::get('user_id');
            $answer_id = Request::get('answer_id');
            $answer = Answer::with('question')->find($answer_id);
            //提问者返回结果
            if(isset($answer) && ($answer->answer_user_id == $user_id || $answer->question_user_id == $user_id)) {
                $name = $answer->audio;
                return Code::response(0, array(
                    'url' => "http://h5app.7dyk.com/ama/api/public/api/v1/answer/audio?name=$name&answer_id=$answer_id",
                    'question_id' => $answer->question->id,
                ));
            }
            $listen = Listen::where('user_id', $user_id)->where('answer_id', $answer_id)->first();
            //听过的返回
            if(isset($listen)) {
                $name = $answer->audio;
                return Code::response(0, array(
                    'url' => "http://h5app.7dyk.com/ama/api/public/api/v1/answer/audio?name=$name&answer_id=$answer_id",
                    'question_id' => $listen->answer->question->id,
                ));
            } else {
                $time = time();
                $name = md5("$user_id$time");
                $tools = new JsApiPay();
                $openid = Session::get('openid');
                date_default_timezone_set('PRC');
                $input = new WxPayUnifiedOrder();
                $input->SetBody("body");
                $input->SetAttach($name);
                $input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
                $prize = Config::get('app.ENV') ? 1 : 100;

                $input->SetTotal_fee(Config::get('app.ENV') ? 1 : 100);
                Log::info('$prize' . $prize);
                Log::info('config ' . Config::get('app.ENV'));
                Log::info('totoalFee:set' . (Config::get('app.ENV') ? 1 : 100));
                Log::info('totoalFee:' . $input->GetTotal_fee());
                $input->SetTime_start(date("YmdHis", time()));
                $input->SetTime_expire(date("YmdHis", time() + 600));
                $input->SetGoods_tag("tag");
                $input->SetNotify_url("http://h5app.7dyk.com/ama/api/public/api/v1/notify");
                $input->SetTrade_type("JSAPI");
                $input->SetOpenid($openid);
                $order = WxPayApi::unifiedOrder($input);
                $jsApiParameters = json_decode($tools->GetJsApiParameters($order));

                $listen = new Listen();
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
            $user_id = Session::get('user_id');
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
                $answer->listen = 1;
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
            if(isset($msg->errcode)) {
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
            //发送消息通知
            $openid = $question->user->openid;
            $name = $teacher->user->wechat;
            $time = date('Y-m-d H:i:s');
            $wechat->sendMessage($openid,[
                'first' => "{$name}已经解决了你的问题",
                'keyword1' => $name,
                'keyword2' => $time,
                'keyword3' => '语音',
                'remark' => '快去看看吧！',
            ], Config::get('urls.appurl') . "question/$question_id", 3);
            return Code::response(0, array('answer' => $answer));
        } else {
            return Code::response(100);
        }
    }

    public function audio() {
        if(Request::has('name') && Request::has('answer_id')) {
            //$user_id = Session::get('user_id');
            $name = Request::get('name');
            $answer_id = Request::get('answer_id');
            $answer = Answer::find($answer_id);
//            $listen = Listen::with('answer')/**->where('user_id', $user_id)**/->where('answer_id', $answer_id)->first();
//            Log::info(json_encode($listen));
            if($name == $answer->audio) {
                $mp3 = file_get_contents("audio/$name.mp3");
                header("Content-type:audio/mp3");
                echo $mp3;
                return;
            } else {
                return Code::response(100);
            }
        } else {
            return Code::response(100);
        }
    }

    public function notify() {
        $notify = new WxPayNotify();
        $notify->Handle(false);
    }
}
