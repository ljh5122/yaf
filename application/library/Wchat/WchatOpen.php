<?php

namespace Wchat;
use Wchat\WxBizMsgCrypt as WxBizMsgCrypt;

/**
 * 功能说明：微信第三方平台接口（用于提供微信公众号应用）
 * 创建时间：2018-3-14
 */
class WchatOpen{

    public $ticket;//推送component_verify_ticket，开放平台设置后每隔10分钟发一次，但要解密
    public $appId;//开放平台appid，在开放平台中
    public $appsecret;//开放平台app密码，在开放平台中
    public $tk;//通过开放平台设置的开放平台的token
    public $encodingAesKey;//开放平台的加密解密秘钥,在开放平台中
    public $component_token;//通过ticket等获取到的开放平台的access_token
    public $pre_auth_code;//预授权码
    public $author_appid;//获取的公众平台的appid
    public $token = '';//获取公众账号的token

    function __construct($sys_config = array()){
        $this->appId = $sys_config['open_AppID'];
        $this->appsecret = $sys_config['open_AppSecret'];
        $this->encodingAesKey = $sys_config['open_AesKey'];
        $this->tk = $sys_config['open_Token'];
        $this->author_appid = $sys_config['wchat_appid'];
        $this->token = $sys_config['wchat_token'];

        $this->init();
    }

    /**
     * 微信开放平台初始化
     */
    private function init(){
        $this->ticket = file_get_contents('ComponentVerifyTicket.txt');

        //获取第三方token
        $this->component_token = file_get_contents('component_access_token.txt');
        if(!$this->component_token){
            $this->component_token = $this->getCommonAccessToken();
        }
        
        //获取预授权码
        $this->pre_auth_code =  file_get_contents('pre_auth_code.txt');
        if(!$this->pre_auth_code){
            $this->pre_auth_code =  $this->getPreAuthCode();
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
        return json_decode($output, true);
    }

    /**
     * 获取第三方token,需要第三方的appid，密码，ticket     $component_token
     */
    private function getCommonAccessToken(){
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_component_token";
        $data = array('component_appid'=>$this->appId,'component_appsecret'=>$this->appsecret,'component_verify_ticket'=>$this->ticket);
        $data = json_encode($data);

        $token_arr = $this->https_request($url, $data);
        if(!empty($token_arr['component_access_token'])){
            file_put_contents('component_access_token.txt', $token_arr['component_access_token']);
        }

        return $token_arr['component_access_token'];
    }

    /**
     * 获取第三方平台的预授权码    需要第三方token，以及第三方appid
     */
    private function getPreAuthCode(){
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=".$this->component_token;
        $data = array('component_appid'=>$this->appId);
        $data = json_encode($data);

        $code_arr = $this->https_request($url, $data);
        if(!empty($code_arr['pre_auth_code'])){
            file_put_contents('pre_auth_code.txt', $code_arr['pre_auth_code']);
        }

        return $code_arr['pre_auth_code'];
    }


    /****************************公从号对第三方授权*************************************/

    /**
     * 授权入口，注意授权之后的auth_code要存入库中
     */
    public function auth($url){
        $redurl = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?component_appid='.$this->appId.'&pre_auth_code='.$this->pre_auth_code.'&redirect_uri='.$url;
        return $redurl;
    }

    /**
     * 使用授权码换取公众号的接口调用凭据和授权信息,得到之后要存入数据库中，尤其是authorizer_appid,authorizer_refresh_token  只提供一次
     */
    public function get_query_auth($author_code){
        //此页面可以是授权的回调地址通过get方法获取到authorization_code
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=".$this->component_token;
        $data = array('component_appid'=>$this->appId,'authorization_code'=>$author_code);
        $data = json_encode($data);

        $code_arr = $this->https_request($url, $data);
        file_put_contents('authorizer.txt', serialize($code_arr));
        return $code_arr;
    }

    /**
     * 通过上述方法获取的公众号authorizer_access_token可能会过期，因此需要定时获取authorizer_access_token
     */
    public function get_access_token($appid, $authorizer_refresh_token){
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=".$this->component_token;
        $data = array('component_appid'=>$this->appId,'authorizer_appid'=>$appid,'authorizer_refresh_token'=>$authorizer_refresh_token);
        $data = json_encode($data);

        $token_arr = $this->https_request($url, $data);
        if(!empty($token_arr['authorizer_access_token'])){
            return $token_arr['authorizer_access_token'];
        }else{
            return '';
        }
    }

    /**
     * 该API用于获取授权方的公众号基本信息，包括头像、昵称、帐号类型、认证类型、微信号、原始ID和二维码图片URL
     */
    public function get_authorizer_info($appid){
        $url = "https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?component_access_token=".$this->component_token;
        $data = array('component_appid'=>$this->appId,'authorizer_appid'=>$appid);
        $data = json_encode($data);
        $code_arr = $this->https_request($url, $data);

        return $code_arr;
    }


    /**********************************第三方微信网页授权**************************************/

    /**
     * [get_member_access_token 获取openid(前台会员)]
     */
    public function get_member_access_token(){
        // 通过code获得openid
        if (empty($_GET['code'])) {
            // 触发微信返回code码
            $baseUrl = request()->url(true);
            $url = $this->get_authorize_url_info($baseUrl, 'STATE');
            
            Header("Location: $url");
            exit();
        } else {
            // 获取code码，以获取openid
            $code = $_GET['code'];
            $data = $this->get_access_token_member($code);
            return $data;
        }
    }

    /**
     * 获取微信OAuth2授权链接snsapi_base,snsapi_userinfo
     * @param string $redirect_uri 跳转地址
     * @param mixed $state 参数
     * 不弹出授权页面，直接跳转，只能获取用户openid
     */
    private function get_authorize_url_info($redirect_uri = '', $state = ''){
        return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->author_appid}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state={$state}&component_appid={$this->appId}&connect_redirect=1#wechat_redirect";
    }

    /**
     * 开放平台代替公众号实现获取前台会员会话
     * @param unknown $appid
     * @param string $code
     * @return mixed
     */
    private function get_access_token_member($code = ''){
        $token_url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid={$this->author_appid}&code={$code}&grant_type=authorization_code&component_appid={$this->appId}&component_access_token=".$this->component_token;
        $result = $this->get_url_return($token_url);
        return $result;
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
     * 微信数据获取
     * @param unknown $url
     * @param unknown $data
     * @param string $needToken
     * @return string|unknown
     */
    private function get_url_return($url, $data=''){
        $strjson = $this->https_request($url, $data);
        if (!empty($strjson['errcode'])){
            switch ($strjson['errcode']){
                case 40001:
                    return $this->get_url_return($url, $data); //获取access_token时AppSecret错误，或者access_token无效
                    break;
                case 40014:
                    return $this->get_url_return($url, $data); //不合法的access_token
                    break;
                case 42001:
                    return $this->get_url_return($url, $data); //access_token超时
                    break;
                case 45009:
                    return "接口调用超过限制：".$strjson['errmsg'];
                    break;
                case 41001:
                    return "缺少access_token参数：".$strjson['errmsg'];
                    break;
                default:
                    return $strjson['errmsg']; //其他错误，抛出
                    break;
            }
        }else{
            return $strjson;
        }
    }
}