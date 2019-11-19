<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/8/28
 * Time: 11:57
 */

namespace Lsshu\Wechat\Classes;


use Lsshu\Wechat\Interfaces\BaseInterface;
use Lsshu\Wechat\Traits\TemplateMessage;

class Account extends BaseClass implements BaseInterface
{
    use TemplateMessage; //使用模板通知
    protected static $instance;     //对象实例
    protected $jsapiTicketCache = true; // jsapi_ticket 缓存

    /**
     * 构造函数
     * @access protected
     * @param array $options 参数 appId appSecret tmpPath
     */
    protected function __construct($options = array())
    {
        $this->appId = $options['appId'] ?? '';
        $this->appSecret = $options['appSecret'] ?? '';
        $this->jsapiTicketCache = $options['jsapiTicketCache'] ?? true;
        $this->accessTokenCache = $options['accessTokenCache'] ?? true;
    }
    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return object|static
     */
    public static function instance($options = array())
    {
        if (is_null(self::$instance)) {
            self::$instance = new self($options);
        }
        return self::$instance;
    }

    /**
     * 获取签名包
     * @param null $url 地址
     * @return array
     */
    public function getSignPackage($url = null) :array
    {
        $jsapiTicket = $this->getJsApiTicket();
        // 注意 URL 一定要动态获取，不能 hardcode.
        if(empty($url)){
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        }
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    /**
     * 获取JsApiTicket
     * @param bool $qyapi
     * @return bool
     */
    private function getJsApiTicket($qyapi = false)
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        if($this->jsapiTicketCache && empty(!$data = $this->fileCaches('jsapi_ticket')) && $data['expire_time'] > time()){
            $ticket = $data['ticket'];
        }else{
            $accessToken = $this->getAccessToken();
            $url = $qyapi ? "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken" :
                "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $data = $this->httpGet($url);

            if(isset($data['ticket'])){
                $ticket = $data['ticket'];
                if($this->jsapiTicketCache){
                    $data['expire_time']  = time() + 7000;
                    $this->fileCaches('jsapi_ticket',$data);
                }
            }else{
                $this->errors(['txt'=>'jsapi_ticket','data'=>$data]);
                $ticket = false;
            }
        }
        return $ticket;
    }

    /**
     * 网页授权获
     * @param string $redirect_uri 回调地址
     * @param string $scope 获取基本 snsapi_base | 获取用户详细信息 snsapi_userinfo
     * @param string $state
     * @return bool|string
     */
    public function getAuthorizeBaseInfo($redirect_uri='', $scope = 'snsapi_base', $state='lsshu')
    {
        if(!empty($redirect_uri)){
            $redirect_uri = urlencode($redirect_uri);
            $appid = $this->appId;
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$appid."&redirect_uri=".$redirect_uri."&response_type=code&scope=".$scope."&state=".$state."#wechat_redirect";
            return $url;
        }
        return false;
    }
    /**
     *  获取基本回调 openid
     * @param $code
     * @return mixed
     */
    public function getAuthorizeUserOpenId($code)
    {
        if(!empty($code)){
            $appid = $this->appId;
            $secret = $this->appSecret;
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
            return $this->httpGet($url);
        }
    }
    /**
     * 获取用户详细信息 回调
     * @param $code
     * @return array|bool
     */
    public function getAuthorizeUserInfo($code)
    {
        $appid = $this->appId;
        $secret = $this->appSecret;
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$appid."&secret=".$secret."&code=".$code."&grant_type=authorization_code";
        $access = $this->httpGet($url);
        if(isset($access['access_token'])){
            return $this->getAuthorizeUserInfoByAccessToken($access);
        }
        $this->errors(['txt'=>'Authorize access_token',$access]);
        return false;
    }

    /**
     * 获取用户详细信息
     * @param $access
     * @return array|bool
     */
    public function getAuthorizeUserInfoByAccessToken($access)
    {

        if(isset($access['access_token'])){
            $access_token = $access['access_token'];
            $openid = $access['openid'];
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
            $user = $this->httpGet($url);
            return compact('access','user');
        }
        return false;
    }
}