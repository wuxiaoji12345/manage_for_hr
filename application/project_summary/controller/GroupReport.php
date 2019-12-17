<?php
namespace app\project_summary\controller;

use app\common\controller\Base;
use app\project_summary\model\HrTuanbao;

class GroupReport extends Base
{
    public function index()
    {
        $result = array();
        $project_code = $this->project_code;
        $son_str = $this->son_str;
        $page = 20;
        $request = $this->request->param();
        //项目编码
        if(!isset($project_code) && empty($project_code)){
            $report_list = 0;
        } else {
            $gReportModel = new HrTuanbao;
            $list = $gReportModel -> getReportList($project_code,$page,$son_str);
            $report_list = $list -> toarray();
            $report_list = $report_list['data']; 
            foreach ($report_list as $key => $value){
                $report_list[$key]['id'] = $key + 1 + ((isset($request['page'])?$request['page']:1) - 1)*$page;
                if(!empty($value['t_file_path'])){
                    $report_list[$key]['download'] = config("app.ERP_DOMAIN").'upload'.$value['t_file_path'];
                    $report_list[$key]['download_word'] = '下载';

                } else {
                    $report_list[$key]['download'] = '';
                    $report_list[$key]['download_word'] = '';
                }

                switch ($report_list[$key]['project_status']) {
                    case '1':
                    $report_list[$key]['project_status'] = '预录入';
                    break;
                    case '2':
                    $report_list[$key]['project_status'] = '待报价';
                    break;                    
                    case '3':
                    $report_list[$key]['project_status'] = '已报价';
                    break;
                    case '4':
                    $report_list[$key]['project_status'] = '已立项';
                    break;
                    case '5':
                    $report_list[$key]['project_status'] = '测试中（平台开发中）';
                    break;
                    case '6':
                    $report_list[$key]['project_status'] = '启动中';
                    break;
                    case '7':
                    $report_list[$key]['project_status'] = '已结束';
                    break;
                    case '8':
                    $report_list[$key]['project_status'] = '预约结束';
                    break; 
                    case '9':
                    $report_list[$key]['project_status'] = '对账';
                    break; 
                    case '10':
                    $report_list[$key]['project_status'] = '开票';
                    break; 
                    case '11':
                    $report_list[$key]['project_status'] = '回款';
                    break; 
                    case '12':
                    $report_list[$key]['project_status'] = '已暂停';
                    break;                                                      
                    default:
                    break;
                }
            }
        }
        $title = '团检报告';
        $this->assign('title',$title);           
        $this->assign('report_list',$report_list);
        $this->assign('appendList',$list);
        return $this->fetch();
    }
}