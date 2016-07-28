<?php namespace App;

use App\Models\BaseModel;

class Code extends BaseModel {
    public static function response($errCode, $datas = array()) {
        header("Access-Control-Allow-Origin:*");
        header("Access-Control-Allow-Methods:GET,POST,OPTIONS");
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
        } else if($errCode == 102) {
            $json = array(
                'errCode'   =>  102,
                'msg'       =>  '没有这个用户',
                'data'      =>  $datas,
            );
        } else if($errCode == 103) {
            $json = array(
                'errCode'   =>  103,
                'msg'       =>  '不是老师',
                'data'      =>  $datas,
            );
        } else if($errCode == 201) {
            $json = array(
                'errCode'   =>  201,
                'msg'       =>  '没有查询到问题',
                'data'      =>  $datas,
            );
        } else if($errCode == 301) {
            $json = array(
                'errCode'   =>  301,
                'msg'       =>  '问题已经被回答',
                'data'      =>  $datas,
            );
        } else if($errCode == 302) {
            $json = array(
                'errCode'   =>  302,
                'msg'       =>  '错误的server_id',
                'data'      =>  $datas,
            );
        } else if($errCode == 303) {
            $json = array(
                'errCode'   =>  303,
                'msg'       =>  '写入失败',
                'data'      =>  $datas,
            );
        } else if($errCode == 303) {
            $json = array(
                'errCode'   =>  303,
                'msg'       =>  '音频名称错误',
                'data'      =>  $datas,
            );
        } else if($errCode == 401) {
            $json = array(
                'errCode'   =>  401,
                'msg'       =>  '邀请码错误',
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
