<?php

/**
 * 用户中心接口
 */
class UserController extends Yaf\Controller_Abstract {
	//个人中心数据
	public function infoAction(){
		$data = $this->getRequest()->getPost();
		$userInfo = \Api\UserModel::getInstance()->getInfo($data['openid']);
		
		//消息
		$result['messageCount'] = \Api\UserModel::getInstance()->getNewMessage($userInfo['id']);

		//造型收藏
		$result['modellingCollect'] = \Api\UserModel::getInstance()->getModellingCollect($userInfo['id']);

		//装备收藏
		$result['equipCollect'] = \Api\UserModel::getInstance()->getEquipCollect($userInfo['id']);

		//音乐收藏
		$result['musicCollect'] = \Api\UserModel::getInstance()->getMusicCollect($userInfo['id']);

		json_return($result);
		return false;
	}

	//用户消息记录
	public function messageAction(){
		$data = $this->getRequest()->getPost();
		$userInfo = \Api\UserModel::getInstance()->getInfo($data['openid']);

		$result = \Api\UserModel::getInstance()->getMessage($userInfo['id']);
		json_return($result);
		return false;
	}

	//发送消息
	public function sendMessageAction(){
		$data = $this->getRequest()->getPost();
		$userInfo = \Api\UserModel::getInstance()->getInfo($data['openid']);
		
		$data['sender'] = $userInfo['id'];
		unset($data['openid']);
		$result = \Api\UserModel::getInstance()->sendMessage($data);
		json_return($result);
		return false;
	}

	//收藏音乐列表
	public function collectMusicAction(){
		$data = $this->getRequest()->getPost();
		$userInfo = \Api\UserModel::getInstance()->getInfo($data['openid']);

		$result = \Api\UserModel::getInstance()->getCollectMusic($userInfo['id']);
		json_return($result);
		return false;
	}

	//收藏造型列表
	public function collectModellingAction(){
		$data = $this->getRequest()->getPost();
		$userInfo = \Api\UserModel::getInstance()->getInfo($data['openid']);
		
		$result = \Api\UserModel::getInstance()->getCollectModelling($userInfo['id']);
		json_return($result);
		return false;
	}

	//收藏装备列表
	public function collectEquipAction(){
		$data = $this->getRequest()->getPost();
		$userInfo = \Api\UserModel::getInstance()->getInfo($data['openid']);
		
		$result = \Api\UserModel::getInstance()->getCollectEquip($userInfo['id']);
		json_return($result);
		return false;
	}

	//造型详情
	public function modellingAction(){
		$data = $this->getRequest()->getPost();
		$result = \Api\UserModel::getInstance()->getModelling($data['id']);
		json_return($result);
		return false;
	}
}