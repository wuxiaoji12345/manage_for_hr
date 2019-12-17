<?php

namespace app\project\controller;

use app\common\controller\Base;
use think\Db;

class Contral extends Base
{
    public function index()
    {
        $project_code = $this->project_code;
        $erp_domain = config('app.ERP_DOMAIN');
        $whereSql='c.cp_code="' . $project_code . '"';
        $list = Db::table('db_erp_new.prj_contract')
            ->alias('c')
            ->field("c.cc_id as id,p.project_code,p.project_name,p.project_begin_time,p.project_end_time,project_status,c.file_path")
            ->leftJoin('db_erp_new.prj_project p', 'p.project_code=c.cp_code')
            ->where($whereSql)
            ->limit(10)
            ->paginate(10);
        $count = $list->total();
        $for_list = $list->all();
        if (!empty($for_list)) {
            foreach ($for_list as $k => $v) {
                $for_list[$k]['status_name'] =get_dictionaries_list('project_status',1,$v['project_status']);
                if (!empty($v['file_path'])) {
                    $for_list[$k]['file_path'] = $erp_domain . 'upload' . $v['file_path'];
                }
                $list->offsetSet($k, $for_list[$k]);
            }
        }
        $this->assign('title', '合同管理');
        $this->assign('list', $list);
        $this->assign('count', $count);
        return $this->fetch();
    }

}