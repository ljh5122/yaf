<?php

namespace Admin;
use BaseModel;

/**
* 小程序数据统计对象
*/
class MiniprogramDataModel extends BaseModel {

	public $table = 'miniprogram_data';

	/**
	 * [create 创建]
	 */
	public function create($data = []){
		if (empty($data['type'])){
			return ['code' => 0, 'msg' => '接口类型不能为空！'];
		}

		if (empty($data['content'])){
			return ['code' => 0, 'msg' => '接口返回值不能为空！'];
		}

		if (empty($data['begin_date'])){
			return ['code' => 0, 'msg' => '开始日期不能为空！'];
		}

		if (empty($data['end_date'])){
			return ['code' => 0, 'msg' => '结束日期不能为空！'];
		}

		$data['utime'] = time();
		$result = $this->db()->insert($data);
		return $result;
	}

	/**
	 * [delete 删除]
	 */
	public function delete($data = []){
		if (empty($data['type'])){
			return false;
		}

		$where = ' type='.$data['type'];
		$where .= $data['drange'] ? ' and drange='.$data['drange'] : '';

		$result = $this->db()->where($where)->delete();
		return $result;
	}

	//获取小程序日统计数据
	// public function getDaily($data = []){
	// 	if(!$data['type']){
	// 		return ['code' => 1, 'msg' => '请输入数据类型'];
	// 	}

	// 	if(!$data['begin_date']){
	// 		return ['code' => 1, 'msg' => '请输入开始日期'];
	// 	}

	// 	if(!$data['end_date']){
	// 		return ['code' => 1, 'msg' => '请输入结束日期'];
	// 	}

	// 	$type = in_array($data['type'], ['share_pv', 'share_uv']) ? 2 : 3;
	// 	$where = ' type='.$type;
	// 	$where .= ' and drange=1 ';
	// 	$where .= ' and (begin_date>='.$data['begin_date'].' and end_date<='.$data['end_date'].')';
	// 	$result = $this->db()->where($where)->select('content');
	// 	if ($result) {
	// 		$result_data = [];
	// 		foreach($result as $value){
	// 			$value = json_decode($value['content'], true);
	// 			$result_data[$value['list'][0]['ref_date']] = $value['list'][0][$data['type']];
	// 		}

	// 		$result = ['code' => 0, 'data' => $result_data];
	// 	}else{
	// 		$result = ['code' => 1, 'msg' => '没有相关数据'];
	// 	}

	// 	return $result;
	// }

	//获取小程序日统计数据
	public function getDaily($data = []){
		if(!$data['begin_date']){
			return ['code' => 1, 'msg' => '请输入开始日期'];
		}

		if(!$data['end_date']){
			return ['code' => 1, 'msg' => '请输入结束日期'];
		}

		$where = ' type in (2,3) ';
		$where .= ' and drange=1 ';
		$where .= ' and (begin_date>='.$data['begin_date'].' and end_date<='.$data['end_date'].')';
		$result = $this->db()->where($where)->select('type,content');
		if ($result) {
			$result_data = [];
			foreach($result as $value){
				$content = json_decode($value['content'], true);
				if(isset($content['errcode'])){
					continue;
				}

				if($value['type'] == 2){
					$result_data[$content['list'][0]['ref_date']]['chart1']['share_pv'] = $content['list'][0]['share_pv'];
					$result_data[$content['list'][0]['ref_date']]['chart1']['share_uv'] = $content['list'][0]['share_uv'];
				}else{
					$result_data[$content['list'][0]['ref_date']]['chart1']['session_cnt'] = $content['list'][0]['session_cnt'];
					$result_data[$content['list'][0]['ref_date']]['chart1']['visit_pv'] = $content['list'][0]['visit_pv'];
					$result_data[$content['list'][0]['ref_date']]['chart1']['visit_uv'] = $content['list'][0]['visit_uv'];
					$result_data[$content['list'][0]['ref_date']]['chart1']['visit_uv_new'] = $content['list'][0]['visit_uv_new'];

					$result_data[$content['list'][0]['ref_date']]['chart2']['stay_time_uv'] = $content['list'][0]['stay_time_uv'];
					$result_data[$content['list'][0]['ref_date']]['chart2']['stay_time_session'] = $content['list'][0]['stay_time_session'];

					$result_data[$content['list'][0]['ref_date']]['chart3']['visit_depth'] = $content['list'][0]['visit_depth'];
				}
			}

			$result = ['code' => 0, 'data' => $result_data];
		}else{
			$result = ['code' => 1, 'msg' => '没有相关数据'];
		}

		return $result;
	}

