<?php namespace App;

use Cache;
use Config;
use Session;
use Redirect;
use Curl\Curl;
use App\Models\BaseModel;

class Wechat extends BaseModel {
    private $appId;
    private $appSecret;
	
	public function __construct() {
		parent::__construct();
        $this->appId = Config::get("wechat.appid");
        $this->appSecret = Config::get("wechat.appsecret");
	}

    public function getQRCode() {
        $curl = new Curl();
        $token = $this->getToken();

        $curl->post("https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=$token", json_encode(array(
            'action_name'   =>  'QR_LIMIT_STR_SCENE',
            'action_info'   =>  array(
                'scene' =>  array(
                    'scene_str'  => "7dyk"
                )
            )
        ), JSON_UNESCAPED_UNICODE));
        dd($curl->response);

        $ticket = urlencode($curl->response->ticket);

        $curl->get("https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket");
        $response = $curl->response;
        return $response;
    }

    public function getSignPackage($url) {
        $ticket = $this->getTicket();
        $appid = $this->appId;

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        //$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $appid,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
        );
        return $signPackage; 
    }

    public function isLogin() {
        return Session::has('openid');
    }

    public function loginWechat($url) {
        $appid = $this->appId;
        $url = urlencode($url);
        $login_url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$url&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";

        return Redirect::to($login_url);
    }

    public function getFile($media_id) {
        $curl = new Curl();
        $token = $this->getToken();

        $curl->get('http://file.api.weixin.qq.com/cgi-bin/media/get', array(
            'access_token'  =>  $token,
            'media_id'      =>  $media_id,
        ));
        $response = $curl->response;

        return $response;
    }

    public function getOpenid($code) {
        if(Cache::has('openid')) {
            $openid = Cache::get('openid');
            return $openid;
        }
        $appid = $this->appId;
        $appsecret = $this->appSecret;
        $curl = new Curl();
        $code_url = "https://api.weixin.qq.com/sns/oauth2/access_token";

        //取得token
        $curl->get($code_url, array(
            'appid'         =>  $appid,
            'secret'        =>  $appsecret,
            'code'          =>  $code,
            'grant_type'    =>  'authorization_code',
        ));
        $response = json_decode($curl->response);
        Cache::put('access_token', $response->access_token, 110);
        Cache::put('opneid', $response->openid, 110);
        //这里没有用到refresh_token,用户量大以后可以使用

        return $response->openid;
    }

    public function getUserinfo($access_token, $openid) {
        $curl = new Curl();
        $url = "https://api.weixin.qq.com/sns/userinfo";

        $curl->get($url, array(
            'access_token'      =>  $access_token,
            'openid'            =>  $openid,
            'lang'              =>  'zh_CN',
        ));
        $response = json_decode($curl->response);
        return $response;
    }

    /**
     * @param $openId 用户的OpenId
     * @param $data   发送的内容
     * @param $redirectUrl  回调的Url
     * @param $type   消息类型
     */
    public function sendMessage($openId, $message, $redirectUrl, $type){
        $curl = new Curl();
        $token = $this->getToken();
        $data = array();
        foreach ($message as $key=> $value){
            $data[$key] =  array(
                   "value" => $value,
                   "color" => '#459ae9'
               );
        };
        $data = [
            'touser' => $openId,
            'template_id' => Config::get('wechat.template' . $type),
            'url' => $redirectUrl,
            'data' => $data
        ];
        $curl->post("https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$token",
            json_encode($data), JSON_UNESCAPED_UNICODE);
        $curl->response;
    }
	public function getToken() {
		if(Cache::has('token')) {
		    return Cache::get('token');
		} else {
			return $this->refreshToken();
		}
	}

	private function refreshToken() {
		$appid = $this->appId;
		$appsecret = $this->appSecret;
        $curl = new Curl();

		//取得当前token
        $curl->get('https://api.weixin.qq.com/cgi-bin/token', array(
            'grant_type'    =>  'client_credential',
            'appid'         =>  $appid,
            'secret'        =>  $appsecret,
        ));
        $response = $curl->response;

        //将token存入Cache
        Cache::put('token', $response->access_token, 110);
        return $response->access_token;
	}

    public function sentTemplate1(){

    }

    private function getTicket() {
        if(Cache::has('ticket')) {
            return Cache::get('ticket');
        } else {
            return $this->refreshTicket();
        }
    }

    private function refreshTicket() {
        $curl = new Curl();
        $token = $this->getToken();

        //取得当前ticket
        $curl->get("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$token&type=jsapi");
        $response = $curl->response;

        //将ticket存入Cache
        Cache::put('ticket', $response->ticket, 110);
        return $response->ticket;
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
