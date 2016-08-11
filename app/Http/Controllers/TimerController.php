<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionExpired;
use Illuminate\Http\Request;
use App\Wechat;
use Curl\Curl;
class TimerController extends Controller {

	/**
	 * 将过期的问题移动到新表中 检查过期的题目，退款给用户，发送退款通知给老师、学生
	 */
	public function checkExpired(){
		Question::where('isanswered', 0)->chunk(100, function($questions){
			foreach ($questions as $question){
				if(time() - strtotime($question->time) > 86400){
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
				}
				exit;
			}
		});
	}
	public function getUserInfo(){

		$wechat = new Wechat();
		$wechat->getToken();
//		$user = $wechat->getUserinfo('0P-ewJsLELyhduFbXcSZbgN6w1T_T7ihgBEDPKykp4pci7sUlF8z2LglJPlmC8owZGmYkwGX6qcxnEML_6ZvfhLKiVcxiXjhcketWUW9z1ufE-VVpJeQLZmg9_nEGL_kDQThACAUHK', 'on7OgwuImaOsx_i0mUkt9aW_nMcI');
//		var_dump($user);
		
	}

}
