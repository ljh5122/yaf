<?php

namespace Wchat;

/**
 * 微信红包封装类
 */
class WeiXinHB {
    public $wxappid;
    public $cmchid;
    public $signKey;

    public function __construct($wxappid = null, $cmchid = null, $signKey = null){
        $this->wxappid = $wxappid;
        $this->mch_id = $cmchid;
        $this->signKey = $signKey;
    }

   /**
    * [sendredpack 发放普通红包]
    * @param  [type] $arr [红包数据(红包金额单位为分)]
    */
    public function sendredpack($arr = array()){
    	$data['nonce_str'] = $this->create_noncestr();
    	$data['mch_billno'] = $arr['order_no'];
        $data['mch_id'] = $this->mch_id;
        $data['wxappid'] = $this->wxappid;
        $data['re_openid'] = $arr['openid'];
        $data['send_name'] = $arr['send_name'];
        $data['total_amount'] = $arr['total_amount']; //付款金额，单位分
        $data['total_num'] = 1;
        $data['client_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['act_name'] = $arr['act_name'];
        $data['remark'] = $arr['remark'];
        $data['wishing'] = $arr['wishing'];
        $data['scene_id'] = $arr['scene_id'] ?: 'PRODUCT_1';

        $data['sign'] = $this->get_sign($data);
        $xml = $this->array_to_xml($data);
        $url ="https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack";
        $re = $this->wxHttpsRequestPem($xml, $url);
        $result = $this->xml_to_array($re);
        if ($result['return_code'] == 'FAIL') {
            $result['result_code'] = 'FAIL';
        }

        return $result;
    }

    /**
     * [transfers 企业付款到零钱]
     * @param [type] $arr  [description]
     */
    public function transfers($arr = array()){
        $data['mch_appid'] = $this->wxappid;
        $data['mchid'] = $this->mch_id;
        $data['nonce_str'] = $this->create_noncestr();
        $data['partner_trade_no'] = $arr['order_no'];
        $data['openid'] = $arr['openid'];
        $data['check_name'] = 'NO_CHECK';
        $data['amount'] = $arr['total_amount']; //付款金额，单位分
        $data['desc'] = $arr['wishing'];
        $data['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $data['sign'] = $this->get_sign($data);
        $xml = $this->array_to_xml($data);
        $url ="https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers";
        $re = $this->wxHttpsRequestPem($xml, $url);
        $result = $this->xml_to_array($re);
        if ($result['return_code'] == 'FAIL') {
            $result['result_code'] = 'FAIL';
        }

        return $result;
    }

    /**
    * 作用：产生随机字符串，不长于32位
    */
    private function create_noncestr( $length = 32 ){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ ) {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }

        return $str;
    }


    /**
    * 作用：格式化参数，签名过程需要使用
    */
    private function formatBizQueryParaMap($paraMap = array(), $urlencode = null){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
            if($urlencode){
                $v = urlencode($v);
            }

            $buff .= $k . "=" . $v . "&";
        }

        $reqPar;
        if (strlen($buff) > 0){
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }

        return $reqPar;
    }


    /**
    * 作用：生成签名
    */
    private function get_sign($Obj = null){
        foreach ($Obj as $k => $v){
            $Parameters[$k] = $v;
        }

        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);

        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$this->signKey; // 商户后台设置的key

        //签名步骤三：MD5加密
        $String = md5($String);

        //签名步骤四：所有字符转为大写
        $result = strtoupper($String);

        return $result;
    }

    /**
    * 作用：array转xml
    */
    private function array_to_xml($arr = array()){
        $xml = "<xml>";
        foreach ($arr as $key=>$val){
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }

        $xml.="</xml>";
        return $xml;
    }

    /**
    * 作用：将xml转为array
    */
    private function xml_to_array($xml = null){
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    private function wxHttpsRequestPem($xml = null, $url = null, $aHeader = array(), $second = 30){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //这里设置代理，如果有的话
        // curl_setopt($ch,CURLOPT_PROXY, '0.0.0.0');
        // curl_setopt($ch,CURLOPT_PROXYPORT, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // 使用证书：cert 与 key 分别属于两个.pem文件
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, dirname(__FILE__).'/cert/apiclient_cert.pem');
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, dirname(__FILE__).'/cert/apiclient_key.pem');
        //curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        //curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).'/cert/rootca.pem');

        if(count($aHeader) >= 1){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);
        }

        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        $data = curl_exec($ch);
        if($data){
            curl_close($ch);
            return $data;
        }else{
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }

}