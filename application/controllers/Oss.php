<?php

/**
 * 阿里云oss存储
 */
class OssController extends \Yaf\Controller_Abstract {
	//获取oss签名
	public function oss_getAction(){
	    $systemAry = \Api\SystemModel::getInstance()->getAll();
	    $id = $systemAry['oss_AccessKeyID'];
	    $key = $systemAry['oss_AccessKey'];
	    $host = $systemAry['oss_bucket_host'];
	    $dir = '';
	    $callbackUrl = $systemAry['oss_callback'];

	    $callback_param = array(
	    	'callbackUrl'=>$callbackUrl,
	    	'callbackBody'=>'filename=${object}&size=${size}&mimeType=${mimeType}&height=${imageInfo.height}&width=${imageInfo.width}',
	    	'callbackBodyType'=>"application/x-www-form-urlencoded"
	    );

	    $callback_string = json_encode($callback_param);
	    $base64_callback_body = base64_encode($callback_string);
	    $now = time();
	    $expire = 30; //设置该policy超时时间是10s. 即这个policy过了这个有效时间，将不能访问
	    $end = $now + $expire;
	    $expiration = $this->gmt_iso8601($end);

	    //最大文件大小.用户可以自己设置
	    $condition = array(0=>'content-length-range', 1=>0, 2=>1048576000);
	    $conditions[] = $condition;

	    //表示用户上传的数据,必须是以$dir开始, 不然上传会失败,这一步不是必须项,只是为了安全起见,防止用户通过policy上传到别人的目录
	    $start = array(0=>'starts-with', 1=>'$key', 2=>$dir);
	    $conditions[] = $start;


	    $arr = array('expiration'=>$expiration,'conditions'=>$conditions);
	    //echo json_encode($arr);
	    //return;
	    $policy = json_encode($arr);
	    $base64_policy = base64_encode($policy);
	    $string_to_sign = $base64_policy;
	    $signature = base64_encode(hash_hmac('sha1', $string_to_sign, $key, true));

	    $response = array();
	    $response['accessid'] = $id;
	    $response['host'] = $host;
	    $response['policy'] = $base64_policy;
	    $response['signature'] = $signature;
	    $response['expire'] = $end;
	    $response['callback'] = $base64_callback_body;
	    //这个参数是设置用户上传指定的前缀
	    $response['dir'] = date('Y-m/');
	    json_return($response);
		return false;
	}

	// 生成超时时间
	private function gmt_iso8601($time) {
        $dtStr = date("c", $time);
        $mydatetime = new DateTime($dtStr);
        $expiration = $mydatetime->format(DateTime::ISO8601);
        $pos = strpos($expiration, '+');
        $expiration = substr($expiration, 0, $pos);
        return $expiration."Z";
    }

    //oss上传文件
    public function upload_jsonAction(){
		try {
			$systemAry = \Api\SystemModel::getInstance()->getAll();
			$ossClient = new \OSS\OssClient($systemAry['oss_AccessKeyID'], $systemAry['oss_AccessKey'], $systemAry['oss_endPoint']);
			$dir = $this->getRequest()->getPost('fileType') ?: 'media';
			$object = $dir.'/'.date('Y-m/').random_string(15).strchr( $_FILES['imgFile']['name'], '.');
			$bucket = $systemAry['oss_bucket'];
			$content = file_get_contents($_FILES['imgFile']['tmp_name']);
			$result = $ossClient->putObject($bucket, $object, $content);
			json_return(['error' => 0, 'url' => $result['oss-request-url']]);
		} catch (OssException $e) {
			$result = $e->getMessage();
			json_return(['error' => 1, 'message' => $result]);
		}

		return false;
	}

	//oss上传gif文件
    public function upload_gif_jsonAction(){
		try {
			if($_FILES['imgFile']['type'] != 'image/gif'){
				json_return(['error' => 1, 'message' => '请上传gif文件']);
				return false;
			}
			
			$systemAry = \Api\SystemModel::getInstance()->getAll();
			$ossClient = new \OSS\OssClient($systemAry['oss_AccessKeyID'], $systemAry['oss_AccessKey'], $systemAry['oss_endPoint']);
			$dir = $this->getRequest()->getPost('fileType') ?: 'media';
			$object = $dir.'/'.date('Y-m/').random_string(15).strchr( $_FILES['imgFile']['name'], '.');
			$bucket = $systemAry['oss_bucket'];
			$content = file_get_contents($_FILES['imgFile']['tmp_name']);
			$result = $ossClient->putObject($bucket, $object, $content);
			json_return(['error' => 0, 'url' => $result['oss-request-url']]);
		} catch (OssException $e) {
			$result = $e->getMessage();
			json_return(['error' => 1, 'message' => $result]);
		}

		return false;
	}
	
	//oss删除文件
    public function delete_jsonAction(){
		if(!isset($_POST['image']) && !$_POST['image']){
			json_return(['code' => 0, 'msg' => '缺少图片地址']);
		}

		try {
			$systemAry = \Api\SystemModel::getInstance()->getAll();
			$ossClient = new \OSS\OssClient($systemAry['oss_AccessKeyID'], $systemAry['oss_AccessKey'], $systemAry['oss_endPoint']);
			$object = strstr($_POST['image'], 'images') ;
			$ossClient->deleteObject($systemAry['oss_bucket'], $object);
			json_return(['code' => 1, 'msg' => '删除成功']);
		} catch (OssException $e) {
			$result = $e->getMessage();
			json_return(['code' => 0, 'msg' => $result]);
		}

		return false;
    }
}