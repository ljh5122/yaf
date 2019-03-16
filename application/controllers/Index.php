<?php

class IndexController extends \Yaf\Controller_Abstract {
	public function indexAction() {
		$system_conf = \Api\SystemModel::getInstance()->getAll();

		//微信分享
        $jssdkObj = new Wchat\Jssdk($system_conf['wchat_appid'] , $system_conf['wchat_secret']);
        $shareConf = $jssdkObj->getSignPackage();
		$this->assign('share', $shareConf);

		$this->getView()->display();
	}

	public function mapAction(){
		throw new \Exception('错误信息', '10010');
		$this->getView()->display();
	}

	public function testAction(){
		$this->getView()->display();
	}
}