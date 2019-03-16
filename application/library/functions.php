<?php

// 应用公共文件

//菜单继归函数
function menu_merge($colarr,$pid=0){
	$arr = array();
	foreach( $colarr as $k => $v) {
		if ($v['pid'] == $pid) {
			$v['child'] = menu_merge($colarr, $v['id'] );
			$arr[$k] = $v;
		}
	}
	return $arr;
}

//菜单继归显示函数
function menu_merge_html($colarr,$pid=0,$html='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$level=0){
	$arr = array();
	$temp = array();
	foreach($colarr as $v) {
		if ($v['pid'] == $pid) {
			$v['level'] = $level+1;
			$v['html'] = str_repeat($html, $level);
			$temp = menu_merge_html($colarr, $v['id'], $html, $v['level']);
			$v['child'] = $temp ? 1 : 0;
			$arr[] = $v;
			$arr = array_merge($arr,$temp);
		}
	}
	return $arr;
}

//当前菜单和所属菜单id
function menus_merge($colarr,$pid=0){
	$str = $pid;
	foreach( $colarr as $v) {
		if ($v['pid'] == $pid) {
			$str .= ','.menus_merge($colarr, $v['id']);
		}
	}
	return $str;
}

//跳转到框架的最外层
function reload($url = '/sysadmin/login/index'){
	echo '<script>top.location.href="'.$url.'";</script>';
}

/*移动端判断*/
function isMobile()
{ 
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return true;
    } 
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    { 
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    } 
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
            ); 
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return true;
        } 
    } 
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    { 
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return true;
        } 
    } 
    return false;
} 

//自定义分页样式
function my_page($page_code, $page_size, $count){
    $page_num = ceil($count/$page_size) ?: 1;
    $html = '<ul class="pager"><li>共 '.$count.' 条</li>';

    if ($page_code == 1) {
        $html .= '<li class="previous active"><a href="#">1</a></li>';
    }else{
        $html .= '<li class="previous"><a href="#">1</a></li>';
    }

    $i = $page_code > 10 ? $page_code - 9 : 2;
    $page_end = $page_num - $page_code > 10 ? $page_code + 9 : $page_num;

    for($i; $i < $page_end; $i++){
        if ($page_code == $i) {
            $html .= '<li class="active"><a href="#">'.$i.'</a></li>';
        }else{
            $html .= '<li><a href="#">'.$i.'</a></li>';
        }
    }

    if ($page_code == $page_num) {
        if($page_num != 1){
            $html .= '<li class="next active"><a href="#">'.$page_num.'</a></li></ul>';
        }
    }else{
        $html .= '<li class="next"><a href="#">'.$page_num.'</a></li></ul>';
    }

    return $html;
}

/**
 * [https_request http/https请求函数]
 * @param  string $url  [请求地址]
 * @param  [type] $data [请求数据]
 */
function curl_request($url = '', $data = null){
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if(!empty($data)){
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }

    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

// 特殊字符过滤
function filterEmoji($str){
    $str = preg_replace_callback( '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
    $str);

    return $str;
}

//获取随机字符串
function random_string($length = 1){
    $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $result = '';
    for($i = 0; $i < $length; $i++){
        $result .= $str[rand(0, 61)];
    }

    return $result;
}

// 返回json结果集
function json_return(array $result = []){
    header("Content-Type:application/json;charset=utf8");
    echo json_encode($result);
}


/**
 * 过滤敏感词(将敏感词替换成"*"号)
 * @param array $bad_words_arr [敏感词数组]
 * @param string $replace_str [要处理的字符串]
 **/
function replaceBadWords($replace_str){
    $bad_words_arr = require_once 'badword.php';
    if(!is_array($bad_words_arr) || empty($bad_words_arr)){
        return $replace_str;
    }

    $null_arr = array_fill(0, count($bad_words_arr), '*'); //生成一个只有"*"元素的数组
    $replace_arr = array_combine($bad_words_arr, $null_arr); //合并数组

    return strtr($replace_str, $replace_arr);
}