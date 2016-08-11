<?php namespace App\Http\Controllers\Admin;



use App\Http\Requests;
use App\Http\Controllers\Controller;
//use App\Models\Answer;
use App\Models\User;
use App\Code;
use App\Models\Invite;
use Request;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{

    public function generateInvite(){
        $users = User::where('isteacher', 0)->get();
        foreach ($users as $user){
            $model = new Invite();
            $model->user_id = $user->id;
            $model->wechat = $user->wechat;
            $model->invite = $this->createNonceStr();
            $model->save();
        }
    }
    public function generateAnonymousInvite($length = 200){
        for($i = 0; $i < $length; $i++){
            $model = new Invite();
            $model->invite = $this->createNonceStr();
            if(!$model->save()) $i--;
        }
    }
    //生成随机字符串
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}
