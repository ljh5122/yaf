<?php

class RoleController extends Yaf\Controller_Abstract {
	public function init(){
		// 验证用户登录
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		if(!Yaf\Session::getInstance()->has($session_prefix . 'admin_user')){
			$this->forward('Sysadmin','Login','out');
		}
	}

	//角色列表
	public function indexAction(){
		if ($this->getRequest()->isXmlHttpRequest()) {
			$page_size = 20;
			$page_code = $this->getRequest()->getParam('page_code');
			$result = \Admin\RoleModel::getInstance()->page($page_code, $page_size);
			json_return($result);
			return false;
		}

		//用户权限
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		$admin_user = Yaf\Session::getInstance()->get($session_prefix . 'admin_user');
		$this->getView()->assign('child_menu', $admin_user['menu'][2]['child'][4]['child']);
	}

	//添加角色
	public function addAction(){
		if ($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			$result = \Admin\RoleModel::getInstance()->create($data);
			json_return($result);
			return false;
		}

		$menuAry = \Admin\MenuModel::getInstance()->getAll('sysadmin');
		$this->getView()->assign('menuAry', menu_merge($menuAry));
	}

	//编辑角色
	public function editAction(){
		if ($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			$result = \Admin\RoleModel::getInstance()->update($data);
			json_return($result);
			return false;
		}

		$data = $this->getRequest()->getParams();
		$result = \Admin\RoleModel::getInstance()->getInfoById($data['id']);
		$result['menu'] = explode(',', $result['menu']);
		$menuAry = \Admin\MenuModel::getInstance()->getAll();

		$this->getView()->assign('info',$result);
		$this->getView()->assign('menuAry', menu_merge($menuAry));
		$this->getView()->display();
	}

	//批量删除角色
	public function batchAction(){
		$data = $this->getRequest()->getPost();
		$result = \Admin\RoleModel::getInstance()->delete($data['id']);
		json_return($result);
		return false;
	}

}