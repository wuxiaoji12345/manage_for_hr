<?php
namespace app\index\controller;

use app\common\controller\Base;
use app\index\logic\AjaxLogic;
use think\Request;

class Ajax extends Base
{
//  获取省份数据
    public function provinceList(Request $request)
    {

        $params = [];

        $params['area_name'] = $request->post('area_name');

        $params['area_code'] = $request->post('area_code');

        $params['parent_code'] = $request->post('parent_code');

        $model = new AjaxLogic();

        $list = $model->areaList($params,2);

        if($list->isEmpty()){
            return msg_return('数据为空',201,$list);
        }
        return msg_return('获取成功',0,$list);
    }


    /**
     * 获取市数据
     */
    public function cityList(Request $request)
    {

        $params = [];

        $params['area_name'] = $request->post('area_name');

        $params['area_code'] = $request->post('area_code');

        $params['parent_code'] = $request->post('parent_code');

        if(empty($params['parent_code']))
            return msg_return('参数不正确',202);

        $model = new AjaxLogic();

        $list = $model->areaList($params,3);

        if($list->isEmpty()){
            return msg_return('数据为空',201,$list);
        }
        return msg_return('获取成功',0,$list);

    }

    /**
     * 获取区数据
     */
    public function areaList(Request $request)
    {
        $params = [];

        $params['area_name'] = $request->post('area_name');

        $params['area_code'] = $request->post('area_code');

        $params['parent_code'] = $request->post('parent_code');

        if(empty($params['parent_code']))
            return msg_return('参数不正确',202);


        $model = new AjaxLogic();

        $list = $model->areaList($params,4);

        if($list->isEmpty()){
            return msg_return('数据为空',201,$list);
        }
        return msg_return('获取成功',0,$list);

    }
}