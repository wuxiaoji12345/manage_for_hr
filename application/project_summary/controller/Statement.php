<?php
namespace app\project_summary\controller;

use app\common\controller\Base;
use app\project_summary\model\HrBalance;
// use think\Request;

class Statement extends Base
{
    public function index()
    {
        $result = array();
        $project_code = $this->project_code;
        $son_str = $this->son_str;
        $request = $this->request->param();
        $page = 20;
        //项目编码
        if(!isset($project_code) && empty($project_code)){
            $balance_list = 0;
        } else {            
            $balanceModel = new HrBalance;

            $list = $balanceModel -> getBalenceList($project_code,$page,$son_str);
            $balance_list = $list -> toarray();
            $balance_list = $balance_list['data']; 

            foreach ($balance_list as $key => $value){
                $balance_list[$key]['id'] = $key + 1 + ((isset($request['page'])?$request['page']:1) - 1)*$page;
                if(!empty($value['b_file_path'])){
                    $balance_list[$key]['download'] = config("app.ERP_DOMAIN").'upload'.$value['b_file_path'];
                    $balance_list[$key]['download_word'] = '下载';

                } else {
                    $balance_list[$key]['download'] = '';
                    $balance_list[$key]['download_word'] = '';
                }
                
                switch ($balance_list[$key]['project_status']) {
                    case '1':
                    $balance_list[$key]['project_status'] = '预录入';
                    break;
                    case '2':
                    $balance_list[$key]['project_status'] = '待报价';
                    break;                    
                    case '3':
                    $balance_list[$key]['project_status'] = '已报价';
                    break;
                    case '4':
                    $balance_list[$key]['project_status'] = '已立项';
                    break;
                    case '5':
                    $balance_list[$key]['project_status'] = '测试中（平台开发中）';
                    break;
                    case '6':
                    $balance_list[$key]['project_status'] = '启动中';
                    break;
                    case '7':
                    $balance_list[$key]['project_status'] = '已结束';
                    break;
                    case '8':
                    $balance_list[$key]['project_status'] = '预约结束';
                    break; 
                    case '9':
                    $balance_list[$key]['project_status'] = '对账';
                    break; 
                    case '10':
                    $balance_list[$key]['project_status'] = '开票';
                    break; 
                    case '11':
                    $balance_list[$key]['project_status'] = '回款';
                    break; 
                    case '12':
                    $balance_list[$key]['project_status'] = '已暂停';
                    break;                                                    
                    default:
                    break;
                }
            }
        }
        $title = '对账列表';
        $this->assign('title',$title);           
        $this->assign('balance_list',$balance_list);
        $this->assign('appendList',$list);
        return $this->fetch();
    }
}