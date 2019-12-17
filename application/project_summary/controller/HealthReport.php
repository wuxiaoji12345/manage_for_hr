<?php
namespace app\project_summary\controller;

use app\common\controller\Base;
use app\project_summary\model\EtHraAppend;

class HealthReport extends Base
{
    public function index()
    {
        $project_code = $this->project_code;
        $son_str = $this->son_str;
        $diseaseModel = new EtHraAppend;
        $middleList = $diseaseModel -> get_health_risk_info($project_code,$son_str);
        $title = '健康风险报告';
        $this->assign('title',$title);   
        $this->assign('data',$middleList);
 
        return $this->fetch();
    }
}