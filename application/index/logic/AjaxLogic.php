<?php
namespace app\index\logic;

use app\index\model\Base;

class AjaxLogic extends Base
{

    /**
     * 获取供应商列表
     */
    public function areaList($params='',$level=3)
    {
        $where = [];

        $where[] = ['area_level','=',$level];

        foreach ($params as $key => $value) {
            # code...
            switch ($key) {

                case 'area_name':
                    # code...
                    if($value)
                        $where[] = ['area_short_name','like','%'.$value.'%'];
                    break;
                case 'area_code':
                    # code...
                    if($value)
                        $where[] = ['id','=',$value];
                    break;
                case 'parent_code':
                    # code...
                    if($value)
                        $where[] = ['area_parent_id','=',$value];
                    break;

                default:
                    # code...
                    break;
            }
        }

        return $this->table('db_etong_base.et_region')->where($where)->select();

    }

}