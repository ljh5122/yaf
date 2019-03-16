<?php

namespace Admin;
use BaseModel;

/**
* 菜单数据对象
*/
class MenuModel extends BaseModel {

    public $table = 'menu';

	/**
	 * [getAll 获取所有菜单]
	 */
	public function getAll($module = ''){
		$where = $module ? ' module="'.$module.'"' : '';
		$result = $this->db()->where($where)->select();
		return $result;
	}

	/**
	 * [getList 获取菜单列表]
	 * @param  string $where [查询条件]
	 */
	public function getList(string $where = ''){
		$result = $this->db()->where($where)->select();
		return $result;
	}
}