<?php namespace App;

use App\Models\BaseModel;

class Code extends BaseModel {
    public static function response($errCode, $datas = array()) {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST");
        header("Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With");
        if($errCode == 100) {
            $json = array(
                'errCode'   =>  100,
                'msg'       =>  '参数错误',
                'data'      =>  $datas,
            );
        } else if($errCode == 101) {
            $json = array(
                'errCode'   =>  101,
                'msg'       =>  '用户未登陆或session过期',
                'data'      =>  $datas,
            );
        } else if($errCode == 201) {
            $json = array(
                'errCode'   =>  201,
                'msg'       =>  '没有查询到问题',
                'data'      =>  $datas,
            );
        } else {
            $json = array(
                'errCode'   =>  0,
                'msg'       =>  'ok',
                'data'      =>  $datas,
            );
        }
        return json_encode($json, JSON_UNESCAPED_UNICODE+JSON_UNESCAPED_SLASHES);
    }
}
