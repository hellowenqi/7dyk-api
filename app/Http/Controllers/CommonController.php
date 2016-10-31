<?php namespace App\Http\Controllers;

use App\Code;
use App\Models\Chapter;
use Request;
use Session;
use App\Models\CoursePay;
class CommonController extends Controller{
    public function audio($name){
        //鉴权
        $flag = false;
        $user_id = Session::get('user_id');
        $adminId = Session::get("adminId");
        if($user_id){  //手机端用户
            $model = Chapter::where("audio", $name)->one();
            if($model && $user_id = CoursePay::where('course_id', $model->course_id)->where("user_id", $user_id)->one()){
                $flag = true;
            }
        }elseif ($adminId){
            $flag = true;
        }
        if($flag){//有权限
            $mp3 = file_get_contents(storage_path() . DIRECTORY_SEPARATOR . "audio" . DIRECTORY_SEPARATOR . $name);
            header("Content-type:audio/mp3");
            echo $mp3;
            return;
        }else{ //无权限
            return Code::response(404, "没有权限");
        }
    }
}

