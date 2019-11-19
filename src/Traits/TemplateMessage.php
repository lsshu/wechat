<?php
/**
 * Created by PhpStorm.
 * User: lsshu
 * Date: 2019/11/19
 * Time: 10:26
 */

namespace Lsshu\Wechat\Traits;


trait TemplateMessage
{
    /**
     * 设置所属行业
     * @param array $industry_id ["industry_id1"=>"","industry_id2"=>""]
     * @return mixed
     */
    public function setIndustry(array $industry_id)
    {
        $accessToken = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token=' . $accessToken;
        return $this->httpPost($url, $industry_id);
    }

    /**
     * 获取设置的行业信息
     * @return mixed
     */
    public function getIndustry()
    {
        $accessToken = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_industry?access_token=' . $accessToken;
        return $this->httpGet($url);
    }

    /**
     * 获得模板ID
     * @param $template_id_short
     * @return mixed
     */
    public function getTemplateId($template_id_short)
    {
        $accessToken = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/api_add_template?access_token' . $accessToken;
        return $this->httpPost($url, $template_id_short);
    }

    /**
     * 获取模板列表
     * @return mixed
     */
    public function getTemplateList()
    {
        $accessToken = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=' . $accessToken;
        return $this->httpGet($url);
    }

    /**
     * 删除模板
     * @param $template_id
     * @return mixed
     */
    public function delTemplate($template_id)
    {
        $accessToken = $this->getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token=' . $accessToken;
        return $this->httpPost($url, compact('template_id'));
    }

    /**
     * @param $touser 接收者openid
     * @param $template_id 模板ID
     * @param $data 模板数据
     * @param null $redirect_url 模板跳转链接（海外帐号没有跳转能力）
     * @param null $color 模板内容字体颜色，不填默认为黑色
     * @param null $miniprogram 跳小程序所需数据，不需跳小程序可不用传该数据 ['appid'=>"","pagepath"=>""]
     * @return mixed
     */
    public function sendTemplateMessage($touser,$template_id,$data,$url=null,$color=null,$miniprogram=null)
    {
        $accessToken = $this->getAccessToken();
        $request_url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $accessToken;
        return $this->httpPost($request_url,json_encode(compact('touser','template_id','data','url','miniprogram','color')));
    }
}