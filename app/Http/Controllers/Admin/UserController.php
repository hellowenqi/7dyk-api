<?php namespace App\Http\Controllers\Admin;



use App\Http\Requests;
use App\Http\Controllers\Controller;
//use App\Models\Answer;
use App\Models\Listen;
use App\Models\Question;
use App\Models\QuestionExpired;
use App\Models\User;
use App\Code;
use App\Models\Invite;
use Request;
use Illuminate\Support\Facades\DB;


class UserController extends Controller
{

    public function getList(){
        if(Request::has('number')  && Request::has('page')){
            $number = intval(Request::get('number'));
            $page = intval(Request::get('page'));
            $index = $number * ($page - 1);
            $query = new User();
            $search = Request::get('search');
            if($search){
                $query = User::where('wechat', 'like', "%$search%");
            }
            $total = $query->count();
            $users = $query->skip($index)->take($number)->get();
            $datas = array();
            $data = array();
            foreach ($users as $user){
                $data[] = array(
                    'id'            => $user->id,
                    'head'          => $user->face,
                    'nickname'      => $user->wechat,
                    'company'       => $user->company,
                    'position'      => $user->position,
                    'experience'    => $user->experience,
                    'introduction'  => $user->introduction,
                    'register_time' => $user->regist_time,
                    'openid'        => $user->openid,
                    'is_teacher'    => $user->isteacher,
                );
            }
            $datas['page'] = $page;
            $datas['number'] = $number;
            $datas['total'] = $total;
            $datas['datas'] = $data;
            return Code::response(0, $datas);

        }else{
            return Code::response(100);
        }

    }
    public function userInfo(){
        $id = Request::get('id');
        if($id){
            $model = User::find($id);
            if(!$model) return Code::response(102);
            $listen_num = Listen::where('user_id', $id)->count();
            $ask_num_answered = Question::where('question_user_id', $id)->count();
            $ask_num_expired = QuestionExpired::where('question_user_id', $id)->count();
            $data = array(
                'id'                => $model->id,
                'wechat'            => $model->wechat,
                'company'           => $model->company,
                'position'          => $model->position,
                'introduction'      => $model->introduction,
                'register_time'     => $model->regist_time,
                'openid'            => $model->openid,
                'isteacher'         => $model->isteacher,
                'listen_num'        => $model->listen_num,
                'ask_num_answered'  => $ask_num_answered,
                'ask_num_expired'   => $ask_num_expired,
                'listen_num'        => $listen_num
            );
            return Code::response(0, $data);
        }else{
            return Code::response(100);
        }
    }
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
