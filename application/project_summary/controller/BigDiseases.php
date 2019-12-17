<?php
namespace app\project_summary\controller;

use app\common\controller\Base;
use app\project_summary\model\HrDiseaseRemind;

class BigDiseases extends Base
{

    public function index()
    {
        //疾病分类
        $diseaseModel = new HrDiseaseRemind;
        $middleList = $diseaseModel -> getMiddleItemList();       
        //阳性疾病表单
        $request = $this->request->param();
        $page = 20;
        $project_code = $this->project_code;
        $searchData = array('zx_code'=>'','dis_name'=>'');
        //疾病分类code
        $whereSql = " AND prj_code  = '$project_code' ";
        if (isset($request['zx_code']) && !empty($request['zx_code'])) {
            $zx_code = $request['zx_code'];
            $whereSql .= " AND zx_code  = '{$zx_code}' ";
            $searchData['zx_code'] = $request['zx_code'];
        }
        //疾病阳性名称
        if (isset($request['dis_name']) && !empty($request['dis_name'])) {
            $dis_name = $request['dis_name'];
            $whereSql .= " AND dis_name like '%{$dis_name}%' ";
            $searchData['dis_name'] = $request['dis_name'];
        }
        $diseaseList = $diseaseModel->getDiseaseList($page,$whereSql);
        $data = $diseaseList -> toarray()['data'];
        foreach ($data as $key => $value) {
            $data[$key]['id'] = $key + 1 + ((isset($request['page'])?$request['page']:1) - 1)*$page;            
        }
        $title = '重大阳性提醒';
        $this->assign('title',$title);        
        $this->assign('middleList',$middleList);
        $this->assign('data',$data);
        $this->assign('diseaseList',$diseaseList);
        $this->assign('searchData',$searchData);
        return $this->fetch();
    }
}