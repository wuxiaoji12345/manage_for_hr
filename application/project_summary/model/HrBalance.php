<?php
namespace app\project_summary\model;

use think\Model;

class HrBalance extends Model
{
  /**
  * 获取某项目对账单清单
  *
  * @param       string     $p_code      项目编码
  * @param       int        $page        设定分页条目数
  * @return      array      $list        清单列表
  */
  public function getBalenceList($p_code,$page,$son_str){
    if(empty($p_code)){
        return $list;
    }

    $where = '';
    // if (isset($son_str)) {
    //     $where = " AND p.enterprise_code in (".$son_str.") ";
    // }    

    $where = " b.prj_code = '$p_code' ".$where;
    $list = db('db_erp_new.hr_balance')
              ->alias('b')
              ->join(' db_erp_new.cus_enterprise e ',' e.e_code = b.enterprise_code ','left')
              ->join(' db_erp_new.prj_project p ',' p.project_code = b.prj_code ','left')
              ->field(['b.*','p.project_name','p.project_status','e_title'])
              ->where($where)
              ->order('b.Id')
              ->paginate($page,false,['query' => request()->param()]);

    return $list;
  }
}