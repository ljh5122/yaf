<?php

/**
 * 会员管理
 */
class UserController extends Yaf\Controller_Abstract {
	public function init(){
		// 验证用户登录
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		if(!Yaf\Session::getInstance()->has($session_prefix . 'admin_user')){
			$this->forward('Sysadmin','Login','out');
		}
	}

	//列表
	public function indexAction(){
		if ($this->getRequest()->isXmlHttpRequest()) {
			$page_size = 20;
			$page_code = $this->getRequest()->getParam('page_code');
			$data = $this->getRequest()->getPost();
			$result = \Admin\UserModel::getInstance()->page($page_code, $page_size, $data);
			json_return($result);
			return false;
		}

		//用户权限
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		$admin_user = Yaf\Session::getInstance()->get($session_prefix . 'admin_user');
		$this->getView()->assign('child_menu', $admin_user['menu'][14]['child'][15]['child']);
	}

	//发送消息
	public function messageAction(){
		if ($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			$result = \Admin\MessageModel::getInstance()->sendMessage($data);
			json_return($result);
			return false;
		}

		//用户信息
		$user_id = $this->getRequest()->getParam('id');
		$result = \Admin\UserModel::getInstance()->getInfoById($user_id);
		$this->getView()->assign('info',$result);

		// 消息记录
		$message = \Admin\MessageModel::getInstance()->getMessage($user_id);
		$this->getView()->assign('messageAry', $message);
	}

	//批量操作
	public function batchAction(){
		$type = $this->getRequest()->getParam('type');
		$data = $this->getRequest()->getPost();
		switch ($type) {
			case 'using':
				$result = \Admin\UserModel::getInstance()->using($data['id']);
				break;

			case 'forbid':
				$result = \Admin\UserModel::getInstance()->forbid($data['id']);
				break;
			
			default:
				$result = ['code' => 0, 'msg' => '操作项不存在！'];
				break;
		}

		json_return($result);
		return false;
	}

}