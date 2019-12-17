<?php

namespace app\project\controller;

use app\common\controller\Base;
use app\common\controller\Excel;
use think\Db;

class EmployeeInfo extends Base
{
    //定义prj_project_user数组
    public $u_type = array(1 => '员工', 2 => '代报销员工', 3 => '特殊员工', 4 => '家属');
    public $u_status = array(1 => '正常', 2 => '离职', 3 => '冻结禁用');

    public function initialize()
    {
        parent::initialize();
        $marry = get_dictionaries_list('user_marry',1);
        $u_type = [
            ['id' => 1, 'name' => '员工'],
            ['id' => 2, 'name' => '代报销员工'],
            ['id' => 3, 'name' => '特殊员工'],
        ];
        $app_id = $this->app_id;
        $son_list = Db::table('db_erp_new.prj_hr_sonenter_relation')
            ->alias('t1')
            ->field('t2.s_code,t2.s_name')
            ->leftJoin('db_erp_new.cus_son_enterprise t2', 't2.s_code = t1.s_enter_code')
            ->where('t1.status=1 and t1.hr_id = ' . $app_id)
            ->select();
        $this->assign('marry', $marry);
        $this->assign('u_type', $u_type);
        $this->assign('u_son_code', $son_list);
    }

    public function index()
    {
        //初始化页面参数
        $searchData['employee_name'] = '';
        $searchData['gh'] = '';
        $searchData['zjh'] = '';
        $searchData['marry'] = 0;
        $searchData['u_type'] = 0;
        $searchData['u_son_code'] = '';
        $project_code = $this->project_code;
        $hr_id = $this->app_id;
        $son_str=$this->son_str;
        $whereSql = " u.`u_project_code` = '$project_code' and u.u_type < 4 ";
        if(!empty($son_str)){
            $whereSql.=" AND u.u_son_code in (".$son_str.")";
        }
        $submit = 0;
        if (request()->isPost() || request()->isGet()) {
            //dump($posts = input('post.'));
            $submit = input('request.submit');
            $employee_name = input('request.employee_name');
            $gh = input('request.gh');
            $zjh = input('request.zjh');
            $marry = intval(input('request.marry'));
            $u_type = intval(input('request.u_type'));
            $u_son_code = input('request.u_son_code');
            if (!empty($u_type)) {
                //预约状态 0=>全部员工；1=>未预约员工；2=>已预约（包含已改约）员工；3=>已到检员工
                $whereSql .= " AND u.`u_type` = " . $u_type;
                $searchData['u_type'] = $u_type;
            }
            if (!empty($employee_name)) {
                $whereSql .= ' AND u.u_name like \'%' . $employee_name . '%\' ';
                $searchData['employee_name'] = $employee_name;
            }
            if (!empty($gh)) {
                $whereSql .= ' AND u.u_job_code like \'%' . $gh . '%\' ';
                $searchData['gh'] = $gh;
            }
            if (!empty($marry)) {
                $whereSql .= " AND u.u_marry='" . $marry . "' ";
                $searchData['marry'] = $marry;
            }
            if (!empty($zjh)) {
                $whereSql .= ' AND u.u_identify_code like \'%' . $zjh . '%\' ';
                $searchData['zjh'] = $zjh;
            }
            if (!empty($u_son_code)) {
                $whereSql .= " AND son.s_code='" . $u_son_code . "' ";
                $searchData['u_son_code'] = $u_son_code;
            }
        }
        //1就是查询，2是导出
        $count = 0;
        $for_list = [];
        if ($submit != 2) {
            //查询
            $list = Db::table('db_erp_new.prj_project_user')
                ->alias('u')
                ->field("u.id,u.u_name,u.u_job_code,u.u_identify_type,u.u_identify_code,u.u_sex, u.u_marry,u.u_mobile,u.u_city,u.u_birthday,u.u_sub_company,u.u_department,u.u_auth,u.u_type,u.u_status,u.u_remark,u.u_ext3,j.project_name,e.e_code,e.e_name,son.s_name,pp.pk_name, r.area_full_name as append_city_name, pt.user_name as post_user_name, pt.user_address as post_user_address, pt.user_mobile as post_user_mobile,a.append_status")
                ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', 'u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= ' . $hr_id)
                ->leftJoin('db_erp_new.prj_project j', 'u.u_project_code = j.project_code')
                ->leftJoin('db_erp_new.cus_son_enterprise son', 'son.s_code = u.u_son_code')
                ->leftJoin('db_erp_new.cus_enterprise e', 'j.enterprise_code = e.e_code')
                ->leftJoin('db_erp_new.ser_append a', 'a.append_user_code = u.id')
                ->leftJoin('db_erp_new.prj_product_package pp', 'u.u_bind_package = pp.pk_code')
                ->leftJoin('db_erp_new.et_region r', 'r.area_code = a.append_city_code')
                ->leftJoin('db_erp_new.ser_post_info pt', 'pt.post_order_code = a.append_order_code')
                ->where($whereSql)
                ->group('u.id')
                ->order('u.id DESC')
                ->limit(10)
                ->paginate(10);
        } else {
            //导出
            $list = Db::table('db_erp_new.prj_project_user')
                ->alias('u')
                ->field("u.id,u.u_name,u.u_job_code,u.u_identify_type,u.u_identify_code,u.u_sex, u.u_marry,u.u_mobile,u.u_city,u.u_birthday,u.u_sub_company,u.u_department,u.u_auth,u.u_type,u.u_status,u.u_remark,u.u_ext3,j.project_name,e.e_code,e.e_name,son.s_name,pp.pk_name, r.area_full_name as append_city_name, pt.user_name as post_user_name, pt.user_address as post_user_address, pt.user_mobile as post_user_mobile,a.append_status")
                ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', 'u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= ' . $hr_id)
                ->leftJoin('db_erp_new.prj_project j', 'u.u_project_code = j.project_code')
                ->leftJoin('db_erp_new.cus_son_enterprise son', 'son.s_code = u.u_son_code')
                ->leftJoin('db_erp_new.cus_enterprise e', 'j.enterprise_code = e.e_code')
                ->leftJoin('db_erp_new.ser_append a', 'a.append_user_code = u.id')
                ->leftJoin('db_erp_new.prj_product_package pp', 'u.u_bind_package = pp.pk_code')
                ->leftJoin('db_erp_new.et_region r', 'r.area_code = a.append_city_code')
                ->leftJoin('db_erp_new.ser_post_info pt', 'pt.post_order_code = a.append_order_code')
                ->where($whereSql)
                ->group('u.id')
                ->order('u.id DESC')
                ->select();
            $data = [];
            foreach ($list as $k => $v) {
                $data[$k]['id'] = $k + 1;
                $data[$k]['s_name'] = $v['s_name'];
                $data[$k]['u_name'] = $v['u_name'];
                $data[$k]['pk_name'] = $v['pk_name'];
                $data[$k]['u_job_code'] = $v['u_job_code'];
                $data[$k]['u_identify_type_name'] = get_dictionaries_list('certificate_type',1,$v['u_identify_type']);
                $data[$k]['u_identify_code'] = $v['u_identify_code'];
                $data[$k]['u_sex_name'] = get_dictionaries_list('user_sex',1,$v['u_sex']);
                $data[$k]['u_mobile'] = $v['u_mobile'];
                $data[$k]['u_type_name'] = isset($this->u_type[$v['u_type']]) && !empty($v['u_type']) ? $this->u_type[$v['u_type']] : "";
                $data[$k]['u_status_name'] = isset($this->u_status[$v['u_status']]) && !empty($v['u_status']) ? $this->u_status[$v['u_status']] : "";
                $data[$k]['u_remark'] = $v['u_remark'];
            }
            $headArr = ["序号", "子公司名称", "员工姓名", "套餐名称", "工号", "证件类型", "证件号", "性别", "手机号", "员工性质", "员工状态", "备注"];
            $width = array(10, 30, 30, 20, 20, 20, 20, 20, 20, 20, 20, 20);
            $columnType = null;
            $excel = new Excel();
            $excel->output_excel('员工信息管理', $headArr, $data, $width);
        }
        $count = $list->total();
        $for_list = $list->all();
        if (!empty($for_list)) {
            foreach ($for_list as $k => $v) {
                $for_list[$k]['u_identify_type_name'] = get_dictionaries_list('certificate_type',1,$v['u_identify_type']);
                $for_list[$k]['u_type_name'] = isset($this->u_type[$v['u_type']]) && !empty($v['u_type']) ? $this->u_type[$v['u_type']] : "";
                $for_list[$k]['u_sex_name'] = get_dictionaries_list('user_sex',1,$v['u_sex']);
                $for_list[$k]['u_status_name'] = isset($this->u_status[$v['u_status']]) && !empty($v['u_status']) ? $this->u_status[$v['u_status']] : "";
                $list->offsetSet($k, $for_list[$k]);
            }
        }
        $this->assign('title', '员工信息管理');
        $this->assign('list', $list);
        $this->assign('count', $count);
        $this->assign('searchData', $searchData);
        return $this->fetch();
    }

}