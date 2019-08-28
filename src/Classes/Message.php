<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/8/28
 * Time: 18:01
 */

namespace Lsshu\Wechat\Classes;


use Lsshu\Wechat\Traits\PKCS7Encoder;
use Lsshu\Wechat\Traits\Prpcrypt;
use Lsshu\Wechat\Traits\XMLParse;

class Message
{
    use Prpcrypt, PKCS7Encoder, XMLParse;
    protected static $instance;     //对象实例
    private $appId;
    private $token;
    private $encodingAesKey;

    /**
     * 构造函数
     * token string 公众平台上，开发者设置的token
     * encodingAesKey string 公众平台上，开发者设置的EncodingAESKey
     * appId string 公众平台的appId
     * @access protected
     * @param array $options 参数
     */
    protected function __construct($options = array())
    {
        $this->appId = $options['appId'] ?? '';
        $this->token = $options['token'] ?? '';
        $this->encodingAesKey = $options['encodingAesKey'] ?? '';
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
     * 将公众平台回复用户的消息加密打包.
     * <ol>
     *    <li>对要发送的消息进行AES-CBC加密</li>
     *    <li>生成安全签名</li>
     *    <li>将消息密文和安全签名打包成xml格式</li>
     * </ol>
     *
     * @param $replyMsg string 公众平台待回复用户的消息，xml格式的字符串
     * @param $timeStamp string 时间戳，可以自己生成，也可以用URL参数的timestamp
     * @param $nonce string 随机串，可以自己生成，也可以用URL参数的nonce
     * @param &$encryptMsg string 加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串,
     *                      当return返回0时有效
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function encryptMsg($replyMsg, $timeStamp, $nonce, &$encryptMsg)
    {
        //加密
        $array = $this->encrypt($replyMsg);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        if ($timeStamp == null) {
            $timeStamp = time();
        }
        $encrypt = $array[1];
        //生成安全签名
        $array = $this->getSHA1($timeStamp, $nonce, $encrypt);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        $signature = $array[1];
        //生成发送的xml
        $encryptMsg = $this->generate($encrypt, $signature, $timeStamp, $nonce);
        return ErrorCode::$OK;
    }

    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     *
     * @param $msgSignature string 签名串，对应URL参数的msg_signature
     * @param $timestamp string 时间戳 对应URL参数的timestamp
     * @param $nonce string 随机串，对应URL参数的nonce
     * @param $postData string 密文，对应POST请求的数据
     * @param &$msg string 解密后的原文，当return返回0时有效
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptMsg($msgSignature, $timestamp = null, $nonce, $postData, &$msg)
    {
        if (strlen($this->encodingAesKey) != 43) {
            return ErrorCode::$IllegalAesKey;
        }
        //提取密文
        $array = $this->extract($postData);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        if ($timestamp == null) {
            $timestamp = time();
        }
        $encrypt = $array[1];
        $touser_name = $array[2];
        //验证安全签名
        $array = $this->getSHA1($timestamp, $nonce, $encrypt);
        $ret = $array[0];
        if ($ret != 0) {
            return $ret;
        }
        $signature = $array[1];
        if ($signature != $msgSignature) {
            return ErrorCode::$ValidateSignatureError;
        }
        $result = $this->decrypt($encrypt);
        if ($result[0] != 0) {
            return $result[0];
        }
        $msg = $result[1];
        return ErrorCode::$OK;
    }

    /**
     * 检验signature
     * @param $signature 微信加密签名
     * @param $timestamp 时间戳
     * @param $nonce 随机数
     * @return bool true 原样返回echostr参数内容
     */
    private function checkSignature($signature, $timestamp, $nonce)
    {
        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 用SHA1算法生成安全签名
     * @param $timestamp 时间戳
     * @param $nonce 随机字符串
     * @param $encrypt_msg 密文消息
     * @return array
     */
    public function getSHA1($timestamp, $nonce, $encrypt_msg)
    {
        $token = $this->token;
        //排序
        try {
            $array = array($encrypt_msg, $token, $timestamp, $nonce);
            sort($array, SORT_STRING);
            $str = implode($array);
            return array(ErrorCode::$OK, sha1($str));
        } catch (Exception $e) {
            //print $e . "\n";
            return array(ErrorCode::$ComputeSignatureError, null);
        }
    }
}