	//获取小程序当日页面访问数据
	public function getAnalysisVisitPage($data = []){
		if(!$data['page']){
			return ['code' => 1, 'msg' => '请输入查询页面'];
		}

		if(!$data['begin_date']){
			return ['code' => 1, 'msg' => '请输入开始日期'];
		}

		if(!$data['end_date']){
			return ['code' => 1, 'msg' => '请输入结束日期'];
		}

		$data['begin_date'] = str_replace('-', '', $data['begin_date']);
		$data['end_date'] = str_replace('-', '', $data['end_date']);
		$where = ' type=8 and drange=1 ';
		$where .= ' and (begin_date>='.$data['begin_date'].' and end_date<='.$data['end_date'].')';
		$result = $this->db()->where($where)->select('content');
		if ($result) {
			$page_array = [
				'pages/equipment/pages/list/list' 			=> '装备列表',
				'pages/equipment/pages/eqdetails/eqdetails' => '装备详情',
				'pages/equipment/pages/disex/disex' 		=> '装备男女分类',
				'pages/tabBar/index/index' 					=> '小程序首页',
				'pages/index/pages/preview/preview' 		=> '造型列表',
				'pages/index/pages/single/single' 			=> '造型单品',
				'pages/tabBar/information/information' 		=> '资讯首页',
				'pages/tabBar/my/my' 						=> '个人中心',
				'pages/index/pages/video/video' 			=> '视频详情',
				'pages/tabBar/music/music' 					=> '音乐主页',
				'pages/information/pages/article/article' 	=> '资讯详情',
				'pages/my/pages/model/model' 				=> '个人收藏',
				'pages/index/pages/ride/ride' 				=> '收藏单页详情',
				'pages/index/pages/introduce/introduce' 	=> '造型图文介绍',
				'pages/my/pages/chat/chat' 					=> '留言室',
				'pages/tabBar/authorization/authorization' 	=> '登录页面',
				'pages/index/pages/shoes/shoes' 			=> '搭配页面',
			];

			$result_data = [];
			$page_table = [];
			foreach($result as $value){
				$content = json_decode($value['content'], true);
				if(isset($content['errcode'])){
					continue;
				}

				$page_chart = 0;
				foreach($content['list'] as $val){
					$page_path = $page_array[$val['page_path']] ?: $val['page_path'];
					if($data['page'] == 'all'){ //所有页面
						$page_chart += $val['page_visit_pv'];
						$page_table[$val['page_path']]['page_path'] = $val['page_path'];
						$page_table[$val['page_path']]['page_name'] = $page_path;
						$page_table[$val['page_path']]['page_visit_pv'] += $val['page_visit_pv'];
						$page_table[$val['page_path']]['page_visit_uv'] += $val['page_visit_uv'];
						$page_table[$val['page_path']]['page_staytime_pv'] += $val['page_staytime_pv'];
						$page_table[$val['page_path']]['entrypage_pv'] += $val['entrypage_pv'];
						$page_table[$val['page_path']]['exitpage_pv'] += $val['exitpage_pv'];
						$page_table[$val['page_path']]['page_share_pv'] += $val['page_share_pv'];
						$page_table[$val['page_path']]['page_share_uv'] += $val['page_share_uv'];

					}else{ //单页面

						if($data['page'] == $val['page_path']){
							$page_chart += $val['page_visit_pv'];
							$page_table[$val['page_path']]['page_path'] = $val['page_path'];
							$page_table[$val['page_path']]['page_name'] = $page_path;
							$page_table[$val['page_path']]['page_visit_pv'] += $val['page_visit_pv'];
							$page_table[$val['page_path']]['page_visit_uv'] += $val['page_visit_uv'];
							$page_table[$val['page_path']]['page_staytime_pv'] += $val['page_staytime_pv'];
							$page_table[$val['page_path']]['entrypage_pv'] += $val['entrypage_pv'];
							$page_table[$val['page_path']]['exitpage_pv'] += $val['exitpage_pv'];
							$page_table[$val['page_path']]['page_share_pv'] += $val['page_share_pv'];
							$page_table[$val['page_path']]['page_share_uv'] += $val['page_share_uv'];
						}
					}
				}

				$result_data['chart'][$content['ref_date']] = $page_chart;
			}

			$result_data['table'] = $page_table;
			$result_data['drange'] = count($result);

			$result = ['code' => 0, 'data' => $result_data];
		}else{
			$result = ['code' => 1, 'msg' => '没有相关数据'];
		}

		return $result;
	}

	//获取小程序当日访问分布数据
	public function getAnalysisVisitDistribution($data = []){
		if(!$data['begin_date']){
			return ['code' => 1, 'msg' => '请输入开始日期'];
		}

		if(!$data['end_date']){
			return ['code' => 1, 'msg' => '请输入结束日期'];
		}

		$data['begin_date'] = str_replace('-', '', $data['begin_date']);
		$data['end_date'] = str_replace('-', '', $data['end_date']);
		$where = ' type=7 and drange=1 ';
		$where .= ' and (begin_date>='.$data['begin_date'].' and end_date<='.$data['end_date'].')';
		$result = $this->db()->where($where)->select('content');
		if ($result) {
			$result_data = [];
			foreach($result as $value){
				$content = json_decode($value['content'], true);
				if(isset($content['errcode'])){
					continue;
				}
				
				foreach($content['list'] as $val){
					foreach($val['item_list'] as $v){
						$result_data[$val['index']][$v['key']] += $v['value'];
					}
				}
			}

			$result = ['code' => 0, 'data' => $result_data];
		}else{
			$result = ['code' => 1, 'msg' => '没有相关数据'];
		}

		return $result;
	}

	//获取小程序新增或活跃用户的画像分布数据
	public function getAnalysisUserPortrait($data = []){
		if(!$data['end_date']){
			return ['code' => 1, 'msg' => '请输入结束日期'];
		}

		if(!$data['drange']){
			return ['code' => 1, 'msg' => '请输入查询期限'];
		}

		$where = ' type=6';
		$where .= ' and drange='.$data['drange'];
		$where .= ' and end_date='.str_replace('-', '', $data['end_date']);
		$result = $this->db()->where($where)->row('content');
		if ($result) {
			$result_data = json_decode($result['content'], true);
			$result = ['code' => 0, 'data' => $result_data];
		}else{
			$result = ['code' => 1, 'msg' => '没有相关数据'];
		}

		return $result;
	}

}