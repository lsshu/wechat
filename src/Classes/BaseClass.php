<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/8/28
 * Time: 16:11
 */

namespace Lsshu\Wechat\Classes;


class BaseClass
{
    protected $accessTokenCache = true; // access_token 缓存
    protected static $errors = [];
    protected $appId;
    protected $appSecret;
    /**
     * CURL请求的函数httpRequest() 通过https 中的get 或 post 或者 上传文件
     * @param $url
     * @param null $data POST data
     * @param null $path FILE path
     * @return mixed
     */
    protected function httpsRequest($url, $data = null,$path = null)
    {
        $curl = curl_init();
        if(!empty($path)){
            if (class_exists('\CURLFile')) {
                curl_setopt($curl, CURLOPT_SAFE_UPLOAD, true);
                $data['file'] = new \CURLFile(realpath($path)); //php >=5.5
            } else {
                if (defined('CURLOPT_SAFE_UPLOAD')) {
                    curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
                }
                $data['file'] = '@' . realpath($path);  //php <=5.5
            }
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_USERAGENT,"LSSHU");
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    /**
     * http CURL get请求
     * @param $url
     * @return mixed
     */
    public function httpGet($url)
    {
        return json_decode($this->httpsRequest($url),true);
    }
    /**
     * http CURL post 请求
     * @param $url
     * @param $data
     * @return mixed
     */
    public function httpPost($url, $data)
    {
        return json_decode($this->httpsRequest($url,$data),true);
    }
    /**
     * 获取 AccessToken
     * @param bool $qyapi
     * @return string
     */
    public function getAccessToken($qyapi = false)
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        if($this->accessTokenCache && empty(!$data = $this->fileCaches('access_token')) && $data['expire_time'] > time()){
            $access_token = $data['access_token'];
        }else{
            //不缓存
            $url = $qyapi ? "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret" :
                "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $data = $this->httpGet($url);
            if(isset($data['access_token'])){
                $access_token = $data['access_token'];
                if($this->accessTokenCache){
                    $data['expire_time']  = time() + 7000;
                    $this->fileCaches('access_token',$data);
                }
            }else{
                $this->errors(['txt'=>'access_token','data'=>$data]);
                $access_token = false;
            }
        }
        return $access_token;
    }
    /**
     * 刷新access_token
     * @param $refresh_token
     * @return mixed
     */
    public function refreshAuthorizeToken($refresh_token)
    {
        $appid = $this->appId;
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=".$appid."&grant_type=refresh_token&refresh_token=".$refresh_token;
        return $this->httpGet($url);
    }
    /**
     * 检查 access_token
     * @param $access_token
     * @param $openid
     * @return mixed
     */
    public function checkAccessToken($access_token, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/auth?access_token=".$access_token."&openid=".$openid;
        return $this->httpGet($url);
    }
    /**
     * 创建随机字符串
     * @param int $length
     * @return string
     */
    protected function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 文件缓存
     * @param $key
     * @param null $value
     * @return bool|int|string
     */
    protected function fileCaches($key, $value=null)
    {
        return cacheFile($key,$value);
    }

    /**
     * 记录错误
     * @param array $error
     * @return array
     */
    protected function errors(array $error):array
    {
        return array_push(self::$errors,$error);
    }

}