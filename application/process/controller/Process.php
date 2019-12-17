<?php

namespace app\process\controller;

use app\common\controller\Base;
use think\Db;

class Process extends Base
{
    public function initialize()
    {
        parent::initialize();
        //体检套餐
        $project_code = $this->project_code;
        $project_list = Db::table('db_erp_new.prj_product')
            ->field('p_code,p_name')
            ->where(" `p_project_code` = '" . $project_code . "' AND `p_type_code` = 'append' AND `p_status` = '1'")
            ->order('id')
            ->select();
        //预约类型
        $yu_type = [
            ['id' => 1, 'name' => '员工预约'],
            ['id' => 2, 'name' => '家属预约'],
        ];
        $this->assign('project_list', $project_list);
        $this->assign('yu_type', $yu_type);
    }

    public function index()
    {
        //初始化页面参数
        $searchData['province_code'] = "";
        $searchData['city_code'] = "";
        $searchData['area_code'] = "";
        $searchData['project'] = "";
        $searchData['yu_type'] = 0;
        $searchData['starttime'] = "";
        $searchData['endtime'] = "";
        $list = [];
        $whereSql = "";
        $submit = 0;
        if (request()->isPost() || request()->isGet()) {
            //判断是点击查询按钮
            $submit = input('request.submit');
            if ($submit == 1) {
                $city_code = input('request.city_code');
                $project = input('request.project');
                $yu_type = intval(input('request.yu_type'));
                $starttime = input('request.start_time');
                $endtime = input('request.end_time');
                if (!empty($city_code)) {
                    $searchData['city_code'] = $city_code;
                    $searchData['province_code'] = input('request.province_code');
                    $searchData['area_code'] = input('request.area_code');
                    $whereSql .= " AND a.append_city_code ='" . $city_code . "'";
                }
                if (!empty($project)) {
                    $searchData['project'] = $project;
                    $whereSql .= " AND a.append_product_code ='" . $project . "'";
                }
                if (!empty($yu_type)) {
                    $searchData['yu_type'] = $yu_type;
                    $whereSql .= ' AND a.append_type =' . $yu_type;
                }
                if (!empty($starttime)) {
                    $searchData['start_time'] = $starttime;
                    $whereSql .= " AND a.append_schedule >= '" . $starttime . "'";
                }
                if (!empty($endtime)) {
                    $searchData['end_time'] = $endtime;
                    $whereSql .= " AND a.append_schedule <= '" . $endtime . "'";
                }
                $list = $this->summaryListQuery($whereSql);
            } else {
                $list = $this->sumListQuery();
            }
        }
        $count = count($list);
        $sum1 = 0;
        $sum2 = 0;
        $sum3 = 0;
        $sum4 = 0;
        $city_list = [];
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $sum1 += intval($v['append_total_num']);
                $sum2 += intval($v['append_arrive_num']);
                $sum3 += intval($v['append_report_num']);
                $sum4 = round($sum2 / $sum1 * 100, 2);
                $append_total_num = $v['append_total_num'];
                $append_arrive_num = $v['append_arrive_num'];
                //检测预约总人数为0时，到检率为0
                if ($append_total_num === 0) {
                    $list[$k]['check_rate'] = 0 . "％";
                } else {
                    $check_rate = round($append_arrive_num / $append_total_num * 100, 2) . "％";
                    $list[$k]['check_rate'] = $check_rate;
                }
            }
        }
        if (substr($sum4, strpos($sum4, '.') + 1) == 0 || substr($sum4, strpos($sum4, '.') + 1) == 00) {
            $sum4 = intval($sum4);
        }
        $total_data['sum1'] = $sum1;
        $total_data['sum2'] = $sum2;
        $total_data['sum3'] = $sum3;
        $total_data['sum4'] = $sum4 . "%";
        $this->assign('count', $count);
        $this->assign('title', '体检数据汇总');
        $this->assign('city_list', $city_list);
        $this->assign('list', $list);
        $this->assign('sub', $submit);
        $this->assign('total_data', $total_data);
        $this->assign('searchData', $searchData);
        $this->assign('e_name', $this->e_name);
        return $this->fetch();
    }

    /**
     * 全国套餐总计功能
     */
    public function sum_all()
    {
        $project_code = $this->project_code;
        $hr_id = $this->app_id;
        $son_str = $this->son_str;
        $whereSql = "";
        if (!empty($son_str)) {
            $whereSql = " AND (u.u_son_code in (" . $son_str . ") or parent.u_son_code in (" . $son_str . "))";
        }
        $list = Db::table('db_erp_new.ser_append')
            ->alias('a')
            ->field("p.p_code,p.p_name,count(a.id) AS append_total_num")
            ->leftJoin('db_erp_new.prj_product p', 'p.p_code = a.append_product_code')
            ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '" . $project_code . "'")
            ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= " . $hr_id)
            ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
            ->where("a.append_status IN ( 1, 2 ) AND a.append_project_code = '$project_code' AND p.p_status = 1 AND p.p_type_code = 'append' AND p.p_project_code = '$project_code' " . $whereSql)
            ->group('a.append_product_code')
            ->order('append_total_num DESC')
            ->select();
        foreach ($list as $k => $v) {
            $append_total_num = $v['append_total_num'];
            $append_arrive_num = Db::table('db_erp_new.ser_append')
                ->alias('a')
                ->leftJoin('db_erp_new.prj_product p', 'p.p_code = a.append_product_code')
                ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '" . $project_code . "'")
                ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= " . $hr_id)
                ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
                ->where("a.append_status in(1,2) AND a.append_arrive_status = 2 AND a.append_project_code = '$project_code' AND a.append_product_code = '" . $v['p_code'] . "' " . $whereSql)
                ->count();
            $append_report_num = Db::table('db_erp_new.ser_append')
                ->alias('a')
                ->leftJoin('db_erp_new.prj_product p', 'p.p_code = a.append_product_code')
                ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '" . $project_code . "'")
                ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= " . $hr_id)
                ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
                ->where("a.append_status in(1,2) AND a.append_report_status = 2 AND a.append_project_code = '$project_code' AND a.append_product_code = '" . $v['p_code'] . "' " . $whereSql)
                ->count();
            $list[$k]['append_arrive_num'] = $append_arrive_num;
            $list[$k]['append_report_num'] = $append_report_num;
            //检测预约总人数为0时，到检率为0
            if ($append_total_num === 0) {
                $list[$k]['check_rate'] = 0 . "％";
            } else {
                $check_rate = round($append_arrive_num / $append_total_num * 100, 2) . "％";
                $list[$k]['check_rate'] = $check_rate;
            }

        }
        if (empty($list)) {
            return msg_return("未查询到套餐汇总信息", 0);
        } else {
            return msg_return("获取套餐汇总信息成功", 1, $list);
        }
    }

    /**
     *各个城市套餐总计功能
     */
    public function sum_search()
    {
        $area = input('request.area');
        $whereSql = "";
        if (!empty($area)) {
            $whereSql = " AND a.append_city_code ='" . $area . "'";
        }
        //将sql传递到公共方法
        $list = $this->summaryListQuery($whereSql);
        foreach ($list as $k => $v) {
            $append_total_num = $v['append_total_num'];
            $append_arrive_num = $v['append_arrive_num'];
            //检测预约总人数为0时，到检率为0
            if ($append_total_num === 0) {
                $list[$k]['check_rate'] = 0 . "％";
            } else {
                $check_rate = round($append_arrive_num / $append_total_num * 100, 2) . "％";
                $list[$k]['check_rate'] = $check_rate;
            }
        }
        if (empty($list)) {
            return msg_return("未查询到体检数据汇总信息", 0);
        } else {
            return msg_return("获取体检数据汇总信息成功", 1, $list);
        }
    }

    /**搜索方法和ajax查询城市套餐的公共方法
     * @param string $whereSql 查询条件
     */
    public function summaryListQuery($sql = "")
    {
        $whereSql = $sql;
        $project_code = $this->project_code;
        $hr_id = $this->app_id;
        $son_str = $this->son_str;
        if (!empty($son_str)) {
            $whereSql .= " AND (u.u_son_code in (" . $son_str . ") or parent.u_son_code in (" . $son_str . "))";
        }
        $subQuery1 = Db::table('db_erp_new.ser_append')
            ->alias('a')
            ->field('a.append_city_code,a.append_schedule,a.append_product_code,1 as sta')
            ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '" . $project_code . "'")
            ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= " . $hr_id)
            ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
            ->where("a.append_project_code='" . $project_code . "' and a.append_status IN(1,2) " . $whereSql)
            ->buildSql();
        $subQuery2 = Db::table('db_erp_new.ser_append')
            ->alias('a')
            ->field('a.append_city_code,a.append_schedule,a.append_product_code,2 as sta')
            ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '" . $project_code . "'")
            ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= " . $hr_id)
            ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
            ->where("a.append_project_code='" . $project_code . "' and a.append_status IN(1,2) and a.append_arrive_status=2 " . $whereSql)
            ->buildSql();
        $subQuery3 = Db::table('db_erp_new.ser_append')
            ->alias('a')
            ->field('a.append_city_code,a.append_schedule,a.append_product_code,3 as sta')
            ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '" . $project_code . "'")
            ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= " . $hr_id)
            ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
            ->where("a.append_project_code='" . $project_code . "' and a.append_status IN(1,2) and a.append_report_status=2 " . $whereSql)
            ->buildSql();
        $list = Db::table('(' . $subQuery1 . ' union all ' . $subQuery2 . ' union all ' . $subQuery3 . ')')
            ->alias('a')
            ->field("	r.area_code,r.area_full_name,p.p_code,a.append_schedule,p.p_name,sum(case when a.sta=1  then 1 else 0 end ) 'append_total_num',sum(case when a.sta=2  then 1 else 0 end ) 'append_arrive_num', sum(case when a.sta=3  then 1 else 0 end ) 'append_report_num'")
            ->leftJoin('db_erp_new.et_region r', 'r.area_code = a.append_city_code')
            ->leftJoin('db_erp_new.prj_product p', 'p.p_code = a.append_product_code')
            ->group('a.append_city_code,a.append_product_code')
            ->select();

        return $list;
    }

    /**sum公共方法
     * @param string $whereSql 查询条件
     */
    public function sumListQuery()
    {
        $project_code = $this->project_code;
        $hr_id = $this->app_id;
        $son_str = $this->son_str;
        $whereSql = "";
        if (!empty($son_str)) {
            $whereSql = " AND (u.u_son_code in (" . $son_str . ") or parent.u_son_code in (" . $son_str . "))";
        }
        $list = Db::table('db_erp_new.ser_append')
            ->alias('a')
            ->field("r.area_code,a.append_schedule,r.area_full_name,count(a.id) as append_total_num")
            ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '" . $project_code . "'")
            ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= " . $hr_id)
            ->leftJoin('db_erp_new.et_region r', 'r.area_code = a.append_city_code')
            ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
            ->where("a.append_status IN ( 1, 2 ) AND a.append_project_code = '" . $project_code . "' and r.area_full_name is not null " . $whereSql)
            ->group('a.append_city_code')
            ->order('append_total_num DESC')
            ->select();
        foreach ($list as $k => $v) {
            $list[$k]['append_arrive_num'] = Db::table('db_erp_new.ser_append')
                ->alias('a')
                ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '" . $project_code . "'")
                ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= " . $hr_id)
                ->leftJoin('db_erp_new.et_region r', 'r.area_code = a.append_city_code')
                ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
                ->where("a.append_status in(1,2) and a.append_arrive_status = 2 and r.area_code =" . $v['area_code'] . " and a.append_project_code = '" . $project_code . "'" . $whereSql)
                ->count();
            $list[$k]['append_report_num'] = Db::table('db_erp_new.ser_append')
                ->alias('a')
                ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '" . $project_code . "'")
                ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= " . $hr_id)
                ->leftJoin('db_erp_new.et_region r', 'r.area_code = a.append_city_code')
                ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
                ->where("a.append_status in(1,2) and a.append_report_status = 2 and r.area_code = " . $v['area_code'] . " and a.append_project_code = '" . $project_code . "'" . $whereSql)
                ->count();
        }
        return $list;
    }

    public function sum()
    {
        $list = $this->sumListQuery();
        foreach ($list as $k => $v) {
            $append_total_num = $v['append_total_num'];
            $append_arrive_num = $v['append_arrive_num'];
            //检测预约总人数为0时，到检率为0
            if ($append_total_num === 0) {
                $list[$k]['check_rate'] = 0 . "％";
            } else {
                $check_rate = round($append_arrive_num / $append_total_num * 100, 2) . "％";
                $list[$k]['check_rate'] = $check_rate;
            }
        }
        if (empty($list)) {
            return msg_return("未查询到体检数据汇总信息", 0);
        } else {
            return msg_return("获取体检数据汇总信息成功", 1, $list);
        }
    }

    public function reservation()
    {
        $hr_id = $this->app_id;
        $project_code = $this->project_code;
        $son_str = $this->son_str;
        $whereSql = "";
        if (!empty($son_str)) {
            $whereSql = " AND (u.u_son_code in (" . $son_str . ") or parent.u_son_code in (" . $son_str . "))";
        }
        //查询未预约人数
        $unReservedNum = Db::table('db_erp_new.prj_project_user')
            ->alias('u')
            ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= " . $hr_id)
            ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
            ->where("u.u_project_code = '" . $project_code . "' AND u.u_is_append = '0'" . $whereSql)
            ->count("u.id");
        //查询已预约人数
        $reservedNum = Db::table('db_erp_new.ser_append')
            ->alias('a')
            ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '$project_code'")
            ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id=" . $hr_id)
            ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
            ->where("a.append_status IN (1, 2) AND a.append_project_code = '$project_code'" . $whereSql)
            ->count('a.id');
        //查询已到检人数
        $checkNum = Db::table('db_erp_new.ser_append')
            ->alias('a')
            ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '$project_code'")
            ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id=" . $hr_id)
            ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
            ->where("a.append_arrive_status = 2 AND a.append_project_code = '$project_code'" . $whereSql)
            ->count('a.id');
        //查询报告已上传人数
        $uploadedNum = Db::table('db_erp_new.ser_append')
            ->alias('a')
            ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '$project_code'")
            ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id=" . $hr_id)
            ->leftJoin('db_erp_new.prj_project_user parent', 'parent.id=u.u_parent_id')
            ->where("a.append_report_status = 2 AND a.append_project_code = '$project_code'" . $whereSql)
            ->count('a.id');
        //查询家属已预约人数
        /* $familyReservedNum = Db::table('db_erp_new.ser_append')
             ->alias('a')
             ->leftJoin('db_erp_new.prj_project_user u', "a.append_user_code=u.id AND u.u_project_code = '$project_code'")
             ->leftJoin('db_erp_new.prj_hr_sonenter_relation hsr', "u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id=" . $hr_id)
             ->where("a.append_type = 2 AND a.append_status IN (1, 2) AND a.append_project_code = '$project_code'".$whereSql)
             ->count('a.id');*/
        $statistics = [];
        $statistics['unReservedNum'] = $unReservedNum;
        $statistics['reservedNum'] = $reservedNum;
        $statistics['checkNum'] = $checkNum;
        $statistics['uploadedNum'] = $uploadedNum;
        //$statistics['familyReservedNum'] = $familyReservedNum;
        return msg_return("获取项目预约情况统计信息成功", 1, $statistics);
    }

    public function sum_echart()
    {
        $this->assign('e_name', $this->e_name);
        return $this->fetch();
    }

}