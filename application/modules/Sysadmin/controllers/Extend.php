<?php

/**
 * 扩展管理
 */
class ExtendController extends Yaf\Controller_Abstract {
	public function init(){
		// 验证用户登录
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		if(!Yaf\Session::getInstance()->has($session_prefix . 'admin_user')){
			$this->forward('Sysadmin','Login','out');
		}
	}

	//广告列表
	public function advertIndexAction(){
		if ($this->getRequest()->isXmlHttpRequest()) {
			$page_size = 20;
			$page_code = $this->getRequest()->getParam('page_code');
			$data = $this->getRequest()->getPost();
			$result = \Admin\AdvertModel::getInstance()->page($page_code, $page_size, $data);
			json_return($result);
			return false;
		}

		//用户权限
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		$admin_user = Yaf\Session::getInstance()->get($session_prefix . 'admin_user');
		$this->getView()->assign('child_menu', $admin_user['menu'][65]['child'][66]['child']);
	}

	//广告新增
	public function advertAddAction(){
		if ($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			$result = \Admin\AdvertModel::getInstance()->create($data);
			json_return($result);
			return false;
		}

		$this->getView()->display();
	}

	//广告编辑
	public function advertEditAction(){
		if ($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			$result = \Admin\AdvertModel::getInstance()->update($data);
			json_return($result);
			return false;
		}

		$id = $this->getRequest()->getParam('id');
		$result = \Admin\AdvertModel::getInstance()->getInfoById($id);
		$this->getView()->assign('info',$result);
	}

	//广告批量操作
	public function advertBatchAction(){
		$type = $this->getRequest()->getParam('type');
		$data = $this->getRequest()->getPost();
		switch ($type) {
			case 'using':
				$result = \Admin\AdvertModel::getInstance()->using($data['id']);
				break;

			case 'forbid':
				$result = \Admin\AdvertModel::getInstance()->forbid($data['id']);
				break;

			case 'delete':
				$result = \Admin\AdvertModel::getInstance()->delete($data['id']);
				break;
			
			default:
				$result = ['code' => 0, 'msg' => '操作项不存在！'];
				break;
		}

		json_return($result);
		return false;
	}

}