<?php
namespace app\process\controller;

use app\common\controller\Base;
use think\Db;

class ComplaintConsultation extends Base
{
    public function initialize()
    {
        parent::initialize();
        $complaint_type = [
            ['id' => 1, 'name' => '投诉'],
            ['id' => 2, 'name' => '咨询'],
            ['id' => 3, 'name' => '索赔'],
        ];

        $complaint_topicid = get_dictionaries_list('topic',1);

        $complaint_status = [
            ['id' => 1, 'name' => '待处理'],
            ['id' => 2, 'name' => '无效'],
            ['id' => 3, 'name' => '处理完成'],
        ];

        $this->assign('complaint_type', $complaint_type);
        $this->assign('complaint_status', $complaint_status);
        $this->assign('complaint_topicid', $complaint_topicid);
    }

    public function index()
    {
        $project_code = $this->project_code;
        $whereSql = 'c.complaint_project_code ="' . $project_code . '" ';
        //初始化页面参数
        $searchData['complaint_type']=0;
        $searchData['complaint_topicid']=0;
        $searchData['u_name']="";
        $searchData['complaint_status']=0;
        $searchData['starttime']="";
        $searchData['endtime']="";
        if (request()->isPost()||request()->isGet()) {
            $complaint_type = intval(input('request.complaint_type'));
            $complaint_topicid = intval(input('request.complaint_topicid'));
            $u_name = input('request.u_name');
            $complaint_status = intval(input('request.complaint_status'));
            $starttime = input('request.start_time');
            $endtime = input('request.end_time');

            if (!empty($complaint_type)) {
                $searchData['complaint_type']=$complaint_type;
                $whereSql .= ' AND c.complaint_type =' . $complaint_type ;
            }
            if (!empty($complaint_topicid)) {
                $searchData['complaint_topicid']=$complaint_topicid;
                $whereSql .= ' AND c.complaint_topicid =' . $complaint_topicid ;
            }
            if (!empty($u_name)) {
                $searchData['u_name']=$u_name;
                $whereSql .= ' AND u.u_name like \'%' . $u_name . '%\' ';
            }
            if (!empty($complaint_status)) {
                $searchData['complaint_status']=$complaint_status;
                $whereSql .= ' AND c.complaint_status =' . $complaint_status ;
            }
            if (!empty($starttime)) {
                $searchData['start_time']=$starttime;
                $whereSql .= " AND c.complaint_addtime >='" . $starttime."'";
            }
            if (!empty($endtime)) {
                $searchData['end_time']=$endtime;
                $whereSql .= " AND c.complaint_addtime <='" . $endtime."'";
            }
        }
        $son_str=$this->son_str;
        if(!empty($son_str)){
            $whereSql.=" AND u.u_son_code in (".$son_str.")";
        }
       $list = Db::table('db_erp_new.ser_complaint')
            ->alias('c')
            ->field('c.*,u.u_name')
            ->leftJoin('db_erp_new.prj_project_user u', 'u.id = c.complaint_member_id')
            ->where($whereSql)
            ->order('c.complaint_id DESC')
            ->limit(10)
            ->paginate(10);
         $count = $list->total();
         $this->assign('title', '投诉咨询管理');
         $this->assign('list', $list);
         $this->assign('count', $count);
         $this->assign('searchData', $searchData);

        return $this->fetch();
    }

    /**
     * 详情页面
     */
    public function detail ($id=0)
    {
        if($id==0){
            //跳回列表页
            return $this->redirect('/process/ComplaintConsultation/index');
        }else{
            //拿到详情页面id
            $complaint_id=$id;
            $data = Db::table('db_erp_new.ser_complaint')
                ->alias('c')
                ->field('c.complaint_id,c.complaint_type,c.complaint_topicid,c.complaint_member_gh,c.complaint_title,c.complaint_content,c.complaint_addtime,c.complaint_status,c.last_plan,u.u_name,u.u_identify_code,u_mobile')
                ->leftJoin('db_erp_new.prj_project_user u', 'u.id = c.complaint_member_id')
                ->where('c.complaint_id ='.$complaint_id)
                ->find();
            $this->assign('title', '投诉咨询管理详情');
            $this->assign('data', $data);
            return $this->fetch();
        }
    }

}