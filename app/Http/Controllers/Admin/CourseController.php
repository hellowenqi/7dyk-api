<?php namespace App\Http\Controllers\Admin;

use App\Code;
use App\Http\Controllers\Controller;
use Request;
use Session;
use App\Models\Course;
use App\Models\CoursePay;
/* 课程的改查
 * 章节的增删改查
 * */
class CourseController extends Controller {
    //课程列表
    public function courseList(){
        $page = Request::get("page");
        $number = Request::get("number");
        if($page && $number){
            $datas = array();
            $data = array();
            $index = ($page - 1) * $number;
            $courses = Course::skip($index)->take($number)->get();
            foreach ($courses as $course){
                $data[] = array_merge($course->toArray(), ['paid_num' => CoursePay::where("course_id", $course->id)->count()]);
            }
            $total = Course::count();
            $datas['total'] = $total;
            $datas['data'] = $data;
            return Code::response(0, $datas);
        }else{
            return Code::response(100);
        }
    }
    //增加课程
    public function create(){
        $title = Request::get('title');
        $profile = Request::get("profile");
        $suitable = Request::get("suitable");
        if($title && $profile && $suitable){
            $model = new Course();
            $model->title = $title;
            $model->profile = $profile;
            $model->suitable = $suitable;
            $model->price_origin = floatval(Request::get('price_origin'));
            $model->price_now = floatval(Request::get('price_now'));
            $model->pic = Request::get('pic');
            $model->qrcode = Request::get('qrcode');
            if($model->save()){
                return Code::response(0, $model->toArray());
            }else{
                return Code::response(404, "保存失败" );
            }
        }else{
            return Code::response(100);
        }
    }
    //删除课程
    public function delete(){
        $id = Request::get("id");
        $model = Course::find($id);
        if($model){
            $model->delete();
            return Code::response(0);
        }else{
            return Code::response(404, 'id 不存在');
        }
    }
    //修改课程
    public function update(){
        $id = Request::get("id");
        $model = Course::find($id);
        if($model){
            if(Request::has('title')) $model->title = Request::get('title');
            if(Request::has('profile')) $model->profile = Request::get('profile');
            if(Request::has('suitable')) $model->suitable = Request::get('suitable');
            if(Request::has('pic')) $model->pic = Request::get('pic');
            if(Request::has('qrcode')) $model->qrcode = Request::get('qrcode');
            if(Request::has('price_origin')) $model->price_origin = Request::get('price_origin');
            if(Request::has('price_now')) $model->price_now = Request::get('price_now');
            if($model->save()){
                return Code::response(0, $model->toArray());
            }else{
                return Code::response(404, '错误！');
            }
        }else{
            Code::response(404, 'id错误');
        }
    }
    //查看课程
    public function info(){
        $id = Request::get("id");
        $model = Course::find($id);
        if($model){
            return Code::response(0,
                array_merge($model->toArray(), ['pay_num' => CoursePay::where('course_id', $id)->count()])
                );;
        }else{
            return Code::response(404, 'id错误');
        }
    }
    //课程支付情况
    public function affordList() {
        $page = Request::get("page");
        $number = Request::get("number");
        if($page && $number){
            $index = ($page - 1) * $number;
            $pays = CoursePay::with('user')->skip($index)->take($number)->get();
            $datas = array();
            $data = array();
            foreach ($pays as $item){
                $data[] = array(
                    'id'            => $item->user->id,
                    'head'          => $item->user->face,
                    'nickname'      => $item->user->wechat,
                    'openid'        => $item->user->openid,
                    'order'         => $item->order,
                    'time'          => $item->time,
                    'money'         => $item->money
                );
            }
            $datas['page'] = $page;
            $datas['number'] = $number;
            $datas['total'] = CoursePay::count();
            $datas['datas'] = $data;
            return Code::response(0, $datas);
        }else{
            return Code::response(100);
        }
    }
    public function chapterInfo(){
        $id = Request::get("id") || 1;
        echo $id;
    }
}