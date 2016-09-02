<?php namespace App;

use Cache;
use Config;
use Log;
use Session;
use Redirect;
use Curl\Curl;
use App\Models\BaseModel;
use App\Wechat\WxPayConfig;

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
     * @param $message   发送的内容
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
        return $curl->response;
    }
	public function getToken() {
        if($_SERVER['HTTP_HOST'] == 'localhost'){
            $curl = new Curl();
            $code_url = "http://h5app.7dyk.com/ama/api/public/timer/getToken";
            //取得token
            $curl->get($code_url, array(
                'token' => Config::get('inner.token')
            ));
            $response = json_decode($curl->response);
            if($response->errCode == 0){
                return $response->data;
            }else{
                return false;
            }
        }else{
            if(Cache::has('token')) {
                return Cache::get('token');
            } else {
                return $this->refreshToken();
            }
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
    /**
     *参数说明，openID，用户在该公众号唯一标识，nickname:用户名，money提现金额，单位元，大于1, desc,描述信息
     *return true提现成功，false提现失败
     */
    public function get_cash($openID, $nickname, $money, $desc, $partner_trade_no){
        $mch_appid = WxPayConfig::APPID;
        $mchid = WxPayConfig::MCHID;
        $nonce_str = $this->createNonceStr(32);
        $openid = $openID;
        $check_name='NO_CHECK';
        $re_user_name=$nickname;
        $amount = intval($money * 100);
        $desc = $desc;
        $spbill_create_ip=$_SERVER["REMOTE_ADDR"];//请求ip
//        $spbill_create_ip='10.205.41.194';//请求ip
        //封装成数据
        $dataArr=array();
        $dataArr['amount']=$amount;
        $dataArr['check_name']=$check_name;
        $dataArr['desc']=$desc;
        $dataArr['mch_appid']=$mch_appid;
        $dataArr['mchid']=$mchid;
        $dataArr['nonce_str']=$nonce_str;
        $dataArr['openid']=$openid;
        $dataArr['partner_trade_no']=$partner_trade_no;
        $dataArr['re_user_name']=$re_user_name;
        $dataArr['spbill_create_ip']=$spbill_create_ip;
        //获取签名
        $sign=$this->getSign($dataArr, WxPayConfig::KEY);

        $data="<xml>
				<mch_appid>".$mch_appid."</mch_appid>
				<mchid>".$mchid."</mchid>
				<nonce_str>".$nonce_str."</nonce_str>
				<partner_trade_no>".$partner_trade_no."</partner_trade_no>
				<openid>".$openid."</openid>
				<check_name>".$check_name."</check_name>
				<re_user_name>".$re_user_name."</re_user_name>
				<amount>".$amount."</amount>
				<desc>".$desc."</desc>
				<spbill_create_ip>".$spbill_create_ip."</spbill_create_ip>
				<sign>".$sign."</sign>
				</xml>";
        $ch = curl_init ();
        $MENU_URL="https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        curl_setopt ( $ch, CURLOPT_URL, $MENU_URL );
        curl_setopt ( $ch, CURLOPT_CUSTOMREQUEST, "POST" );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT,WxPayConfig::SSLCERT_PATH);
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY,WxPayConfig::SSLKEY_PATH);
        curl_setopt ( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt ( $ch, CURLOPT_AUTOREFERER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
        $data = curl_exec ( $ch );
        if($data){
            curl_close($ch);
            return $data;
        }
        else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }
    //生成签名
    private function getSign($Obj, $key){
        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$key;
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }
     /**
     * 	作用：格式化参数，签名过程需要使用
     */
    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        Log::info("处理前: " . $buff  . ' 处理后： '. $reqPar);
        return $reqPar;
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
