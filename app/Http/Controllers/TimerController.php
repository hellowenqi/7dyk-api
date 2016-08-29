<?php namespace App\Http\Controllers;
use App\Code;
use Crypt;
use Config;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionExpired;
use Illuminate\Http\Request;
use App\Wechat;
use Curl\Curl;
use App\Models\Admin;
class TimerController extends Controller {

	/**
	 * 将过期的问题移动到新表中 检查过期的题目，退款给用户，发送退款通知给老师、学生
	 */
	public function checkExpired(){
		Question::where('isanswered', 0)->chunk(100, function($questions){
			foreach ($questions as $question){
				$timespan = time() - strtotime($question->time);
				if($question->answer_user_id == 33){
					var_dump($question);
					echo $timespan;
				}
				//退款
				if($timespan > 86400){
					continue;
					//超时, 移动问题
					$model = new QuestionExpired();
					$model->prize = $question->prize;
					$model->content = $question->content;
					$model->time = $question->time;
					$model->question_user_id = $question->question_user_id;
					$model->answer_user_id = $question->answer_user_id;
					if($model->save()){
						$question->delete();
					}
					//退款
					//发送通知给用户
					$question_union = Question::find($question->id)->with('user')->with('teachr');
				}elseif($timespan > 43200 && $timespan <= 43800){
					//即将过期提醒
					$openid = $question->teacher->openid;
					$url = Config::get('urls.appurl') . 'account/AskedMeList';
					$count = Question::where('answer_user_id', $question->answer_user_id)
						->where('isanswered', 0)->count();
					$exipireTime = date('Y-m-d H:i:s', strtotime($question->time) + 86400);
					$message = [
						'first' => "你还有{$count}个提问未回答，超过24小时问题将过期哦",
						'keyword1' => '暂未回答的提问',
						'keyword2' => $exipireTime,
						'remark' => '快去回答吧'
					];
					$wechat = new Wechat();
					$wechat->sendMessage($openid, $message, $url, 2);
				}
			}
		});
	}
	public function getUserInfo(){
		$wechat = new Wechat();
		$return = $wechat->sendMessage('on7OgwizVILjdisVtqsEhkU3WRRE', [
			'first' => "武文齐很喜欢你，想让你解答一个问题",
			'keyword1' => 'hahha',
			'keyword2' => '公开',
			'keyword3' => '201',
			'remark'   => "快去回答这个价值￥{123}:00的问题吧"

		], Config::get('urls.appurl'). 'answer/109', 1);
		var_dump($return);
	}
	public function getToken(){
		$wechat = new Wechat();
		return Code::response(0, $wechat->getToken());
	}
	//生成随机字符串
	public function createNonceStr($length = 32) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

}
