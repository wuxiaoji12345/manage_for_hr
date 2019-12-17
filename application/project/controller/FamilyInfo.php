<?php

namespace app\project\controller;

use app\common\controller\Base;
use think\Db;

class FamilyInfo extends Base {

	public function index() {
		$project_code = $this->project_code;
		$son_str = $this->son_str;
		$whereSql = 't1.u_project_code="' . $project_code . '" and t1.u_parent_id >0 and t1.u_type = 4 ';
		if (!empty($son_str)) {
			$whereSql .= " AND t6.u_son_code in (" . $son_str . ")";
		}
		$searchData = [];
		if (request()->isPost() || request()->isGet()) {
			$employee_name = input('request.employee_name');
			$gh = input('request.gh');
			$zjh = input('request.zjh');
			$family_name = input('request.family_name');
			$company = input('request.company');
			$department = input('request.department');
			$searchData['employee_name'] = $employee_name;
			$searchData['gh'] = $gh;
			$searchData['zjh'] = $zjh;
			$searchData['family_name'] = $family_name;
			$searchData['company'] = $company;
			$searchData['department'] = $department;
			if (!empty($employee_name)) {
				$whereSql .= ' AND t6.u_name like \'%' . $employee_name . '%\' ';
			}
			if (!empty($gh)) {
				$whereSql .= ' AND t6.u_job_code like \'%' . $gh . '%\' ';
			}
			if (!empty($family_name)) {
				$whereSql .= ' AND t1.u_name like \'%' . $family_name . '%\' ';
			}
			if (!empty($zjh)) {
				$whereSql .= ' AND t1.u_identify_code like \'%' . $zjh . '%\' ';
			}
			if (!empty($company)) {
				$whereSql .= ' AND t6.u_sub_company like \'%' . $company . '%\' ';
			}
			if (!empty($department)) {
				$whereSql .= ' AND t6.u_department like \'%' . $department . '%\' ';
			}
		}
		//t1家属  t6员工
		$list = Db::table('db_erp_new.prj_project_user')
			->alias('t1')
			->field('t1.id,t6.u_job_code,t6.u_sub_company,t6.u_department,t6.u_name,t1.u_parent_id,t4.pk_name,t5.area_full_name,t1.u_name as f_name,t1.u_sex as f_sex,t1.u_marry as f_marry,t1.u_identify_code as f_identify_code')
			->leftJoin('db_erp_new.cus_son_enterprise t2', 't1.u_son_code=t2.s_code')
			->leftJoin('db_erp_new.ser_append t3', 't3.append_user_code=t1.id')
			->leftJoin('db_erp_new.prj_product_package t4', 't1.u_bind_package = t4.pk_code')
			->leftJoin('db_erp_new.et_region t5', 't5.id=t3.append_city_code')
			->leftJoin('db_erp_new.prj_project_user t6', 't6.id=t1.u_parent_id')
			->where($whereSql)
			->group('t1.Id')
			->order('t1.Id DESC')
			->limit(10)
			->paginate(10);
		$count = $list->total();
		$for_list = $list->all();
		if (!empty($for_list)) {
			foreach ($for_list as $k => $v) {
				$for_list[$k]['f_sex'] = get_dictionaries_list('user_sex', 1, $v['f_sex']);
				$for_list[$k]['f_marry'] = get_dictionaries_list('user_marry', 1, $v['f_marry']);
				$list->offsetSet($k, $for_list[$k]);
			}
		}
		$this->assign('title', '家属信息');
		$this->assign('list', $list);
		$this->assign('count', $count);
		$this->assign('searchData', $searchData);

		return $this->fetch();
	}
}