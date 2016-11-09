<?php namespace App\Http\Controllers;

use App\Code;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\Mark;
use App\Models\User;
use App\Models\View;
use Request;
use Session;
use Cache;
use Config;
use App\Models\CoursePay;
use App\Wechat\WxPayConfig;
use App\Wechat\WxPayApi;
use App\Wechat\JsApiPay;
use App\Wechat\WxPayUnifiedOrder;
class CourseController extends Controller{
    //用户详情
    public function userInfo(){
        $user_id = Session::get("user_id") | 30;;
        $isPaid = CoursePay::select('course_id')->where("user_id", $user_id)->get()->toArray();
        $model = User::find($user_id)->toArray();
        return Code::response(0, array_merge($model, ["isPaid" => $isPaid]));
    }
    //课程详情
    public function info($id){
        $model = Course::find($id);
        if($model){
            return Code::response(0, $model->toArray());
        }else{
            return Code::response(404, "课程不存在");
        }
    }
    //章节列表
    public function chapterList(){
        $course_id = Request::get("course_id");
        $chapters = Chapter::where("course_id", $course_id)->orderBy("time", "asc")->get()->toArray();
        $views = View::where("user_id", Session::get('user_id'))->get()->toArray();
        $marks = Mark::where("user_id", Session::get("user_id"))->get()->toArray();
        foreach ($chapters as &$chapter){
            (in_array($chapter['id'], $views)) ? $chapter['isViewed'] = true : $chapter['isViewed'] = false;
            (in_array($chapter['id'], $marks)) ? $chapter['isMarked'] = true : $chapter['isMarked'] = false;
        }
        unset($chapter);
        return Code::response(0, $chapters);
    }
    //章节详情
    //测试不存在，鉴权，查看过加一
    public function chapterInfo($id){
        $model = Chapter::find($id);
        if($model){
            //鉴权
            $course_id = $model->course->id;
            $user_id =  Session::get("user_id") | 30;
            if($model->is_free == 1 || CoursePay::where("course_id", $course_id)->where("user_id", $user_id)->first()){
                $view = View::where("user_id", $user_id)->where("chapter_id", $id)->first();
                $mark = Mark::where("user_id", $user_id)->where("chapter_id", $id)->first();
                $marks_model = Mark::with("user")->where("chapter_id", $id)->take(20)->get();
                $marks = [];
                foreach ($marks_model as $item){
                    $marks[] = [
                        'face' => $item->user->face,
                        'user_id' => $item->user->id,
                        'wechat'  => $item->user->wechat,
                        'time' => $item->time,
                    ];
                }
                $marked = $viewed = false;
                if($view == null){
                    $view = new View();
                    $view->time = time();
                    $view->user_id = $user_id;
                    $view->chapter_id = $id;
                    $view->save();
                    $model->view_num += 1;
                    $model->save();
                }else{
                    $viewed = true;
                }
                if($mark) $marked = true;
                $data = $model->toArray();
                $data['audio'] = Config::get("urls.appurl") . 'audio/' . $data['audio'];
                return Code::response(0, array_merge($data, ['viewed'=>$viewed, 'marked'=>$marked, "marks" =>$marks]));
            }else{
                return Code::response(404, "没有购买课程，不能查看");
            }
        }else{
            return Code::response(404, "章节不存在");
        }
    }
    //打卡
    public function chapterMark($id){
        $chapter = Chapter::find($id);
        if($chapter){
            $user_id = Session::get("user_id") | 30;
            $mark = Mark::where("user_id", $user_id)->where("chapter_id", $id)->first();
            if($mark == null){
                $mark = new Mark();
                $mark->time = time();
                $mark->user_id = $user_id;
                $mark->chapter_id = $id;
                $mark->save();
                $chapter->mark_num += 1;
                $chapter->save();
            }
            return Code::response(0);
        }else{
            return Code::response(404, "章节不存在");
        }
    }
    //支付
    public function pay(){
        $course_id = Request::get("course_id");
        $model = Course::find($course_id);
        if($model){
            $user_id = Session::get('user_id');
            $trade_no = WxPayConfig::MCHID.date("YmdHis");
            $time = time();
            date_default_timezone_set('PRC');
            $name = md5("{$user_id}{$time}");
            $price = (int)($model->price_now * 100);
            $tools = new JsApiPay();
            $openid = Session::get('openid');
//            $openid = "on7OgwizVILjdisVtqsEhkU3WRRE";
            $input = new WxPayUnifiedOrder();
            $input->SetBody("购买课程【".$model->title."】");
            $input->SetAttach($name);
            $input->SetOut_trade_no($trade_no);
            $input->SetTotal_fee($price);
            $input->SetTime_start(date("YmdHis", time()));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag("tag");
            $input->SetNotify_url("http://h5app.7dyk.com/ama/api/public/api/v1/notify");
            $input->SetTrade_type("JSAPI");
            $input->SetOpenid($openid);
            $order = WxPayApi::unifiedOrder($input);
            $jsApiParameters = json_decode($tools->GetJsApiParameters($order));
            $coursePay = new CoursePay();
            $coursePay->user_id = $user_id;
            $coursePay->course_id = $course_id;
            $coursePay->time = time();
            $coursePay->price = $price / 100;
            $coursePay->order = $trade_no;
            Cache::put($name, $coursePay, 10);
            return Code::response(0, $jsApiParameters);
        }else{
            return Code::response(404, "课程不存在");
        }
    }
}