<?php

use \Admin\MiniprogramDataModel;

/**
 * 小程序Api接口
 */
class MiniprogramController extends \Yaf\Controller_Abstract {
	//微信用户登录
	public function wxLoginAction(){
        $data           = $this->getRequest()->getPost();
        $code           = $data['code'];
        $encryptedData  = $data['encryptedData'];
        $iv             = $data['iv'];
        $user_data      = json_decode($data['userInfo'], true);

        //获取小程序配置信息
	    $systemAry = \Api\SystemModel::getInstance()->getAll();
        $appid  =  $systemAry['miniprogram_appid'];
        $secret  =  $systemAry['miniprogram_secret'];

        $URL = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        $apiData = curl_request($URL);
        $data = json_decode($apiData, true);
        if(!isset($data['openid'])){
            json_return(['errmsg' => $apiData]);
        }else{
            //验证用户信息
            $userifo = new \Miniprogram\WXBizDataCrypt($appid, $data['session_key']);
            $errCode = $userifo->decryptData($encryptedData, $iv, $apiData);

            if ($errCode == 0) {
                //保存用户信息
                $user_data['openid'] = $data['openid'];
                $user_data['session_key'] = $data['session_key'];
                $result = \Api\UserModel::getInstance()->getInfo($data['openid']);
                if($result){
                    $result = \Api\UserModel::getInstance()->update($user_data);
                }else{
                    $result = \Api\UserModel::getInstance()->create($user_data);
                }
                
                json_return(['errCode' => 0, 'openid' => $data['openid']]);
            } else {
                json_return(['errCode' => $errCode]);
            }
        }

		return false;
    }

    //获取微信用户手机号
	public function wxPhoneAction(){
        $data           = $this->getRequest()->getPost();
        $openid         = $data['openid'];
        $encryptedData  = $data['encryptedData'];
        $iv             = $data['iv'];

        //获取小程序配置信息
	    $systemAry = \Api\SystemModel::getInstance()->getAll();
        $appid  =  $systemAry['miniprogram_appid'];
        $secret =  $systemAry['miniprogram_secret'];

        //解密encryptedData
        $errCode = $userifo->decryptData($encryptedData, $iv, $apiData);
        if ($errCode == 0) {
            //保存用户信息
            $apiData = json_decode($apiData, true);
            $user_data['phoneNumber'] = $apiData['phoneNumber'];
            $user_data['purePhoneNumber'] = $apiData['purePhoneNumber'];
            $user_data['countryCode'] = $apiData['countryCode'];
            $user_data['openid'] = $openid;
            $result = \Api\UserModel::getInstance()->update($user_data);
            json_return(['errCode' => 0, 'data' => $apiData]);
        } else {
            json_return(['errCode' => $errCode]);
        }

		return false;
    }


    /*******************************小程序数据统计****************************/

    // 获取小程序接口 access_token
    private function get_access_token(){
        $access_token = BaseModel::getInstance()->cache()->get('miniprogram_access_token');
        if(!$access_token){
            $sys_config = \Api\SystemModel::getInstance()->getAll();
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$sys_config['miniprogram_appid']}&secret={$sys_config['miniprogram_secret']}";
            $result = file_get_contents($url);
            $data = json_decode($result, true);
            if(isset($data['errcode'])){
                throw new \Exception('获取小程序access_token失败：'.$data['errmsg'], '30010');
            }

            $access_token = $data['access_token'];
            BaseModel::getInstance()->cache()->set('miniprogram_access_token', $data['access_token'], $data['expires_in'] - 200);
        }
        
