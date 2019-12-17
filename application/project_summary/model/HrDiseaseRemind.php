<?php
namespace app\project_summary\model;

use think\Model;


class HrDiseaseRemind extends Model
{
  /**
   * 获取重大阳性信息列表
   * @param       string     $whereSql    SLQ的where条件
   * @param       int        $page        设定分页条目数   
   */
  public function getDiseaseList($page,$whereSql=''){
    
    $result = NULL;

    $where = " 1 ".$whereSql;
    $result = db('db_erp_new.hr_disease_remind')
              ->field(['Id id','prj_code project_code','zx_code','zx_name','dis_name','dis_content','remark','create_time',])
              ->where($where)
              ->order('Id')
              ->paginate($page,false,['query' => request()->param()]);

    return $result;
  }

  /**
   * 获取体检中项列表
   */
  public function getMiddleItemList(){
    
      $result = NULL;

      $result = db('db_erp_new.et_product_item')
                ->field(['item_code value','item_name name'])
                ->where(' item_level = 2 ')
                ->select();

      return $result;
  }

}