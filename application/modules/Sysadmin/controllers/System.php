<?php

class SystemController extends Yaf\Controller_Abstract {
	public function init(){
		// 验证用户登录
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		if(!Yaf\Session::getInstance()->has($session_prefix . 'admin_user')){
			$this->forward('Sysadmin','Login','out');
		}
	}

	//系统配置
	public function settingAction(){
		//更新
		if ($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			$result = \Admin\SystemModel::getInstance()->addAll($data);
			json_return($result);
			return false;
		}

		//查看
		$systemAry = \Admin\SystemModel::getInstance()->getAll();
		$this->getView()->assign('system', $systemAry);
	}

}