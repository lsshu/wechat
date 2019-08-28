<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/8/28
 * Time: 11:52
 */

namespace Lsshu\Wechat;


class Service
{
    protected static $instance;     //对象实例
    /**
     * 构造函数
     * @access protected
     * @param array $options 参数 appId appSecret tmpPath
     */
    protected function __construct($options = array())
    {

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
     * @param $method
     * @param $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return ('Lsshu\Wechat\Classes\\'.ucfirst($method))::instance(...$args);
    }
}