<?php

namespace Admin;
use BaseModel;

/**
* 管理员数据对象
*/
class SystemModel extends BaseModel {
	
	public $table = 'system';

	// 插入所有配置
	public function addAll(array $data = []){
		$result = $this->db()->where('type=1')->delete();
		if (!$result) {
			return ['code' => 0, 'msg' => '操作失败'];
		}

		$sys_config = [];
		foreach ($data as $key => $value) {
			$sys_config[] = ['key' => $key, 'value' => $value, 'type' => 1];
		}

		$result = $this->db()->insertAll($sys_config);
		if($result){
			$this->cache()->rm('sys_config');
			return ['code' => 1, 'msg' => '操作成功'];
		}

		return ['code' => 0, 'msg' => '操作失败'];
	}

	// 获取所有配置
	public function getAll(){
		$sys_config = $this->cache()->get('sys_config');
		if(!$sys_config){
			$result = $this->db()->select();
			$sys_config = [];
			foreach ($result as $value) {
				$sys_config[$value['key']] = $value['value'];
			}
			
			$this->cache()->set('sys_config', $sys_config);

		}

        return $sys_config;
	}
}