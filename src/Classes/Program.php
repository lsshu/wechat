<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/8/28
 * Time: 11:55
 */

namespace Lsshu\Wechat\Classes;


use Lsshu\Wechat\Interfaces\BaseInterface;

class Program extends BaseClass implements BaseInterface
{
    protected static $instance;     //对象实例
    /**
     * 构造函数
     * @access protected
     * @param array $options 参数 appId appSecret tmpPath
     */
    protected function __construct($options = array())
    {
        $this->appId = $options['appId'] ?? '';
        $this->appSecret = $options['appSecret'] ?? '';
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
     * code 换取 session
     * @param $code wx.login 获取的code
     * @return mixed
     */
    public function code2Session($code)
    {
        $appid = $this->appId;
        $secret = $this->appSecret;
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code=".$code."&grant_type=authorization_code";
        return $this->httpGet($url);
    }

    /**
     * 获取小程序二维码，
     * 适用于需要的码数量较少的业务场景。
     * 通过该接口生成的小程序码，永久有效，有数量限制
     * @param $path 扫码进入的小程序页面路径，最大长度 128 字节，不能为空
     * @param int $width 二维码的宽度，单位 px。最小 280px，最大 1280px
     * @return bool|void
     */
    public function createWXAQRCode($path, $width=430)
    {
        $width = $width + 0;
        $width = $width < 280 ? 280 : ($width > 1280 ? 1280 : $width);
        if(!empty($path)){
            $access_token = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token;
            return $this->httpPost($url,compact('width','path'));
        }
        return false;
    }
    /**
     * 获取小程序码，
     * 适用于需要的码数量较少的业务场景。
     * 通过该接口生成的小程序码，永久有效，有数量限制
     * @param $path 扫码进入的小程序页面路径，最大长度 128 字节，不能为空
     * @param int $width 二维码的宽度，单位 px。最小 280px，最大 1280px
     * @param $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调
     * @param $line_color auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示
     * @param bool $is_hyaline 是否需要透明底色，为 true 时，生成透明底色的小程序码
     * @return bool|void
     */
    public function getWXACode($path, $width = 430, $auto_color, $line_color, $is_hyaline = false)
    {
        $width = $width + 0;
        $width = $width < 280 ? 280 : ($width > 1280 ? 1280 : $width);
        if(!empty($path)){
            $access_token = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$access_token;
            return $this->httpPost($url,compact('width','path','auto_color','line_color','is_hyaline'));
        }
        return false;
    }
    /**
     * 获取小程序码，
     * 适用于需要的码数量极多的业务场景。
     * 通过该接口生成的小程序码，永久有效，数量暂无限制。
     * @param $scene 最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，其它字符请自行编码为合法字符（因不支持%，中文无法使用 urlencode 处理，请使用其他编码方式）
     * @param $page 必须是已经发布的小程序存在的页面（否则报错），例如 pages/index/index, 根路径前不要填加 /,不能携带参数（参数请放在scene字段里），如果不填写这个字段，默认跳主页面
     * @param int $width 二维码的宽度，单位 px，最小 280px，最大 1280px
     * @param bool $auto_color 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调，默认 false
     * @param $line_color auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示
     * @param bool $is_hyaline 是否需要透明底色，为 true 时，生成透明底色的小程序
     * @return bool|void
     */
    public function getWXACodeUnlimit($scene, $page, $width = 430, $auto_color = false, $line_color, $is_hyaline = false)
    {
        $width = $width + 0;
        $width = $width < 280 ? 280 : ($width > 1280 ? 1280 : $width);
        if(!empty($path)){
            $access_token = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token;
            return $this->httpPost($url,compact('scene','width','page','auto_color','line_color','is_hyaline'));
        }
        return false;
    }
}