<?php

namespace Api;
use BaseModel;

/**
* 管理员数据对象
*/
class SystemModel extends BaseModel {

	public $table = 'system';

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