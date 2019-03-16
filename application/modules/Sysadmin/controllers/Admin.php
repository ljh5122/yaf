<?php

class AdminController extends Yaf\Controller_Abstract {
	public function init(){
		// 验证用户登录
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		if(!Yaf\Session::getInstance()->has($session_prefix . 'admin_user')){
			$this->forward('Sysadmin','Login','out');
		}
	}

	//管理员列表
	public function indexAction(){
		if($this->getRequest()->isXmlHttpRequest()){
			$page_size = 20;
			$page_code = $this->getRequest()->getParam('page_code');
			$data = $this->getRequest()->getPost();
			$result = \Admin\AdminModel::getInstance()->page($page_code, $page_size, $data);
			json_return($result);
			return false;
		}

		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		$admin_user = Yaf\Session::getInstance()->get($session_prefix . 'admin_user');
		$this->getView()->assign('child_menu', $admin_user['menu'][2]['child'][5]['child']);
		$this->getView()->assign('role', \Admin\RoleModel::getInstance()->getAll());
	}

	//添加管理员
	public function addAction(){
		if ($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			json_return(\Admin\AdminModel::getInstance()->create($data));
			return false;
		}
	}

	//编辑管理员
	public function editAction(){
		if ($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			json_return(\Admin\AdminModel::getInstance()->update($data));
			return false;
		}
	}

	//管理员批量操作
	public function batchAction(){
		$type = $this->getRequest()->getParam('type');
		$data = $this->getRequest()->getPost();
		switch ($type) {
			case 'using':
				$result = \Admin\AdminModel::getInstance()->using($data['id']);
				break;

			case 'forbid':
				$result = \Admin\AdminModel::getInstance()->forbid($data['id']);
				break;

			case 'delete':
				$result = \Admin\AdminModel::getInstance()->delete($data['id']);
				break;
			
			default:
				$result = ['code' => 0, 'msg' => '操作项不存在！'];
				break;
		}

		json_return($result);
		return false;
	}

}