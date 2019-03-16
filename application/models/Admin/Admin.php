<?php

namespace Admin;
use BaseModel;

/**
* 管理员数据对象
*/
class AdminModel extends BaseModel{

	public $table = 'admin';

	/**
	 * [getInfoById 创建管理员]
	 */
	public function create($data = []){
		if (empty($data['name'])){
			return ['code' => 0, 'msg' => '管理员名称不能为空！'];
		}

		if(empty($data['password'])){
			unset($data['password']);
		}else{
			if ($data['password'] != $data['password2']) {
				return ['code' => 0, 'msg' => '两次密码不一致！'];
			}

			$data['password'] = md5($data['password']);
		}

		unset($data['password2']);
		unset($data['id']);
		$session_prefix = \Yaf\Registry::get('config')->session->prefix;
		$admin_user = \Yaf\Session::getInstance()->get($session_prefix . 'admin_user');
		$data['updater'] = $admin_user['id'];
		$data['utime'] = time();
		
		$result = $this->db()->insert($data);
		if ($result) {
			return ['code' => 1, 'msg' => '创建管理员成功！'];
		}

		return ['code' => 0, 'msg' => '创建管理员失败！'];
	}

	/**
	 * [getInfoById 编辑管理员]
	 */
	public function update($data = []){
		if (empty($data['id'])){
			return ['code' => 0, 'msg' => '管理员不能为空！'];
		}

		if (empty($data['name'])){
			return ['code' => 0, 'msg' => '管理员名称不能为空！'];
		}

		if(empty($data['password'])){
			unset($data['password']);
		}else{
			if ($data['password'] != $data['password2']) {
				return ['code' => 0, 'msg' => '两次密码不一致！'];
			}

			$data['password'] = md5($data['password']);
		}

		unset($data['password2']);
		$session_prefix = \Yaf\Registry::get('config')->session->prefix;
		$admin_user = \Yaf\Session::getInstance()->get($session_prefix . 'admin_user');
		$data['updater'] = $admin_user['id'];
		$data['utime'] = time();
		$data['status'] = 2;

		$result = $this->db()->where('id='.$data['id'])->update($data);
		if ($result) {
			return ['code' => 1, 'msg' => '编辑管理员成功！'];
		}
		
		return ['code' => 0, 'msg' => '编辑管理员失败！'];
	}

	/**
	 * [getInfoById 删除管理员]
	 */
	public function delete($ids = []){
		if (empty($ids)){
			return ['code' => 0, 'msg' => '请选择管理员！'];
		}

		$ids = implode(',', $ids);
		$result = $this->db()->where('id in('.$ids.')')->delete();
		if ($result) {
			return ['code' => 1, 'msg' => '删除成功！'];
		}

		return ['code' => 0, 'msg' => '删除失败！'];
	}

	/**
	 * [getInfoById 启用管理员]
	 */
	public function using($ids = []){
		if (empty($ids)){
			return ['code' => 0, 'msg' => '管理员不能为空！'];
		}

		$data['status'] = 1;
		$ids = implode(',', $ids);
		$result = $this->db()->where('id in('.$ids.')')->update($data);
		if ($result) {
			return ['code' => 1, 'msg' => '启用成功！'];
		}

		return ['code' => 0, 'msg' => '启用失败！'];
	}

	/**
	 * [getInfoById 禁用管理员]
	 */
	public function forbid($ids = []){
		if (empty($ids)){
			return ['code' => 0, 'msg' => '管理员不能为空！'];
		}

		$data['status'] = 2;
		$ids = implode(',', $ids);
		$result = $this->db()->where('id in('.$ids.')')->update($data);
		if ($result) {
			return ['code' => 1, 'msg' => '禁用成功！'];
		}

		return ['code' => 0, 'msg' => '禁用失败！'];
	}

	/**
	 * [getInfoById 根据ID获取管理员信息]
	 * @param  int|integer $id [管理员ID]
	 */
	public function getInfoById(int $id = 0){
		$result = $this->db()->where('id='.$id)->row();
		return $result;
	}

