<?php

namespace Wchat;

use think\Cache as cache;

/**
 * 功能说明：微信基本功能测试编码，可以获取通过开放平台得到的公众号会话（token）以及公众号对应的appid
 * 创建时间：2018-3-9
 */
class WchatOauth {

    private $application = '';     //项目ID
    private $token = '';          //token
    private $config = array();    //微信参数配置

    public function __construct($wchat_config = array(), $application = ''){
        $this->application = $application ?: 'instanceid_0';
        $this->config = $wchat_config;
    }

    /*******************************************基础信息**************************************************/

    /**
     * [get_access_token 公众平台模式获取access_token]
     */
    private function get_access_token(){
        $token = $this->single_get_access_token();
        return $token;
    }

    /**
     * [single_get_access_token 公众平台账户获取token]
     */
    private function single_get_access_token(){
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->config['wchat_appid'] . '&secret=' . $this->config['wchat_secret'];
        $result = $this->https_request($url);
        $strjson = json_decode($result);
        if($strjson == false || empty($strjson)){
            return '';
        }else{
            $token = $strjson->access_token;
            if (empty($token)) {} else {
                // 注意如果是多用户需要
                cache::set('token-' . $this->application, $token, 3600);
            }
            return $token;
        }
    }

    /**
     * [get_url_return 微信数据获取]
     * @param  [type]  $url       [请求地址]
     * @param  string  $data      [请求数据]
     * @param  boolean $needToken [是否重新获取]
     */
    private function get_url_return($url, $data = '', $needToken = false){
        // 第一次为空，则从文件中读取
        if (empty($this->token)) {
            $this->token = cache::get('token-' . $this->application);
        }

        // 为空则重新取值
        if (empty($this->token) or $needToken) {
            
            $this->get_access_token();
            $this->token = cache::get('token-' . $this->application);
        }

        $newurl = sprintf($url, $this->token);
        $AjaxReturn = $this->https_request($newurl, $data);
        $strjson = json_decode($AjaxReturn, true);
        if (! empty($strjson['errcode'])) {
            switch ($strjson['errcode']) {
                case 40001:
                    return $this->get_url_return($url, $data, true); // 获取access_token时AppSecret错误，或者access_token无效
                    break;
                case 40014:
                    return $this->get_url_return($url, $data, true); // 不合法的access_token
                    break;
                case 42001:
                    return $this->get_url_return($url, $data, true); // access_token超时
                    break;
                case 45009:
                    return json_encode(array(
                        "errcode" => - 45009,
                        "errmsg" => "接口调用超过限制：" . $strjson['errmsg']
                    ));
                    break;
                case 41001:
                    return json_encode(array(
                        "errcode" => - 41001,
                        "errmsg" => "缺少access_token参数：" . $strjson['errmsg']
                    ));
                    break;
                default:
                    return json_encode(array(
                        "errcode" => - 41000,
                        "errmsg" => $strjson['errmsg']
                    )); // 其他错误，抛出
                    break;
            }
        } else {
            return $strjson;
        }
    }

    /**
     * [https_request http/https请求函数]
     * @param  string $url  [请求地址]
     * @param  [type] $data [请求数据]
     */
    private function https_request($url = '', $data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if(!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }

    /**************************************************网页授权**************************************************/
    
    /**
     * [get_member_access_token 获取openid(前台会员)]
     */
    public function get_member_access_token(){
        // 通过code获得openid
        if (empty($_GET['code'])) {
            // 触发微信返回code码
            $baseUrl = request()->url(true);
            $url = $this->get_single_authorize_url($baseUrl, 'STATE');
            
            Header("Location: $url");
            exit();
        } else {
            // 获取code码，以获取openid
            $code = $_GET['code'];
            $data = $this->get_single_access_token($code);
            return $data;
        }
    }

    /**
     * [get_single_access_token 获取OAuth2授权access_token(微信公众平台模式)]
     * @param  string $code [通过get_authorize_url获取到的code]
     */
    public function get_single_access_token($code = ''){
        $token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->config['wchat_appid'] . '&secret=' . $this->config['wchat_secret'] . '&code=' . $code . '&grant_type=authorization_code';
        $token_data = $this->get_url_return($token_url);
        return $token_data;
    }

    /**
     * [get_single_authorize_url 获取微信OAuth2授权链接snsapi_base]
     * @param  string $redirect_url [回调地址]
     * @param  string $state        [不弹出授权页面，直接跳转，只能获取用户openid]
     */
    public function get_single_authorize_url($redirect_url = '', $state = ''){
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $this->config['wchat_appid'] . "&redirect_uri=" . $redirect_url . "&response_type=code&scope=snsapi_userinfo&state={$state}&connect_redirect=1#wechat_redirect";
    }

    /**
     * [get_oauth_member_info 获取会员对于公众号信息]
     * @param  [type] $token [token信息]
     */
    public function get_oauth_member_info($token){
        $token_url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $token['access_token'] . "&openid=" . $token['openid'] . "&lang=zh_CN";
        $data = $this->get_url_return($token_url);
        return $data;
    }

    /**
     * 获取粉丝信息（通过openID）
     *
     * @param unknown $openid            
     * @return Ambigous <string, \data\extend\unknown, mixed>
     */
    public function get_fans_info($openid){
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid={$openid}";
        return $this->get_url_return($url);
    }
}