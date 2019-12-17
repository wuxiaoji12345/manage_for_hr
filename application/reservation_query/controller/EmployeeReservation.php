<?php
namespace app\reservation_query\controller;

use app\common\controller\Base;
use app\reservation_query\model\SerAppend;
use app\common\controller\Excel;

class EmployeeReservation extends Base
{

	public function index()
	{
    	//预约详情
		$request = $this->request->param();
		$page = 10;
		$whereSql = ' ';

        //预约人姓名（模糊查询）
		if(isset($request['append_name']) && !empty($request['append_name'])){
			$whereSql .= "AND a.`append_name` LIKE '%".$request['append_name']."%' ";
			$searchData['append_name'] = $request['append_name'];
		} 

        //预约手机号码（模糊查询）
		if(isset($request['append_mobile']) && !empty($request['append_mobile'])){
			$whereSql .= "AND a.`append_mobile` LIKE '%".$request['append_mobile']."%' ";
			$searchData['append_mobile'] = $request['append_mobile'];
		} 

        //预约人证件号码（模糊查询）
		if(isset($request['append_identify_code']) && !empty($request['append_identify_code'])){
			$whereSql .= "AND a.`append_identify_code` LIKE '%".$request['append_identify_code']."%' ";
			$searchData['append_identify_code'] = $request['append_identify_code'];
		}

        //工号（模糊查询）
		if(isset($request['u_job_code']) && !empty($request['u_job_code'])){
			$whereSql .= "AND u.`u_job_code` LIKE '%".$request['u_job_code']."%' ";
			$searchData['u_job_code'] = $request['u_job_code'];
		}

        //预约状态
		if(isset($request['append_status']) && !empty($request['append_status'])){
			if ($request['append_status'] == 8) {
				$whereSql .= " AND a.append_status  in(8,9) ";
			} else {
				$whereSql .= "AND a.`append_status` = '".$request['append_status']."' ";
			}
			$searchData['append_status'] = $request['append_status'];
		} else {
			$searchData['append_status'] = 0;
		}

        //报告状态
		if(isset($request['append_report_status']) && !empty($request['append_report_status']) && $request['append_report_status'] > 0){
			$whereSql .= "AND a.`append_report_status` = '".$request['append_report_status']."' ";
			$searchData['append_report_status'] = $request['append_report_status'];
		} else {
			$searchData['append_report_status'] = 0;
		}

        //查询类型（query_type 1=>按创建时间查询；2=>按体检时间查询；3=>按取消时间查询）
		if(isset($request['query_type']) && !empty($request['query_type'])){
			if($request['query_type'] == '1'){
				if(isset($request['start_time']) && !empty($request['start_time'])){
					$whereSql .= "AND a.`create_time` >= '".$request['start_time']."' ";
				}
				if(isset($request['end_time']) && !empty($request['end_time'])){
					$whereSql .= "AND a.`create_time` <= '".$request['end_time']."' ";
				}
			} elseif ($request['query_type'] == '2'){
				if(isset($request['start_time']) && !empty($request['start_time'])){
					$whereSql .= "AND a.`append_schedule` >= '".$request['start_time']."' ";
				}
				if(isset($request['end_time']) && !empty($request['end_time'])){
					$whereSql .= "AND a.`append_schedule` <= '".$request['end_time']."' ";
				}
			} elseif ($request['query_type'] == '3'){
				if(isset($request['start_time']) && !empty($request['start_time'])){
					$whereSql .= "AND a.`append_status` = 8 AND a.`update_time` >= '".$request['start_time']."' ";
				}
				if(isset($request['end_time']) && !empty($request['end_time'])){
					$whereSql .= "AND a.`append_status` = 8 AND a.`update_time` <= '".$request['end_time']."' ";
				}
			}
			$searchData['query_type'] = $request['query_type'];
		} else {
			$searchData['query_type'] = 0;
		}

		// print_r($whereSql);die;
		if(isset($request['start_time']) && !empty($request['start_time'])){
			$searchData['start_time'] = $request['start_time'];
		}
		if(isset($request['end_time']) && !empty($request['end_time'])){
			$searchData['end_time'] = $request['end_time'];
		}

        //子公司查询
		$u_son_code = '';
		if(isset($request['u_son_code']) && !empty($request['u_son_code'])){
			$whereSql .= "AND u.u_son_code= '".$request['u_son_code']."' ";
			$u_son_code = $request['u_son_code'];
			$searchData['u_son_code'] = $request['u_son_code'];
		} else {
			$searchData['u_son_code'] = 0;
		}		            

		$submit = isset($request['submit'])?$request['submit']:1;
		//1就是查询，2是导出
		if($submit != 2){
			$project_code = $this->project_code;
			$hr_id = $this->app_id;
			$son_str = $this->son_str;
			$data = [];
			$appendModel = new SerAppend;
			$appendList = $appendModel -> getEmployeeAppendList($project_code,$whereSql,$hr_id,$page,$son_str);
			$array = $appendList->toarray();
			if( empty($array) ){
				$data = [];
			} else {
            //员工列表
				$list = $array['data'];
				foreach ($list as $key => $value) {
					$list[$key]['bindAddons'] = '';
                //获取员工未预约时绑定的加项包
					if (!empty($value['u_bind_addon'])) {
						$bindAddonPackage = $this->getAddonList($value['u_bind_addon']);
						$list[$key]['bindAddons'] = $bindAddonPackage['p_name_str'];
					}
					$list[$key]['addon_expense'] = '';
                //获取自费加项包信息
					if (!empty($value['append_addon_expense'])) {
						$addonExpense = $this->getAddonList($value['append_addon_expense']);
						$list[$key]['addon_expense'] = $addonExpense['p_name_str'];
					}
					if (!empty($value['report_upload_time'])) {
						$list[$key]['report_upload_time_days'] = diffTwoDate($value['create_time'],$value['report_upload_time']);
					} else {
						$list[$key]['report_upload_time_days'] = diffTwoDate($value['create_time'],date("Y-m-d H:i:s"));
					}

					$data[$key]['id'] = $key + 1 + ((isset($request['page'])?$request['page']:1) - 1)*$page;
					$data[$key]['append_name'] = $value['append_name'];
					$data[$key]['u_job_code'] = $value['u_job_code'];
					if ($value['append_sex'] == 1) {
						$data[$key]['append_sex'] = '男';
					} else if ($value['append_sex'] == 2) {
						$data[$key]['append_sex'] = '女';
					}
					if ($value['append_marry'] == 1) {
						$data[$key]['append_marry']  = '未婚 ';
					} else if ($value['append_marry'] == 2) {
						$data[$key]['append_marry']  = '已婚 ';
					}else if ($value['append_marry'] == 3) {
						$data[$key]['append_marry']  = '保密';
					}
					if ($value['append_identify_type'] == 1) {
						$data[$key]['append_identify_type'] = '身份证';
					} else if ($value['append_identify_type'] == 2) {
						$data[$key]['append_identify_type'] = '护照';
					}else if ($value['append_identify_type'] == 3) {
						$data[$key]['append_identify_type'] = '军官证 ';
					}else if ($value['append_identify_type'] == 4) {
						$data[$key]['append_identify_type'] = '其他';
					}
					$data[$key]['append_identify_code'] = $value['append_identify_code'];
					$data[$key]['append_mobile'] = $value['append_mobile'];
					$data[$key]['append_code'] = $value['append_code'];
					$data[$key]['append_order_code'] = $value['append_order_code'];
					$data[$key]['store_name'] = $value['store_name'];
					$data[$key]['append_schedule'] = $value['append_schedule'];
					$data[$key]['p_name'] = $value['p_name'];
					$data[$key]['addon_expense'] = $list[$key]['addon_expense'] ? $list[$key]['addon_expense'] : "无";
					$data[$key]['bindAddons'] = $list[$key]['bindAddons'] ? $list[$key]['bindAddons'] : "无";
					if ($value['append_status'] == 1) {
						$data[$key]['append_status'] = '已预约';
					} else if ($value['append_status'] == 2) {
						$data[$key]['append_status'] = '已改约';
					} else if ($value['append_status'] == 8) {
						$data[$key]['append_status'] = '已取消';
					}else if ($value['append_status'] == 9) {
						$data[$key]['append_status'] = '已关闭';
					}
					if ($value['append_report_status'] == 1) {
						$data[$key]['append_report_status'] = '未获取报告';
					} else if ($value['append_report_status'] == 2) {
						$data[$key]['append_report_status'] = '报告已处理';
					}
					$data[$key]['report_upload_time_days'] = intval($list[$key]['report_upload_time_days']).'天';
				}
			}

        //预约人数统计
			$count_type = 1;
			$statisticsInfo = $appendModel -> getStatisticsInfo($project_code,$count_type,$hr_id,$u_son_code,$son_str);
			$u_son = $appendModel -> getSonEnterpriInfo($hr_id);     
			// $query_type = array(array('value'=>1,'name'=>'按创建时间查询'),array('value'=>2,'name'=>'按体检时间查询'),array('value'=>3,'name'=>'按取消时间查询'));

            $query_type = array(array('value'=>2,'name'=>'按体检时间查询')); 			

			$append_status = array(array('value'=>1,'name'=>'已预约'),array('value'=>2,'name'=>'已改约'),array('value'=>8,'name'=>'已取消'));
			$append_report_status = array(array('value'=>1,'name'=>'未获取报告'),array('value'=>2,'name'=>'报告已处理'));
            $title = '员工预约查询';
			$this->assign('searchData',$searchData);
			$this->assign('u_son_code',$u_son);
			$this->assign('query_type',$query_type);
			$this->assign('append_status',$append_status);
			$this->assign('append_report_status',$append_report_status);
			$this->assign('title',$title);

			$this->assign('appendList',$appendList);
			$this->assign('data',$data);
			$this->assign('info',$statisticsInfo);
			return $this->fetch();
		} else {
			$project_code = $this->project_code;
			$hr_id = $this->app_id;
			$son_str = $this->son_str;
			$appendModel = new SerAppend();
			$appendList = $appendModel -> getEmployeeAppendListAll($project_code,$whereSql,$hr_id,$son_str);
			$data = [];
			if (!empty($appendList)) {
            //员工列表
				$list = $appendList;
				foreach ($list as $key => $value) {
					$list[$key]['bindAddons'] = '';
                //获取员工未预约时绑定的加项包
					if (!empty($value['u_bind_addon'])) {
						$bindAddonPackage = $this->getAddonList($value['u_bind_addon']);
						$list[$key]['bindAddons'] = $bindAddonPackage['p_name_str'];
					}
					$list[$key]['addon_expense'] = '';
                //获取自费加项包信息
					if (!empty($value['append_addon_expense'])) {
						$addonExpense = $this->getAddonList($value['append_addon_expense']);
						$list[$key]['addon_expense'] = $addonExpense['p_name_str'];
					}
					if (!empty($value['report_upload_time'])) {
						$list[$key]['report_upload_time_days'] = diffTwoDate($value['create_time'],$value['report_upload_time']);
					} else {
						$list[$key]['report_upload_time_days'] = diffTwoDate($value['create_time'],date("Y-m-d H:i:s"));
					}

					$data[$key]['id'] = $key+1;
					$data[$key]['append_name'] = $value['append_name'];
					$data[$key]['u_job_code'] = $value['u_job_code'];
					if ($value['append_sex'] == 1) {
						$data[$key]['append_sex'] = '男';
					} else if ($value['append_sex'] == 2) {
						$data[$key]['append_sex'] = '女';
					}
					if ($value['append_marry'] == 1) {
						$data[$key]['append_marry']  = '未婚 ';
					} else if ($value['append_marry'] == 2) {
						$data[$key]['append_marry']  = '已婚 ';
					}else if ($value['append_marry'] == 3) {
						$data[$key]['append_marry']  = '保密';
					}
					if ($value['append_identify_type'] == 1) {
						$data[$key]['append_identify_type'] = '身份证';
					} else if ($value['append_identify_type'] == 2) {
						$data[$key]['append_identify_type'] = '护照';
					}else if ($value['append_identify_type'] == 3) {
						$data[$key]['append_identify_type'] = '军官证 ';
					}else if ($value['append_identify_type'] == 4) {
						$data[$key]['append_identify_type'] = '其他';
					}
					$data[$key]['append_identify_code'] = $value['append_identify_code'];
					$data[$key]['append_mobile'] = $value['append_mobile'];
					$data[$key]['append_code'] = $value['append_code'];
					$data[$key]['append_order_code'] = $value['append_order_code'];
					$data[$key]['store_name'] = $value['store_name'];
					$data[$key]['append_schedule'] = $value['append_schedule'];
					$data[$key]['p_name'] = $value['p_name'];
					$data[$key]['addon_expense'] = $list[$key]['addon_expense'] ? $list[$key]['addon_expense'] : "无";
					$data[$key]['bindAddons'] = $list[$key]['bindAddons'] ? $list[$key]['bindAddons'] : "无";
					if ($value['append_status'] == 1) {
						$data[$key]['append_status'] = '已预约';
					} else if ($value['append_status'] == 2) {
						$data[$key]['append_status'] = '已改约';
					} else if ($value['append_status'] == 8) {
						$data[$key]['append_status'] = '已取消';
					}else if ($value['append_status'] == 9) {
						$data[$key]['append_status'] = '已关闭';
					}
					if ($value['append_report_status'] == 1) {
						$data[$key]['append_report_status'] = '未获取报告';
					} else if ($value['append_report_status'] == 2) {
						$data[$key]['append_report_status'] = '报告已处理';
					}
					$data[$key]['report_upload_time_days'] = intval($list[$key]['report_upload_time_days']).'天';
				}

			}

			$headArr = ["编号","姓名","工号","性别","婚姻","证件类型","证件号码","手机号码","预约单号","订单号","预约门店","体检日期","体检套餐","自费加项包",
			"绑定加项包","预约状态","报告状态","报告处理时间"];
			$width = array(10,30,30,20,20,20, 20,20,20,20,20, 20,20,30,40,10, 20,20);
			$columnType = null;
			$excel = new Excel();
			$excel->output_excel('员工预约信息查询',$headArr,$data,$width);

		}
	}
	
	public function getAddonList($addoncodes = '') {

		if (empty($addoncodes)) {
			return NULL;
		}

		//实例化appendModel对象
		$appendModel = new SerAppend();

		$addonList = $appendModel->getBindAddonInfo($addoncodes);

		return $addonList;
	}

}