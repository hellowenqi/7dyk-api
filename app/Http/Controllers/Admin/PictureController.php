<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Picture;
use Session;
use Request;
use App\Code;
use Config;

class PictureController extends Controller {
    public function index(){
        $page = Request::get("page");
        $number = Request::get("number");
        if($page && $number){
            $index = ($page - 1) * $number;
            $data = Picture::skip($index)->take($number)->get()->toArray();
            foreach ($data as &$item){
                $item['path'] = Config::get('urls.picUrl') . "/" . $item['path'];
            }
            return Code::response(0, $data);
        }else{
            return Code::response(100);
        }
    }
    public function delete(){
        $id = Request::get("id");
        if($id){
            $model = Picture::find($id);
            if($model){
                $path = Config::get('urls.picPath') . '/' . $model->path;
                $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
                unlink($path);
                $model->delete();
                return Code::response(0);   
            }else{
                return Code::response(404, "id不存在");
            }
        }else{
            return Code::response(100);
        }
    }
    public function upload(){
        $file = Request::file('pic');
        if($file == null || !exif_imagetype($file)){ //如果上传不是图片格式，返回
            return Code::response(404, '请上传jpg，png，gif格式图片');
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
        if($picture_model){
            return Code::response(0, $picture_model->toArray());
        }
        $model = new Picture();
        $model->name = $file->getClientOriginalName();
        $model->path = $fullpath;
        $model->desc = Request::get('desc');
        $model->time = time();
        $movePath = Config::get('urls.picPath') . '/' . $path;
        $movePath = str_replace('/', DIRECTORY_SEPARATOR, $movePath);
        if($file->move($movePath, $fullname)){//移动文件
            $model->save();
            return Code::response(0, Config::get('urls.picUrl') . '/' . $model->path);
        }
    }
}



