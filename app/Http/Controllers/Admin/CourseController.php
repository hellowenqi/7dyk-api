<?php namespace App\Http\Controllers\Admin;

use App\Code;
use App\Http\Controllers\Controller;
use League\Flysystem\Directory;
use Request;
use Session;
use Input;
use Config;
use Cache;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CoursePay;
use App\Models\Audio;
use App\Models\Picture;
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
            return Code::response(404, 'id错误');
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
            $pays = CoursePay::with('user')->take($number)->skip($index)->get();
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
    //章节列表
    public function chapter(){
        $page = Request::get("page");
        $number = Request::get("number");
        $id = Request::get("course_id");
        if($page && $number && $id){
            $datas = array();
            $index = ($page - 1) * $number;
            $courses = Chapter::select('id', 'title', 'pic', 'time', 'view_num', 'mark_num', 'course_id', 'is_free')->where('course_id', $id)->orderBy("time", 'desc')->skip($index)->take($number)->get();
            $total = Chapter::count();
            $datas['total'] = $total;
            $datas['data'] = $courses;
            return Code::response(0, $datas);
        }else{
            return Code::response(100);
        }
    }
    //增加章节
    public function chapterCreate(){
        $id = Request::get("course_id");
        if(Course::find($id)){
            $model = new Chapter();
            $model->title = Request::get('title');
            $model->content = Request::get('content');
            $model->pic = Request::get('pic');
            $model->course_id = $id;
            $model->time = intval(Request::get('time'));
            $model->audio = Request::get("audio");
            $model->is_free = intval(Request::get("is_free"));
            if($model->title == ''
                || $model->content == ""
                || $model->pic==""
                || $model->time==0
                || $model->audio == ""
                || $model->is_free !== 1 && $model->is_free !== 2
            ){
                return Code::response(100);
            }
            $arrs = explode('/', $model->audio);
            $path = $arrs[count($arrs) - 1];
            $model->audio = $path;
            if($model->save()){
                $audio_model = Audio::where("path", $path)->first();
                if($audio_model) $audio_model->delete();
                $model->audio = Config::get("urls.audiourl") . $model->audio;
                return Code::response(0, $model->toArray());
            }else{
                return Code::response(404, "保存失败");
            }
        }else{
            return Code::response(404, "course_id $id 不存在");
        }
    }
    //删除章节
    public function chapterDelete($id){
        $model = Chapter::find($id);
        if($model){
            if(unlink(storage_path() . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . $model->audio)){
                $model->delete();
                return Code::response(0);
            }else{
                return Code::response(404, '删除失败');
            };
        }else{
            return Code::response(404, "id 不存在");
        }
    }
    //修改章节
    public function chapterUpdate($id){
        $model = Chapter::find($id);
        if($model){
            if(Request::has("title")) $model->title = Request::get('title');
            if(Request::has("content")) $model->content = Request::get('content');
            if(Request::has("pic")) $model->pic = Request::get('pic');
            if(Request::has("time")) $model->time = Request::get('time');
            if(Request::has("is_free")){
                $is_free = intval(Request::get("is_free"));
                if($is_free === 1|| $is_free === 2){
                    $model->is_free = $is_free;
                }
            }
            if(Request::has("audio")){
                $audio = Request::get("audio");
                $arrs = explode('/', $audio);
                $path = $arrs[count($arrs) - 1];
                if($path != $model->audio){
                    file_exists(storage_path() . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . $model->audio) && unlink(storage_path() . DIRECTORY_SEPARATOR . 'audio' . DIRECTORY_SEPARATOR . $model->audio);
                    $model->audio = $path;
                    $audio_model = Audio::where("path", $path)->first();
                    if($audio_model) $audio_model->delete();
                }
            }
            $model->save();
            $model->audio = Config::get("urls.audiourl") . $model->audio;
            return Code::response(0, $model->toArray());
        }else{
            return Code::response(404, "id 不存在");
        }
    }
    //课程详情
    public function chapterInfo($id){
        $model = Chapter::find($id);
        if($model){
            $model->audio = Config::get("urls.audiourl") . $model->audio;
            return Code::response(0 , $model->toArray());
        }else{
            return Code::response(404, "id 不存在");
        }
    }
    public function uploadAudio(){
        $file = Request::file("audio");
//        var_dump($_POST);
        if(null != $file){
            $name = uniqid();
            $extension = $file->getClientOriginalExtension();
            if($extension != 'mp3'  && $extension != 'ogg'){
                return Code::response(404, "请上传mp3或者ogg格式文件");
            }
            $file->move(storage_path() . DIRECTORY_SEPARATOR . 'audio', "$name.$extension");
            $model = new Audio();
            $model->path = "$name.$extension";
            $model->time = time();
            if($model->save()){
                $model->audio = Config::get("urls.audiourl") . "$name.$extension";
                return Code::response(0, $model->toArray());
            }else{
                return Code::response(404, "保存失败");
            }
        }else{
            return Code::response(404, "文件的字段为audio");
        }
    }
    public function richEditor(){
        $action = Request::get("action");
        $file = Request::file("upfile");
        header("Content-Type: text/html; charset=utf-8");
        header("Access-Control-Allow-Methods:GET,POST,OPTIONS");
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With");
        $result = array();
        switch ($action) {
                /* 上传图片 */
            case 'uploadimage':
                //生出图片
                if($file == null){
                    echo json_encode(['state'=> "配置imageFieldName为upfile"]);
                    return;
                }
                $name = md5_file($file->getRealPath());
                $begin = substr($name, 0, 3);
                $begin = dechex(hexdec($begin)/4);//取前三位除以4 落在1024之间
                $end = substr($name, -3);
                $end = dechex(hexdec($end)/4);//取前三位除以4 落在1024之间
                $fullname = $name . '.' . $file->getClientOriginalExtension();
                $path = $begin . '/' . $end;
                $fullpath = $path . '/' . $fullname;
                $picture_model = Picture::where("path", $fullpath)->first();
                if($picture_model == null){
                    $model = new Picture();
                    $model->name = $file->getClientOriginalName();
                    $model->path = $fullpath;
                    $model->desc = "富文本";
                    $model->time = time();
                    $movePath = Config::get('urls.picPath') . '/' . $path;
                    $movePath = str_replace('/', DIRECTORY_SEPARATOR, $movePath);
                    if($file->move($movePath, $fullname)){//移动文件
                        $model->save();
                    }
                }else{
                }
                $result['state'] = "SUCCESS";
                $result['url'] = Config::get("urls.picUrl") . $fullpath;
                $result['title'] = $fullname;
                $result['original'] = $file->getClientOriginalName();
                $result['type'] = "." . $file->getClientOriginalExtension();
                $result['size'] = $file->getClientSize();
            break;
            case 'config':
                $result = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents( config_path() . DIRECTORY_SEPARATOR . "config.json")));
                break;
            default:
                break;
                /* 上传涂鸦 */
            //case 'uploadscrawl':
                /* 上传视频 */
            //case 'uploadvideo':
                /* 上传文件 */
            //case 'uploadfile':
        }
        //输出jsonp
        if(Request::has('callback')){
            $callback = Request::get("callback");
            if (preg_match("/^[\w_]+$/", $callback)) {
                echo htmlspecialchars($callback) . '(' . json_encode($result) . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        }else{
            echo json_encode($result);
        }

    }

    //章节预览
    public function previewUpdate(){
        $chapter_id = Request::get('chapter_id');
        if($chapter_id){
            $data = Request::all();
            $key = "laravel_chapter_$chapter_id";
            Cache::put($key, json_encode($data), 10);
            return Code::response(0, json_decode(Cache::get($key)));
        }else{
            return Code::response(404, "请传入chapter_id");
        }
    }
    //章节预览
    public function preview(){
        $chapter_id = Request::get('chapter_id');
        if($chapter_id){
            $data = Cache::get("laravel_chapter_$chapter_id");
            return Code::response(0, json_decode($data));
        }else{
            return Code::response(404, "请传入chapter_id");
        }

    }
}
