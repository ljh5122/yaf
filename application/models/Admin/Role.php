<?php

namespace Admin;
use BaseModel;
use \Admin\AdminModel;

/**
* 角色数据对象
*/
class RoleModel extends BaseModel {
	
	public $table = 'role';

	/**
	 * [getInfoById 创建角色]
	 */
	public function create($data = []){
		if (empty($data['name'])){
			return ['code' => 0, 'msg' => '角色名称不能为空！'];
		}

		$session_prefix = \Yaf\Registry::get('config')->session->prefix;
		$admin_user = \Yaf\Session::getInstance()->get($session_prefix . 'admin_user');
		$data['menu'] = implode(',', $data['menu']);
		$data['updater'] = $admin_user['id'];
		$data['utime'] = time();
		$result = $this->db()->insert($data);
		if ($result) {
			return ['code' => 1, 'msg' => '创建成功！'];
		}

		return ['code' => 0, 'msg' => '创建失败！'];
	}

	/**
	 * [getInfoById 编辑角色]
	 */
	public function update($data = []){
		if (empty($data['name'])){
			return ['code' => 0, 'msg' => '角色名称不能为空！'];
		}

		$session_prefix = \Yaf\Registry::get('config')->session->prefix;
		$admin_user = \Yaf\Session::getInstance()->get($session_prefix . 'admin_user');
		$data['menu'] = implode(',', $data['menu']);
		$data['updater'] = $admin_user['id'];
		$data['utime'] = time();
		$data['status'] = 2;

		$result = $this->db()->where('id='.$data['id'])->update($data);
		if ($result) {
			return ['code' => 1, 'msg' => '编辑成功！'];
		}

		return ['code' => 0, 'msg' => '编辑失败！'];
	}

	/**
	 * [getInfoById 删除角色]
	 */
	public function delete($data = []){
		if (empty($data)){
			return ['code' => 0, 'msg' => '请选择操作项！'];
		}

		$ids = implode(',', $data);
		if(AdminModel::getInstance()->db()->where('role in('.$ids.')')->row()){
			return ['code' => 0, 'msg' => '角色已被使用'];
		}

		$result = $this->db()->where('id in('.$ids.')')->delete();
		if ($result) {
			return ['code' => 1, 'msg' => '删除角色成功！'];
		}

		return ['code' => 0, 'msg' => '删除角色失败！'];
	}

	/**
	 * [getInfoById 根据ID获取角色信息]
	 * @param  int|integer $id [角色ID]
	 */
	public function getInfoById(int $id = 0){
		$result = $this->db()->where('id='.$id)->row();
		return $result;
	}

	/**
	 * [getInfoByName 获取所有角色]
	 */
	public function getAll(){
		$result = $this->db()->select();
		return $result;
	}
	
	// 获取分页记录
	public function page($page = 1, $size = 10){
		$result['count'] = $this->db()->count();
		$result['list'] = [];
		if($result['count']){
			$result['list'] = $this->db()->order('id desc')->page($page, $size)->select();
			foreach($result['list'] as $key => $value){
				$updater = \Admin\AdminModel::getInstance()->db()->where('id='.$value['updater'])->row('nickName');
				$result['list'][$key]['updater'] = $updater['nickName'];
			}
		}
		
		$result['page'] = my_page($page, $size, $result['count']);
		return $result;
	}
}