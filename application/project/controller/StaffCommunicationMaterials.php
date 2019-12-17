<?php
namespace app\project\controller;

use app\common\controller\Base;
use think\Db;

class StaffCommunicationMaterials extends Base
{
    public function index()
    {
        $project_code=$this->project_code;
        $erp_domain=config('app.ERP_DOMAIN');
        $whereSql='f.prj_code="'.$project_code.'"';
        $list = Db::table('db_erp_new.hr_contact_file')
            ->alias('f')
            ->field('f.id,f.file_name,f.file_path,f.file_upload_time')
            ->leftJoin('db_erp_new.prj_project p', 'p.project_code=f.prj_code')
            ->where($whereSql)
            ->order('f.Id DESC')
            ->limit(10)
            ->paginate(10);
        $count=$list->total();
        $for_list=$list->all();
        if (!empty($for_list)) {
            foreach ($for_list as $k => $v) {
                if(!empty($v['file_path'])){
                    $for_list[$k]['file_path'] = $erp_domain.'upload'.$v['file_path'];
                }
                $list->offsetSet($k,$for_list[$k]);
            }
        }
        $this->assign('title', '员工沟通资料');
        $this->assign('list', $list);
        $this->assign('count', $count);
        return view('index');
    }

}