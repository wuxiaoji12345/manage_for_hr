<?php
/**
 * 存储各个模块需要用到的信息
 */

namespace app\common\controller;

use think\Controller;
use think\Db;
use think\facade\Session;

class Base extends Controller
{
    public $app_id;
    public $project_code;
    public $e_name;
    public $son_str;

    public function initialize()
    {

        parent::initialize();
        $module_name = request()->module();
        $controller_name = request()->controller();
        $action = request()->action();
        $active_url = $module_name . '/' . $controller_name . '/' . $action;
        //如果有url来源并且是从new erp过来的链接自动登录
        //允许直接访问的url
        $allowNotLoginUrl = [
            'index/Index/login'
        ];
        if (in_array($active_url, $allowNotLoginUrl)) {
            //如果有session直接跳转首页 防止重复登陆
            if (!empty(session('app_id'))) {
                $this->redirect(url('/index/Index/index'));
            }
        } else {
            if (null === session('app_id')) {
                //如果用户没有登陆直接跳转到用户的登陆页面
                $this->redirect(url('/index/Index/login'));
            }
        }
        if (!empty(session('app_id'))) {
            $this->app_id = Session::get('app_id');
            $this->project_code = Session::get('project_code');
            $this->e_name = Session::get('userInfo.e_name');
            //加载菜单
            $hr_code = $this->app_id;
            $project_code = $this->project_code;
            //三级菜单ID
            $authCodeStr = Db::table('db_erp_new.prj_project_hr')
                ->alias('h')
                ->where('h.hr_id =' . $hr_code . " and h.hr_project_code ='" . $project_code . "'")
                ->value('h.hr_auth');
            $menuList = array();
            if (!empty($authCodeStr)) {
                $whereSql = '';

                if ($authCodeStr != 'ALL') {
                    $authCodeArray = unserialize($authCodeStr);
                    $whereSql = " m.id IN ('" . implode("','", $authCodeArray) . "') ";
                }
                $subQuery = Db::table('db_erp_new.hr_menu')
                    ->alias('m')
                    ->field('m.m_parent_id')
                    ->where($whereSql . " AND m_status = 1")
                    ->group('m.m_parent_id')
                    ->buildSql();
                $oneMenuList = Db::table('db_erp_new.hr_menu')
                    ->field('id,m_name,m_parent_id,m_sort,m_url,m_remark,m_status,icon')
                    ->where("id in (" . $subQuery . ")")
                    ->where('m_status', '=', 1)
                    ->order('m_sort')
                    ->select();
                $secondMenuList = Db::table('db_erp_new.hr_menu')
                    ->alias('m')
                    ->field('m.id,m.m_name,m.m_parent_id,m.m_sort,m.m_url,m.m_remark,m.m_status,icon')
                    ->where($whereSql . " AND m.m_status = 1")
                    ->order('m_sort')
                    ->select();
                //每个菜单的路径集合
                $menu_url_arr = [];
                //循环一级菜单
                foreach ($oneMenuList as $key => $value) {

                    $menuList[$key] = $value;

                    //循环二级菜单
                    foreach ($secondMenuList as $k => $v) {
                        if (!empty($v['m_url'])) {
                            $menu_url_arr[$v['m_parent_id']][$v['id']] = $v['m_url'];
                        }
                        if ($menuList[$key]['id'] == $v['m_parent_id']) {
                            $menuList[$key]['menu_list'][] = $v;
                        }
                    }
                }
            }
            $this->assign('menuList', $menuList);
            //子公司列表
            $son_list = Db::table('db_erp_new.prj_hr_sonenter_relation')
                ->alias('t1')
                ->leftJoin('db_erp_new.cus_son_enterprise t2', 't2.s_code = t1.s_enter_code')
                ->where('t1.status=1 and t1.hr_id = ' . $hr_code)
                ->column('t2.s_code');
            //是否有子公司
            if(!empty($son_list)){
                $in_son_str=implode(",",$son_list);
                $this->son_str = "'".str_replace(",","','",$in_son_str)."'";
            }else{
                $this->son_str="";
            }
        }

    }
}