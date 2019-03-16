<?php

/**
 * 后台登录
 */
class LoginController extends Yaf\Controller_Abstract {
	public function init(){
		// 验证用户登录
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		if(Yaf\Session::getInstance()->has($session_prefix . 'admin_user')){
			$this->redirect('/sysadmin/Index/index');
		}
	}

	// 登录页面
	public function indexAction(){
		$this->getView()->display();
	}

	//验证码
	public function captchaAction(){
		$Captcha = new Captcha\Image(130, 40, 4);
		$session_prefix = \Yaf\Registry::get('config')->session->prefix;
		\Yaf\Session::getInstance()->set($session_prefix . 'admin_captcha', $Captcha->getCode());
		return $Captcha->printImage();
	}

	//执行登录
	public function loginAction(){
		if ($this->getRequest()->isXmlHttpRequest()) {
			$data = $this->getRequest()->getPost();
			$result = \Admin\AdminModel::getInstance()->login($data);
			json_return($result);
			return false;
		}
	}

	//登录会话超时
	public function outAction(){
		if($this->getRequest()->isXmlHttpRequest()){
			json_return(['code' => -1, 'msg' => '会话超时，请重新登录']);
		}else{
			reload('/sysadmin/login/index');
		}

		return false;
	}

	//超时重新登录
	public function reloginAction(){
		if ($this->getRequest()->isXmlHttpRequest()) {
			$data = $this->getRequest()->getPost();
			$result = \Admin\AdminModel::getInstance()->relogin($data);
			json_return($result);
			return false;
		}
	}
}