<?php
/**
 * 提供提取消息格式中的密文及生成回复消息格式的接口.
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/8/28
 * Time: 18:09
 */

namespace Lsshu\Wechat\Traits;


trait XMLParse
{
    /**
     * 提取出xml数据包中的加密消息
     * @param $xmltext 待提取的xml字符串
     * @return array 提取出的加密消息字符串
     */
    public function extract($xmltext)
    {
        try {
            $xml = new DOMDocument();
            $xml->loadXML($xmltext);
            $array_e = $xml->getElementsByTagName('Encrypt');
            $array_a = $xml->getElementsByTagName('ToUserName');
            $encrypt = $array_e->item(0)->nodeValue;
            $tousername = $array_a->item(0)->nodeValue;
            return array(0, $encrypt, $tousername);
        } catch (Exception $e) {
            //print $e . "\n";
            return array(ErrorCode::$ParseXmlError, null, null);
        }
    }
    /**
     * 生成xml消息
     * @param $encrypt 加密后的消息密文
     * @param $signature 安全签名
     * @param $timestamp 时间戳
     * @param $nonce 随机字符串
     * @return string
     */
    public function generate($encrypt, $signature, $timestamp, $nonce)
    {
        $format = "<xml>
<Encrypt><![CDATA[%s]]></Encrypt>
<MsgSignature><![CDATA[%s]]></MsgSignature>
<TimeStamp>%s</TimeStamp>
<Nonce><![CDATA[%s]]></Nonce>
</xml>";
        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }
}