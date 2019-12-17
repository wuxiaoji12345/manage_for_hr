<?php
namespace app\project_summary\model;

use think\Model;


class HrTuanbao extends Model
{
  /**
  * 获取某项目团检报告清单
  *
  * @param       string     $p_code      项目编码
  * @return      array      $list        清单列表
  */
  public function getReportList($p_code,$page,$son_str){
    if(empty($p_code)){
        return $list;
    }
    $where = '';
    // if (isset($son_str)) {
    //      $where = " AND t.enterprise_code in (".$son_str.") ";
    // }    
    $list = db('db_erp_new.hr_tuanbao')
              ->alias('t')
              ->field(['t.*','p.project_name','p.project_status','e_title'])
              ->join(' db_erp_new.cus_enterprise e ',' e.e_code = t.enterprise_code ','left')
              ->join(' db_erp_new.prj_project p ',' p.project_code = t.prj_code ','left')
              ->where(" t.prj_code = '$p_code' ".$where)
              ->order('t.Id')
              ->paginate($page,false,['query' => request()->param()]);

    return $list;
    }
}