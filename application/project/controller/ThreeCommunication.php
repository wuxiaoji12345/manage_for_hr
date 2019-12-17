<?php

namespace app\project\controller;

use app\common\controller\Base;
use think\Db;

class ThreeCommunication extends Base
{
    public function index()
    {
        $contact_type_list=get_dictionaries_list('contact_type',1);
        $this->assign('contact_type_list', $contact_type_list);
        $this->assign('title', '三方通讯录');
        return $this->fetch();
    }

    public function hr_contact_type($type = 0)
    {
        $list = [];
        $count = 0;
        $table_title = "";
        if (!empty($type)) {
            $project_code = $this->project_code;
            $table_title = get_dictionaries_list('contact_type',1,$type);
            $whereSql = 'c.c_type = '.$type.' AND c.c_status = 1';
            $list = Db::table('db_erp_new.hr_contact')
                ->alias('c')
                ->field("c.c_type,c.c_company,c.c_person,c.c_tel,c.c_cell,c.c_email,c.c_do,c.c_remark,c.c_status,p.project_name,e.e_name")
                ->leftJoin('db_erp_new.prj_project p', 'c.prj_code = p.project_code')
                ->leftJoin('db_erp_new.cus_enterprise e', 'c.enterprise_code = e.e_code')
                ->where($whereSql . ' AND c.prj_code ="' . $project_code . '"')
                ->order('c.id DESC')
                ->limit(5)
                ->paginate(5);
            $count = $list->total();
        }
        $this->assign('list', $list);
        $this->assign('count', $count);
        $this->assign('table_title', $table_title);
        return $this->fetch('hr_contact_type');
    }

}