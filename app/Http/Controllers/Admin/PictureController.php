<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Picture;
use Session;
use Request;
use App\Code;
use Config;

class PictureController extends Controller {
    public function index(){
        $page = intval(Request::get("page"));
        $number = intval(Request::get("number"));
        if($page && $number){
            $data = [];
            $index = ($page - 1) * $number;
            $data['datas'] = Picture::skip($index)->take($number)->get()->toArray();
            foreach ($data['datas'] as &$item){
                $item['path'] = Config::get('urls.picUrl') . "/" . $item['path'];
            }
            $data['page'] = $page;
            $data['number'] = $number;
            $data['total'] = Picture::count();
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
            $data =  $picture_model->toArray();
            $data['path'] = Config::get('urls.picUrl') . $data['path'];
            return Code::response(0, $data);
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
            $data =  $model->toArray();
            $data['path'] = Config::get('urls.picUrl') . $data['path'];
            return Code::response(0, $data);
        }
    }
}