        return $access_token;
    }

    //获取用户访问小程序日留存
    public function getAnalysisDailyRetainAction(){
        $access_token = $this->get_access_token();
        $end_date = $begin_date = date('Ymd', strtotime('-1 day'));
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappiddailyretaininfo?access_token={$access_token}";
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($api_result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisDailyRetain接口调用失败：'.$data['errmsg'], '30020');
        }

        $data = ['drange' => 1, 'type' => 1, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        $result = MiniprogramDataModel::getInstance()->create($data);

        return false;
    }

    //获取用户访问小程序数据概况
    public function getAnalysisDailySummaryAction(){
        $access_token = $this->get_access_token();
        $end_date = $begin_date = date('Ymd', strtotime('-1 day'));
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappiddailysummarytrend?access_token={$access_token}";
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisDailySummary接口调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 1, 'type' => 2, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        $result = MiniprogramDataModel::getInstance()->create($data);

        return false;
    }

    //获取用户访问小程序数据日趋势
    public function getAnalysisDailyVisitTrendAction(){
        $access_token = $this->get_access_token();
        $end_date = $begin_date = date('Ymd', strtotime('-1 day'));
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappiddailyvisittrend?access_token={$access_token}";
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisDailyVisitTrend接口调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 1, 'type' => 3, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        $result = MiniprogramDataModel::getInstance()->create($data);

        return false;
    }

    //获取用户访问小程序月留存
    public function getAnalysisMonthlyRetainAction(){
        if(date('d') != 1){
            throw new \Exception('非月初', '10090');
        }

        $access_token = $this->get_access_token();
        $begin_date = date('Ymd', strtotime('-1 month'));
        $end_date = date('Ymd', strtotime('-1 day'));
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappidmonthlyretaininfo?access_token={$access_token}";
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisMonthlyRetain接口调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 3, 'type' => 4, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        $result = MiniprogramDataModel::getInstance()->create($data);

        return false;
    }

    //获取用户访问小程序数据月趋势
    public function getAnalysisMonthlyVisitTrendAction(){
        if(date('d') != 1){
            throw new \Exception('非月初', '10090');
        }

        $access_token = $this->get_access_token();
        $begin_date = date('Ymd', strtotime('-1 month'));
        $end_date = date('Ymd', strtotime('-1 day'));
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappidmonthlyvisittrend?access_token={$access_token}";
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisMonthlyVisitTrend接口调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 3, 'type' => 5, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        $result = MiniprogramDataModel::getInstance()->create($data);

        return false;
    }

    //获取小程序新增或活跃用户的画像分布数据
    public function getAnalysisUserPortraitAction(){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappiduserportrait?access_token={$access_token}";

        //最近1天
        $end_date = $begin_date = date('Ymd', strtotime('-1 day'));
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisUserPortrait接口最近1天调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 1, 'type' => 6, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        MiniprogramDataModel::getInstance()->create($data);

        //最近7天
        $begin_date = date('Ymd', strtotime('-7 day'));
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisUserPortrait接口最近7天调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 2, 'type' => 6, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        MiniprogramDataModel::getInstance()->create($data);

        //最近30天
        $begin_date = date('Ymd', strtotime('-30 day'));
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisUserPortrait接口最近30天调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 3, 'type' => 6, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        MiniprogramDataModel::getInstance()->create($data);

        return false;
    }

    //获取用户小程序访问分布数据
    public function getAnalysisVisitDistributionAction(){
        $access_token = $this->get_access_token();
        $end_date = $begin_date = date('Ymd', strtotime('-1 day'));
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappidvisitdistribution?access_token={$access_token}";
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisVisitDistribution接口调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 1, 'type' => 7, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        $result = MiniprogramDataModel::getInstance()->create($data);

        return false;
    }

    //访问页面。目前只提供按 page_visit_pv 排序的 top200。
    public function getAnalysisVisitPageAction(){
        $access_token = $this->get_access_token();
        $end_date = $begin_date = date('Ymd', strtotime('-1 day'));
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappidvisitpage?access_token={$access_token}";
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisVisitPage接口调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 1, 'type' => 8, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        $result = MiniprogramDataModel::getInstance()->create($data);

        return false;
    }

    //获取用户访问小程序周留存
    public function getAnalysisWeeklyRetainAction(){
        if(date('w') != 1){
            throw new \Exception('非周一', '10090');
        }
        
        $access_token = $this->get_access_token();
        $begin_date = date('Ymd', strtotime('-1 week'));
        $end_date = date('Ymd', strtotime('-1 day'));
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappidweeklyretaininfo?access_token={$access_token}";
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisWeeklyRetain接口调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 2, 'type' => 9, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        $result = MiniprogramDataModel::getInstance()->create($data);
        
        return false;
    }

    //获取用户访问小程序数据周趋势
    public function getAnalysisWeeklyVisitTrendAction(){
        if(date('w') != 1){
            throw new \Exception('非周一', '10090');
        }
        
        $access_token = $this->get_access_token();
        $begin_date = date('Ymd', strtotime('-1 week'));
        $end_date = date('Ymd', strtotime('-1 day'));
        $url = "https://api.weixin.qq.com/datacube/getweanalysisappidweeklyvisittrend?access_token={$access_token}";
        $params = json_encode(['begin_date' => $begin_date, 'end_date' => $end_date]);
        $api_result = curl_request($url, $params);
        $data = json_decode($result, true);
        if(isset($data['errcode'])){
            throw new \Exception('getAnalysisWeeklyVisitTrend接口调用失败：'.$data['errmsg'], '30020');
        }
        
        $data = ['drange' => 2, 'type' => 10, 'content' => $api_result, 'begin_date' => $begin_date, 'end_date' => $end_date];
        $result = MiniprogramDataModel::getInstance()->create($data);

        return false;
    }
}