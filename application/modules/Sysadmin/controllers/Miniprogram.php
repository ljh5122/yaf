<?php

/**
 * 小程序数据统计
 */
class MiniprogramController extends Yaf\Controller_Abstract {
	public function init(){
		// 验证用户登录
		$session_prefix = Yaf\Registry::get('config')->session->prefix;
		if(!Yaf\Session::getInstance()->has($session_prefix . 'admin_user')){
			$this->forward('Sysadmin','Login','out');
		}
	}

	// 小程序日统计数据查询
	public function trendAction(){
		if($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			$result = \Admin\MiniprogramDataModel::getInstance()->getDaily($data);
			json_return($result);
			return false;
		}

		$this->getView()->display();
	}

	// 小程序当日页面访问数据查询
	public function pageAction(){
		if($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			$result = \Admin\MiniprogramDataModel::getInstance()->getAnalysisVisitPage($data);
			json_return($result);
			return false;
		}

		$this->getView()->assign('end_date', date('Y-m-d', strtotime('-1 day')));
	}

	// 小程序当日访问分布数据查询
	public function distributionAction(){
		if($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			$result = \Admin\MiniprogramDataModel::getInstance()->getAnalysisVisitDistribution($data);
			json_return($result);
			return false;
		}

		$this->getView()->assign('end_date', date('Y-m-d', strtotime('-1 day')));
	}

	// 获取小程序新增或活跃用户的画像分布数据
	public function portraitAction(){
		if($this->getRequest()->isXmlHttpRequest()){
			$data = $this->getRequest()->getPost();
			$result = \Admin\MiniprogramDataModel::getInstance()->getAnalysisUserPortrait($data);
			json_return($result);
			return false;
		}

		$this->getView()->display();
	}

	// 小程序日统计数据查询
	public function dailyAction(){
		$data = $this->getRequest()->getPost();
		$result = \Admin\MiniprogramDataModel::getInstance()->getDaily($data);
		json_return($result);
		return false;
	}

	// 小程序当日页面访问数据查询
	public function visitPageAction(){
		$data = $this->getRequest()->getPost();
		$result = \Admin\MiniprogramDataModel::getInstance()->getAnalysisVisitPage($data);
		json_return($result);
		return false;
	}

	// 小程序当日访问分布数据查询
	public function visitDistributionAction(){
		$data = $this->getRequest()->getPost();
		$result = \Admin\MiniprogramDataModel::getInstance()->getAnalysisVisitDistribution($data);
		json_return($result);
		return false;
	}

	// 获取小程序新增或活跃用户的画像分布数据
	public function userPortraitAction(){
		$data = $this->getRequest()->getPost();
		$result = \Admin\MiniprogramDataModel::getInstance()->getAnalysisUserPortrait($data);
		json_return($result);
		return false;
	}
}