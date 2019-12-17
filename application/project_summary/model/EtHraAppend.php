<?php
namespace app\project_summary\model;

use think\Model;

class EtHraAppend extends Model
{
    /**
     * 获取项目下风险评估信息统计
     *
     * @param       string    $project_code   项目编码
     * @return      array     $result         健康风险评估统计信息
     */
    public function get_health_risk_info($project_code,$son_str){
        $result = array();

        if(empty($project_code)){
            return $project_code;
        }

        $where = '';
        // if (isset($son_str)) {
        //     $where = " AND p.enterprise_code in (".$son_str.") ";
        // }

        //已评估人数
        $wheresql = " a.h_project_code = '$project_code' AND h_is_estimate = 2 ".$where;
        $assessmentNum = db('db_erp_new.et_hra_append')
                       ->alias('a')
                       ->field(['e.e_title','p.project_name','h_is_estimate','COUNT(hid) AS assessmentNum'])
                       ->join(' db_erp_new.prj_project p ',' p.project_code = a.h_project_code ','left')
                       ->join(' db_erp_new.cus_enterprise e ',' e.e_code = p.enterprise_code ')
                       ->where($wheresql)
                       ->find();

        //已出报告人数
        $wheresql2 = "a.h_project_code = '$project_code' AND h_is_estimate = 2 AND h_report_status IN (3,4)".$where;
        $reportNum = db('db_erp_new.et_hra_append')
                       ->alias('a')
                       ->field(['e.e_title','p.project_name','h_is_estimate','COUNT(hid) AS assessmentNum'])
                       ->join(' db_erp_new.prj_project p ',' p.project_code = a.h_project_code ','left')
                       ->join(' db_erp_new.cus_enterprise e ',' e.e_code = p.enterprise_code ')
                       ->where($wheresql2)
                       ->find();

        $result['e_title'] = $assessmentNum['e_title'];
        $result['project_name'] = $assessmentNum['project_name'];

        if(!empty($assessmentNum['assessmentNum'])){
            $result['assessmentNum'] = $assessmentNum['assessmentNum'];
        }else{
            $result['assessmentNum'] = 0;
        }

        if(!empty( $reportNum['reportNum'])){
            $result['reportNum'] = $reportNum['reportNum'];
        }else{
            $result['reportNum'] = 0;
        }

        return $result;
        
    }

     /**
     * 获取健康风险评估员工列表
     *
     * @param     string   $project_code    项目编码
     * @return    array    $list            员工列表
     */
    public function get_health_risk_staff_list($project_code,$page,$son_str){
        $list = array();

        if(empty($project_code)){
            return $list;
        }
        $where = '';
        if (isset($son_str)) {
             $where = " AND u.u_son_code in (".$son_str.") ";
        }
        $where = " p.project_code = '$project_code' AND o.order_action_key = 'hra' ".$where;
        $list = db('db_erp_new.ser_order')
                ->alias('o')
                ->field(['h.h_create_time','h.h_report_status','h.h_is_estimate','p.project_name','d.p_name','u.u_job_code','u.u_sex','u.u_identify_type','u.u_identify_code','u.u_name'])
                ->join('db_erp_new.et_hra_append h','h.h_order_code = o.order_code','left')
                ->join('db_erp_new.prj_project p','p.project_code = o.order_project_code','left')
                ->join('db_erp_new.prj_project_user u','u.id = o.order_user_code','left')
                ->join('db_erp_new.prj_product d','d.p_code = o.order_product_code','left')
                ->where($where)
                ->order('o.id DESC')
                ->paginate($page,false,['query' => request()->param()]);

        return $list;
    }

}