<?php

class IndexController extends Yaf\Controller_Abstract {
	public function init(){
		// 验证用户登录
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		if(!Yaf\Session::getInstance()->has($session_prefix . 'admin_user')){
			$this->forward('Sysadmin','Login','out');
		}
	}

	// 后台首页
	public function indexAction(){
		$session_prefix = \Yaf\Registry::get('config')->session->prefix;
		$admin_user = \Yaf\Session::getInstance()->get($session_prefix . 'admin_user');
		$this->getView()->assign('admin', $admin_user);
	}

	// 系统主页
	public function mainAction(){
		//获取最近30天用户登录数曲线图数据
		$result = \Admin\UserModel::getInstance()->getLoginCount();
		$this->getView()->assign('chart', $result);

		//获取用户性别饼状图数据
		$myPieChart1 = \Admin\UserModel::getInstance()->getGenderCount();
		$this->getView()->assign('myPieChart1', $myPieChart1);

		//获取造型饼状图数据
		$myPieChart2 = \Admin\ModellingModel::getInstance()->getStatusCount();
		$this->getView()->assign('myPieChart2', $myPieChart2);

		//获取装备饼状图数据
		$myPieChart3 = \Admin\EquipModel::getInstance()->getStatusCount();
		$this->getView()->assign('myPieChart3', $myPieChart3);

		//获取资讯饼状图数据
		$myPieChart4 = \Admin\ArticleModel::getInstance()->getStatusCount();
		$this->getView()->assign('myPieChart4', $myPieChart4);
	}

	//编辑管理员
	public function passwordAction(){
		if ($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			json_return(\Admin\AdminModel::getInstance()->update($data));
			return false;
		}
	}

	// 小程序日统计数据查询
	public function miniprogramDailyAction(){
		$data = $this->getRequest()->getPost();
		$result = \Admin\MiniprogramDataModel::getInstance()->getDaily($data);
		json_return($result);
		return false;
	}

	// 小程序当日页面访问数据查询
	public function miniprogramVisitPageAction(){
		$data = $this->getRequest()->getPost();
		$result = \Admin\MiniprogramDataModel::getInstance()->getAnalysisVisitPage($data);
		json_return($result);
		return false;
	}

	// 小程序当日访问分布数据查询
	public function miniprogramVisitDistributionAction(){
		$data = $this->getRequest()->getPost();
		$result = \Admin\MiniprogramDataModel::getInstance()->getAnalysisVisitDistribution($data);
		json_return($result);
		return false;
	}

	// 获取小程序新增或活跃用户的画像分布数据
	public function miniprogramUserPortraitAction(){
		$data = $this->getRequest()->getPost();
		$result = \Admin\MiniprogramDataModel::getInstance()->getAnalysisUserPortrait($data);
		json_return($result);
		return false;
	}

	// 退出系统
	public function quitAction(){
		$session_prefix = \Yaf\Registry::get('config')->session->prefix;
		$admin_user = \Yaf\Session::getInstance()->del($session_prefix . 'admin_user');
		$this->redirect('/sysadmin/Login/index');
	}
}