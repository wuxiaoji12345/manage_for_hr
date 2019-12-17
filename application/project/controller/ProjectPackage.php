<?php

namespace app\project\controller;

use app\common\controller\Base;
use think\Db;

class ProjectPackage extends Base
{

    public function index()
    {
        $project_code = $this->project_code;
        $whereSql = "`p_project_code` = '$project_code' AND `p_type_code` = 'append' AND `p_status` = '1'";
        $list = Db::table('db_erp_new.prj_product')
            ->field('id,p_name,p_sex_limit,p_marry_limit,p_code,p_type_limit')
            ->where($whereSql)
            ->order('id')
            ->limit(10)
            ->paginate(10);
        $count = $list->total();
        $for_list = $list->all();
        if (!empty($for_list)) {
            foreach ($for_list as $k => $v) {
                switch ($v['p_sex_limit']) {
                    case 1:
                        $for_list[$k]['sex_limit'] = '仅限男性';
                        break;
                    case 2:
                        $for_list[$k]['sex_limit'] = '仅限女性';
                        break;
                    case 0:
                        $for_list[$k]['sex_limit'] = '无限制';
                        break;
                    default:
                        $for_list[$k]['sex_limit'] = "";
                        break;
                }
                switch ($v['p_marry_limit']) {
                    case 1:
                        $for_list[$k]['marry_limit'] = '仅限未婚';
                        break;
                    case 2:
                        $for_list[$k]['marry_limit'] = '仅限已婚';
                        break;
                    case 0:
                        $for_list[$k]['marry_limit'] = '无限制';
                        break;
                    default:
                        $for_list[$k]['marry_limit'] = "";
                        break;
                }
                switch ($v['p_type_limit']) {
                    case 1:
                        $for_list[$k]['type_limit'] = '仅员工可选';
                        break;
                    case 2:
                        $for_list[$k]['type_limit'] = '仅家属可选';
                        break;
                    case 0:
                        $for_list[$k]['type_limit'] = '无限制';
                        break;
                    default:
                        $for_list[$k]['type_limit'] = "";
                        break;
                }
                $list->offsetSet($k, $for_list[$k]);
            }
        }
        $this->assign('title', '项目套餐');
        $this->assign('list', $list);
        $this->assign('count', $count);

        return $this->fetch();
    }

    public function product_detail($p_code = "")
    {
        if (empty($p_code)) {
            //跳回列表页
            return $this->redirect('/project/ProjectPackage/index');
        } else {
            $whereSql = "`product_code` = '$p_code'";
            $subQuery = Db::table('db_erp_new.prj_product_item_relation')
                ->field('item_code')
                ->where($whereSql)
                ->buildSql();
            $list = Db::table('db_erp_new.et_product_item')
                ->alias('t1')
                ->field('t1.id,t2.item_name AS middle_item_name,t1.item_name AS small_item_name,t1.item_mean,t1.item_sex_limit,t1.item_marry_limit,t2.item_code AS middle_item_code')
                ->leftJoin('db_erp_new.et_product_item t2', 't1.item_parent_code = t2.item_code')
                ->leftJoin('db_erp_new.et_product_item t3', 't2.item_parent_code = t3.item_code')
                ->where('t1.item_code IN' . $subQuery)
                ->order('middle_item_code,t1.id ASC')
                ->limit(10)
                ->paginate(10);
            $count = $list->total();
            $for_list = $list->all();
            if (!empty($for_list)) {
                foreach ($for_list as $k => $v) {
                    switch ($v['item_sex_limit']) {
                        case 3:
                            $for_list[$k]['man'] = "★";
                            break;
                        case 1:
                            $for_list[$k]['man'] = "★";
                            break;
                        default:
                            $for_list[$k]['man'] = "";
                            break;
                    }

                    if ($v['item_sex_limit'] == 3 || $v['item_marry_limit'] == 3) {
                        $for_list[$k]['w_women'] = "★";
                    } else if ($v['item_sex_limit'] == 3 || $v['item_marry_limit'] == 1) {
                        $for_list[$k]['w_women'] = "★";
                    } else if ($v['item_sex_limit'] == 2 || $v['item_marry_limit'] == 3) {
                        $for_list[$k]['w_women'] = "★";
                    } else if ($v['item_sex_limit'] == 2 || $v['item_marry_limit'] == 1) {
                        $for_list[$k]['w_women'] = "★";
                    } else {
                        $for_list[$k]['w_women'] = "";
                    }

                    if ($v['item_sex_limit'] == 3 || $v['item_marry_limit'] == 3) {
                        $for_list[$k]['y_women'] = "★";
                    } else if ($v['item_sex_limit'] == 3 || $v['item_marry_limit'] == 2) {
                        $for_list[$k]['y_women'] = "★";
                    } else if ($v['item_sex_limit'] == 2 || $v['item_marry_limit'] == 2) {
                        $for_list[$k]['y_women'] = "★";
                    } else if ($v['item_sex_limit'] == 2 || $v['item_marry_limit'] == 3) {
                        $for_list[$k]['y_women'] = "★";
                    } else {
                        $for_list[$k]['y_women'] = "";
                    }
                    $list->offsetSet($k, $for_list[$k]);
                }
            }
            $this->assign('title', '套餐详情');
            $this->assign('list', $list);
            $this->assign('count', $count);
            return $this->fetch();
        }
    }

