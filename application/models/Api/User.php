<?php

namespace Api;
use BaseModel;
use \Api\MessageModel;
use \Api\ModellingCollectModel;
use \Api\EquipCollectModel;
use \Api\MusicCollectModel;
use \Api\MusicModel;
use \Api\ModellingModel;
use \Api\EquipModel;

/**
* 会员数据对象
*/
class UserModel extends BaseModel {

	public $table = 'user';

	/**
	 * 创建
	 */
	public function create($data = []){
		if (empty($data['openid'])){
			return ['code' => 0, 'msg' => 'openid不能为空！'];
		}

		$data['utime'] = time();
		$result = $this->db()->insert($data);
		if ($result) {
			return ['code' => 1, 'msg' => '创建成功！'];
		}

		return ['code' => 0, 'msg' => '创建失败！'];
	}

	/**
	 * 编辑
	 */
	public function update($data = []){
		if (empty($data['openid'])){
			return ['code' => 0, 'msg' => 'openid不能为空！'];
		}

		$data['utime'] = time();
		$result = $this->db()->where('openid="'.$data['openid'].'"')->update($data);
		if ($result) {
			return ['code' => 1, 'msg' => '编辑成功！'];
		}
		
		return ['code' => 0, 'msg' => '编辑失败！'];
	}

	/**
	 * 消息记录
	 */
	public function getMessage($userID = 0){
		$result = MessageModel::getInstance()->db()->where('sender='.$userID .' or sendee='.$userID)->order('id asc')->select();
		if($result){
			//修改新消息状态为已阅读
			$message_ids = '';
			foreach($result as $key => $value){
				if($value['sendee'] == $userID && $value['status'] == 1){
					$message_ids .= $value['id'].',';
				}
			}

			if($message_ids){
				$message_ids = substr($message_ids, 0, -1);
				MessageModel::getInstance()->db()->where('id in('.$message_ids.')')->update(['status' => 2]);
			}
		}else{
			$result = ['code' => 0, 'msg' => '没有消息记录'];
		}
		
		return $result;
	}

	/**
	 * 发送消息
	 */
	public function sendMessage($data = []){
		if (empty($data['content'])){
			return ['code' => 0, 'msg' => '消息内容不能为空！'];
		}

		$data['sendee'] = 0;
		$data['utime'] = time();
		$result = MessageModel::getInstance()->db()->insert($data);
		if ($result) {
			return ['code' => 1, 'msg' => '发送成功！'];
		}

		return ['code' => 0, 'msg' => '发送失败！'];
	}

	/**
	 * 用户新消息数量
	 */
	public function getNewMessage($userID = 0){
		$result = MessageModel::getInstance()->db()->where('sendee='.$userID.' and status=1')->count();
		return $result;
	}

	/**
	 * 获取用户详情
	 */
	public function getInfo( $openid = ''){
		$result = $this->db()->where('openid="'.$openid.'"')->row();
		return $result;
	}

	/**
	 * 用户造型收藏数量
	 */
	public function getModellingCollect(int $userID = 0){
		$result = ModellingCollectModel::getInstance()->db()->where('user='.$userID)->count();
		return $result;
	}

	/**
	 * 用户造型收藏列表
	 */
	public function getCollectModelling(int $userID = 0){
		$modelling_result = ModellingCollectModel::getInstance()->db()->where('user='.$userID)->order('id desc')->select('modelling');
		$result = ['code' => 0, 'msg' => '没有收藏内容'];
		if($modelling_result){
			$modelling = implode(',', array_column($modelling_result, 'modelling'));
			$result = ModellingModel::getInstance()->db()->where('id in('.$modelling.')')->select('id,title,subhead,background,thumb,equip');
			foreach($result as $key => $value){
				$result[$key]['equip'] = EquipModel::getInstance()->db()->where('id in('.$value['equip'].')')->select('id,thumb');
			}
		}

		return $result;
	}

	/**
	 * 造型详情
	 */
	public function getModelling(int $modelling = 0){
		if ($modelling == 0) {
			return ['code' => 0, 'msg' => '缺少造型ID'];
		}

		$result = ModellingModel::getInstance()->db()->where('id='.$modelling)->row('id,title,titlecolor,subhead,thumb,thumb2,video,equip,collect,background');
		$result['equip'] = EquipModel::getInstance()->db()->where('id in('.$result['equip'].')')->select('id,thumb');

		return $result;
	}

	/**
	 * 用户装备收藏数量
	 */
	public function getEquipCollect(int $userID = 0){
		$result = EquipCollectModel::getInstance()->db()->where('user='.$userID)->count();
		return $result;
	}

	/**
	 * 用户装备收藏列表
	 */
	public function getCollectEquip(int $userID = 0){
		$equip_result = EquipCollectModel::getInstance()->db()->where('user='.$userID)->order('id desc')->select('equip');
		$result = ['code' => 0, 'msg' => '没有收藏内容'];
		if($equip_result){
			$equip = implode(',', array_column($equip_result, 'equip'));
			$result = EquipModel::getInstance()->db()->where('id in('.$equip.')')->select('id,title,thumb,category');
		}
		
		return $result;
	}

	/**
	 * 用户音乐收藏数量
	 */
	public function getMusicCollect(int $userID = 0){
		$result = MusicCollectModel::getInstance()->db()->where('user='.$userID)->count();
		return $result;
	}

	/**
	 * 用户音乐收藏列表
	 */
	public function getCollectMusic(int $userID = 0){
		$music_result = MusicCollectModel::getInstance()->db()->where('user='.$userID)->order('id desc')->select('music');
		$result = ['code' => 0, 'msg' => '没有收藏内容'];
		if($music_result){
			$music = implode(',', array_column($music_result, 'music'));
			$result = MusicModel::getInstance()->db()->where('id in('.$music.')')->select('id,title,thumb');
		}

		return $result;
	}
}