	// 获取分页记录
	public function page($page = 1, $size = 10, $data = []){
		$data['name'] = trim($data['name']);
		$where = 'super=0';
		$where .= $data['name'] ? ' and name like "%'.$data['name'].'%"' : '';
		$where .= $data['status'] ? ' and status = '.$data['status'] : '';
		$result['count'] = $this->db()->where($where)->count();
		$result['list'] = [];
		if($result['count']){
			$result['list'] = $this->db()->where($where)->order('id desc')->page($page, $size)->select();
			foreach($result['list'] as $key => $value){
				//编辑者
				$updater = \Admin\AdminModel::getInstance()->db()->where('id='.$value['updater'])->row('nickName');
				$result['list'][$key]['updater'] = $updater['nickName'];

				//角色名称
				$role = \Admin\RoleModel::getInstance()->db()->where('id='.$value['role'])->row('name');
				$result['list'][$key]['roleName'] = $role['name'];
			}
		}
		
		$result['page'] = my_page($page, $size, $result['count']);
		return $result;
	}

	/**
	 * [login 管理员登录]
	 * @param  [type] $data [表单数据]
	 */
	public function login($data = []){
		if (!isset($data['captcha']) || empty($data['captcha'])) {
			return ['code' => 0, 'msg' => '验证码不能为空'];
		}

		if (!isset($data['user_name']) || empty($data['user_name'])) {
			return ['code' => 0, 'msg' => '用户名不能为空'];
		}

		if (!isset($data['user_pwd']) || empty($data['user_pwd'])) {
			return ['code' => 0, 'msg' => '密码不能为空'];
		}

		$session_prefix = \Yaf\Registry::get('config')->session->prefix;
		$session = \Yaf\Session::getInstance();
		$captcha = $session->get($session_prefix . 'admin_captcha');
		if (strtolower($captcha) !== strtolower($data['captcha'])) {
			return ['code' => 0, 'msg' => '验证码不可用'];
		}

		$result = $this->db()->where('name="'.$data['user_name'].'"')->row();
		if (!$result || md5($data['user_pwd']) != $result['password']) {
			return ['code' => 0, 'msg' => '帐户或密码错误'];
		}

		// 获取管理员权限
		if ($result['super']) {
			$menu_res = \Admin\MenuModel::getInstance()->getAll('sysadmin');
		}else{
			$role = \Admin\RoleModel::getInstance()->getInfoById($result['role']);
			$menu_res = \Admin\MenuModel::getInstance()->getList('id in('.$role['menu'].')');
		}

		if (!$menu_res) {
			return ['code' => 0, 'msg' => '没有获取到管理员权限'];
		}

		//保存管理员会话
		$menu_array = [];
		foreach($menu_res as $key => $value){
			$menu_array[$value['id']] = $value; 
		}

		$result['menu'] = menu_merge($menu_array);
		$session->set($session_prefix . 'admin_user', $result);

		// 删除验证码
		$session->del($session_prefix . 'admin_captcha');

		return ['code' => 1, 'msg' => '登录成功'];
	}

	/**
	 * [relogin 超时重新登录]
	 * @param  [type] $data [表单数据]
	 */
	public function relogin($data = []){
		if (!isset($data['user_name']) || empty($data['user_name'])) {
			return ['code' => 0, 'msg' => '用户名不能为空'];
		}

		if (!isset($data['user_pwd']) || empty($data['user_pwd'])) {
			return ['code' => 0, 'msg' => '密码不能为空'];
		}

		$result = $this->db()->where('name="'.$data['user_name'].'"')->row();
		if (!$result || md5($data['user_pwd']) != $result['password']) {
			return ['code' => 0, 'msg' => '帐户或密码错误'];
		}

		// 获取管理员权限
		if ($result['super']) {
			$menu_res = \Admin\MenuModel::getInstance()->getAll('sysadmin');
		}else{
			$role = \Admin\RoleModel::getInstance()->getInfoById($result['role']);
			$menu_res = \Admin\MenuModel::getInstance()->getList('id in('.$role['menu'].')');
		}

		if (!$menu_res) {
			return ['code' => 0, 'msg' => '没有获取到管理员权限'];
		}

		//保存管理员会话
		$menu_array = [];
		foreach($menu_res as $key => $value){
			$menu_array[$value['id']] = $value; 
		}

		$session_prefix = \Yaf\Registry::get('config')->session->prefix;
		$session = \Yaf\Session::getInstance();
		$result['menu'] = menu_merge($menu_array);
		$session->set($session_prefix . 'admin_user', $result);

		return ['code' => 1, 'msg' => '登录成功'];
	}
}