    public function addon_list($p_code = "")
    {
        if (empty($p_code)) {
            //跳回列表页
            return $this->redirect('/project/ProjectPackage/index');
        } else {
            $whereSql = "r.`pa_product_code` = '$p_code'";
            $list = Db::table('db_erp_new.prj_product_addon_relation')
                ->alias('r')
                ->field('a.p_name,a.p_detail,a.p_employee_price,a.p_code')
                ->leftJoin('db_erp_new.`prj_addon_package` a', 'a.p_code = r.pa_addon_code ')
                ->where($whereSql)
                ->order('a.id DESC')
                ->limit(10)
                ->paginate(10);
            $count = $list->total();
            $this->assign('title', '支持加项');
            $this->assign('p_code', $p_code);
            $this->assign('list', $list);
            $this->assign('count', $count);
            return $this->fetch();
        }
    }

    public function addon_list_detail($p_code = "")
    {
        if (empty($p_code)) {
            //跳回列表页
            return $this->redirect('/project/ProjectPackage/index');
        } else {
            $whereSql = " `ak_code` = '$p_code'";
            $subQuery = Db::table('db_erp_new.prj_addon_item_relation')
                ->field('item_code')
                ->where($whereSql)
                ->buildSql();
            $list = Db::table('db_erp_new.et_product_item')
                ->alias('t1')
                ->field("	t1.id,t2.item_code AS middle_item_code,t1.item_name AS small_item_name,t1.item_mean,t1.item_sex_limit,t1.item_marry_limit")
                ->leftJoin('db_erp_new.`et_product_item` t2', 't1.item_parent_code = t2.item_code')
                ->leftJoin('db_erp_new.`et_product_item` t3', 't2.item_parent_code = t3.item_code')
                ->where('t1.item_code IN (' . $subQuery . ')')
                ->order('middle_item_code,t1.id ASC')
                ->limit(10)
                ->paginate(10);
            $count = $list->total();
            $for_list = $list->all();
            if (!empty($for_list)) {
                foreach ($for_list as $k => $v) {
                    switch ($v['item_sex_limit']) {
                        case 3:
                            $for_list[$k]['man'] = "★";
                            break;
                        case 1:
                            $for_list[$k]['man'] = "★";
                            break;
                        default:
                            $for_list[$k]['man'] = "";
                            break;
                    }

                    if ($v['item_sex_limit'] == 3 || $v['item_marry_limit'] == 3) {
                        $for_list[$k]['w_women'] = "★";
                    } else if ($v['item_sex_limit'] == 3 || $v['item_marry_limit'] == 1) {
                        $for_list[$k]['w_women'] = "★";
                    } else if ($v['item_sex_limit'] == 2 || $v['item_marry_limit'] == 3) {
                        $for_list[$k]['w_women'] = "★";
                    } else if ($v['item_sex_limit'] == 2 || $v['item_marry_limit'] == 1) {
                        $for_list[$k]['w_women'] = "★";
                    } else {
                        $for_list[$k]['w_women'] = "";
                    }

                    if ($v['item_sex_limit'] == 3 || $v['item_marry_limit'] == 3) {
                        $for_list[$k]['y_women'] = "★";
                    } else if ($v['item_sex_limit'] == 3 || $v['item_marry_limit'] == 2) {
                        $for_list[$k]['y_women'] = "★";
                    } else if ($v['item_sex_limit'] == 2 || $v['item_marry_limit'] == 2) {
                        $for_list[$k]['y_women'] = "★";
                    } else if ($v['item_sex_limit'] == 2 || $v['item_marry_limit'] == 3) {
                        $for_list[$k]['y_women'] = "★";
                    } else {
                        $for_list[$k]['y_women'] = "";
                    }
                    $list->offsetSet($k, $for_list[$k]);
                }
            }
            $this->assign('title', '加项详情');
            $this->assign('list', $list);
            $this->assign('parent_code', input('request.parent_code'));
            $this->assign('count', $count);
            return $this->fetch();
        }
    }

    public function check_store($p_code = "")
    {
        if (empty($p_code)) {
            //跳回列表页
            return $this->redirect('/project/ProjectPackage/index');
        } else {
            $whereSql = " `product_code` = '$p_code'";
            $subQuery = Db::table('db_erp_new.prj_product_store_relation')
                ->field('store_code')
                ->where($whereSql)
                ->buildSql();
            $list = Db::table('db_erp_new.pro_store')
                ->alias('ps')
                ->field('ps.store_id,ps.store_name,ps.store_address,ps.store_off_day,ps.store_blood_time,r.area_full_name,su.supplier_full_name')
                ->leftJoin('db_erp_new.`et_region` r', 'r.area_code = ps.store_city_code')
                ->leftJoin('db_erp_new.`pro_supplier` su', 'su.supplier_code = ps.store_supplier_code')
                ->where('ps.store_code IN (' . $subQuery . ') AND ps.store_status = 1 AND ps.store_coop_status = 1 ')
                ->order('ps.store_city_code ASC')
                ->limit(10)
                ->paginate(10);
            $count = $list->total();
            $for_list = $list->all();
            if (!empty($for_list)) {
                foreach ($for_list as $k => $v) {
                    $for_list[$k]['store_off_day'] = $this->getStoreOffDay($v['store_off_day']);
                    $list->offsetSet($k, $for_list[$k]);
                }
            }
            $this->assign('title', '支持门店');
            $this->assign('list', $list);
            $this->assign('count', $count);
            return $this->fetch();
        }
    }

    /**
     * 处理门店档期字符
     *
     * @param $dayString
     * @return bool|null|string
     */
    public function getStoreOffDay($dayString)
    {
        // 0 礼拜天
        if ($dayString === '' || $dayString === null) {
            return null;
        }

        $week = array("天", "一", "二", "三", "四", "五", "六");

        //处理休息日
        $string = "";
        $storeOffDay = explode("_", $dayString);

        foreach ($storeOffDay as $val) {
            $string .= "星期" . $week[$val] . "、";
        }

        return substr($string, 0, -3);

    }

}