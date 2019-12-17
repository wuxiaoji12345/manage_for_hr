<?php
namespace app\project_summary\controller;

use app\common\controller\Base;
use app\project_summary\model\EtHraAppend;

class AssessmentNumberList extends Base
{

  public function index()
  {
    $page = 20;
    $project_code = $this->project_code;
    $son_str = $this->son_str;
    $request = $this->request->param();
    $diseaseModel = new EtHraAppend;
    $middleList = $diseaseModel -> get_health_risk_staff_list($project_code,$page,$son_str);
    $data = $middleList -> toarray()['data'];
    if(!empty($data)){
      foreach ($data as $key => $value) {
        $data[$key]['id'] = $key + 1 + ((isset($request['page'])?$request['page']:1) - 1)*$page;            
        switch ($value['u_identify_type']) {
          case '1':
          $data[$key]['u_identify_type'] = '身份证';
          break;
          case '2':
          $data[$key]['u_identify_type'] = '护照';
          break;
          case '3':
          $data[$key]['u_identify_type'] = '军官证';
          break;
          case '4':
          $data[$key]['u_identify_type'] = '其他';
          break;                                        
          default:
          break;
        }

        switch ($value['u_sex']) {
          case '1':
          $data[$key]['u_sex'] = '男性';
          break;
          case '2':
          $data[$key]['u_sex'] = '女性';
          break;
          case '3':
          $data[$key]['u_sex'] = '保密';
          break;                                    
          default:
          break;
        }

        switch ($value['h_report_status']) {
          case '1':
          $data[$key]['h_report_status'] = '未生成';
          break;
          case '2':
          $data[$key]['h_report_status'] = '待生成';
          break;
          case '3':
          $data[$key]['h_report_status'] = '已生成';
          break;
          case '4':
          $data[$key]['h_report_status'] = '已生成可下载';
          break;
          case '5':
          $data[$key]['h_report_status'] = '生成失败';
          break; 
          case '9':
          $data[$key]['h_report_status'] = '已取消';
          break;    
          default:
          break;
        }

      }
    } else {
      $data = [];
    }
    $title = '评估人数列表';
    $this->assign('title',$title);
    $this->assign('middleList',$middleList);
    $this->assign('data',$data);

    return $this->fetch();
  }
}