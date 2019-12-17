<?php

namespace app\index\controller;
use app\common\controller\Base;
use think\Db;
use think\facade\Session;

class Index extends Base
{
    public function index()
    {
        $this->assign('title', '首页');
        return $this->fetch();
    }
    
    //修改密码
    public function changePassword()
    {   
    	$request = $this->request->param();
    	unset($request['s']);
    	
    	if(!empty($request)){
	    	$hr_code = $this->app_id;
	        $old_password = $request['old_password'];
	        $new_password = $request['new_password'];

	        if (!isset($old_password) || empty($old_password)) {
               return $this->error('原密码不能为空','index/changePassword');
	        }

	        if (!isset($new_password) || empty($new_password)) {
               return $this->error('新密码不能为空','index/changePassword');
	        }

	        if ($old_password == $new_password) {
	           return $this->error('新密码和原密码不能相同','index/changePassword');
	        }

	        $hrInfo = array();

	        $hrInfo = Db::table('db_erp_new.prj_project_hr')
	           ->alias('h')
	           ->field('h.hr_id,h.hr_project_code,h.hr_account,h.hr_level,h.hr_password,h.hr_name,h.hr_auth,h.hr_ext_code,h.hr_status,h.hr_remark,j.project_name,e.e_code,e.e_name')
	           ->join(' db_erp_new.prj_project j ',' h.hr_project_code = j.project_code ','left')
	           ->join(' db_erp_new.cus_enterprise e ',' j.enterprise_code = e.e_code ','left')
	           ->where(" h.hr_id = $hr_code ")
	           ->find();
	        //验证帐号
	        if (intval($hrInfo['hr_status']) != 1) {
	           return $this->error('当前账号已被禁用','index/changePassword');
	        }

	        //验证帐号密码
	        if (md5($old_password) != $hrInfo['hr_password']) {
	           return $this->error('原密码输入错误','index/changePassword');
	        }

	        //初始化修改数据
	        $projectUserData = array();

	        $projectUserData['hr_password'] = md5($new_password);
	        $projectUserData['update_time'] = date("Y-m-d H:i:s", time());

	        //修改信息
	        $updateResult = Db::name('db_erp_new.prj_project_hr') -> where(" hr_id = ' $hr_code ' ") ->update($projectUserData);

	        if (intval($updateResult) <= 0) {
	           return $this->error('修改密码失败','index/changePassword');
	        }
            //成功跳转至首页    
            return $this->success('修改密码成功，正在为您跳转...','Index/index','',2);
    	} else {
	        $title = '修改密码';
	        $this->assign('title',$title);      		
    		return $this->fetch();
    	}

    }

    public function login()
    {
        if (request()->isPost()) {
            $account = trim(input("account"));
            $password = trim(input("password"));

            if (empty($account)) {
                return msg_return("请输入登陆账号", -1);
            }

            if (empty($password)) {
                return msg_return("请输入登陆密码", -1);
            }

            $userInfo = Db::table('db_erp_new.prj_project_hr')
                ->alias('h')
                ->field('	h.hr_id,h.hr_project_code,h.hr_account,h.hr_password,h.hr_name,h.hr_auth,h.hr_ext_code,h.hr_status,h.hr_remark,j.project_name,e.e_code,e.e_name')
                ->leftJoin('db_erp_new.prj_project j', 'h.hr_project_code = j.project_code')
                ->leftJoin('db_erp_new.cus_enterprise e', 'j.enterprise_code = e.e_code')
                ->leftJoin('db_erp_new.cus_son_enterprise son', 'j.enterprise_code = e.e_code')
                ->where("h.hr_account = '$account' ")
                ->find();

            if (empty($userInfo)) {
                return msg_return("未查询到账号信息", -1);
            }

            //验证帐号
            if (intval($userInfo['hr_status']) != 1) {
                return msg_return("当前账号状态不能登录", -1);
            }

            //验证帐号密码
            if (md5($password) != $userInfo['hr_password']) {
                return msg_return("账号密码错误", -1);
            }

            if (!empty($userInfo)) {
                Session::clear();
                Session::set('userInfo', $userInfo);
                $projectUserData['update_time'] = date("Y-m-d H:i:s", time());
                //修改登录信息
                $updateResult = Db::table("db_erp_new.prj_project_hr")->where('hr_id="' . $userInfo['hr_id'] . '"')->update($projectUserData);
                if (intval($updateResult) <= 0) {
                    return msg_return("修改登录信息失败", -1);
                }
                //组装需要的登陆信息
                Session::set('project_code', $userInfo['hr_project_code']);
                Session::set('app_id', $userInfo['hr_id']);
                return msg_return("登陆成功");
            } else {
                return msg_return("账号或者密码错误", -1);
            }
        }
        return $this->fetch();
    }

    public function loginOut()
    {
        Session::clear();
        $this->redirect(url('index/index/login'));
    }

}
