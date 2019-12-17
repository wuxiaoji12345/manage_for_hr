<?php
namespace app\reservation_query\model;

use think\Model;

class SerAppend extends Model
{

      /**
     * 获取项目员工预约信息
     *
     * @param    string     $projectCode       项目编码
     * @param    string     $whereSql          查询条件
     * @return   array      $result            员工预约信息
     */
      public function getEmployeeAppendList($projectCode,$whereSql,$hr_id=0,$page=10,$son_str){

        $result = array();

        if(empty($projectCode)){
            return $result;
        }

        $where = '';
        if (isset($son_str) && !empty($son_str)) {
             $where = " AND u.u_son_code in (".$son_str.") ";
        }
        $join_r = '';

        $whereSql =  " 1 ".$whereSql." AND a.append_type = 1 AND a.append_project_code = '$projectCode' AND o.order_action_key = 'append' AND o.order_type_code = 1 AND u.u_type < 4 ".$where;
        if($hr_id){
            $result = db('db_erp_new.ser_append')
            ->alias('a')
            ->join('db_erp_new.prj_project_user u','u.id = a.append_user_code','left')
            ->join('db_erp_new.prj_hr_sonenter_relation hsr','u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id='.$hr_id,'left')
            ->join('db_erp_new.pro_store s','s.store_code = a.append_store_code','left')
            ->join('db_erp_new.pro_supplier su','su.supplier_code = s.store_supplier_code','left')
            ->join('db_erp_new.prj_product p','p.p_code = a.append_product_code','left')
            ->join('db_erp_new.ser_order o','o.order_code = a.append_order_code','left')
            ->join('db_erp_new.ser_report rport','rport.report_order_code = a.append_order_code','left')
            ->join('db_erp_new.prj_project j','j.project_code = a.append_project_code','left')
            ->join('db_erp_new.cus_enterprise e','j.enterprise_code = e.e_code','left')
            ->field(['a.*','s.store_name','p.p_name','o.order_status','o.order_total_price','o.order_pay_price','j.project_name','e.e_code','rport.report_upload_time','e.e_name','u.u_bind_addon','u.u_job_code','u.u_type'])
            ->where($whereSql)
            ->order('a.id desc')
                        // ->buildSql();
                        // dump($result);
                        // exit;
                        // ->select();
            ->paginate($page,false,['query' => request()->param()]);
        } else {
            $result = db('db_erp_new.ser_append')
            ->alias('a')
            ->join('db_erp_new.prj_project_user u','u.id = a.append_user_code','left')
            ->join('db_erp_new.pro_store s','s.store_code = a.append_store_code','left')
            ->join('db_erp_new.pro_supplier su','su.supplier_code = s.store_supplier_code','left')
            ->join('db_erp_new.prj_product p','p.p_code = a.append_product_code','left')
            ->join('db_erp_new.ser_order o','o.order_code = a.append_order_code','left')
            ->join('db_erp_new.ser_report rport','rport.report_order_code = a.append_order_code','left')
            ->join('db_erp_new.prj_project j','j.project_code = a.append_project_code','left')
            ->join('db_erp_new.cus_enterprise e','j.enterprise_code = e.e_code','left')
            ->field(['a.*','s.store_name','p.p_name','o.order_status','o.order_total_price','o.order_pay_price','j.project_name','e.e_code','rport.report_upload_time','e.e_name','u.u_bind_addon','u.u_job_code','u.u_type'])
            ->where($whereSql)
            ->order('a.id desc')
                        // ->buildSql();
                        // dump($result);
                        // exit;
                        // ->select();
            ->paginate($page,false,['query' => request()->param()]);
        }

        return $result;
      }

     /**
     * 根据已绑定加项包编码获取加项包信息
     *
     * @param     string   $addonCodes   加项包编码  eg:$addonCodes= "jxb004-jxb003" ;
     * @return    array    $list         包含加项包信息的数组
     */
     public function getBindAddonInfo($addonCodes = '') {

        if (empty($addonCodes)) {
            return NULL;
        }

        $addonCodes = explode("-", $addonCodes);

        //数组转成带单引号的字符串
        $addonCodes = join(',', array_map(function($v) {
            return "'$v'";
        }, $addonCodes));

        $list = array();
        $where = "p_code IN (" . $addonCodes .") " ;

        $list = db("db_erp_new.prj_addon_package")
        ->where($where)
        ->field("GROUP_CONCAT(p_name) AS p_name_str")
        ->order("id desc")
        ->find();
        return $list;
     }


        /**
     * 项目预约情况统计
     *
     * @param    string     $projectCode    项目编码$projectCode
     * @param    string     $countType      统计类型（1=>员工预约；2=>家属预约）
     * @return   array      $result         统计结果
     */
        public function getStatisticsInfo($projectCode,$countType,$hr_id=0,$u_son_code = '',$son_str){
            $now_date = date("Y-m-d",time());
            $result = array();
            if(empty($projectCode) || empty($countType)){
                return $result;
            }

            $where = '';
            if (isset($son_str) && !empty($son_str)) {
                 $where = " AND u.u_son_code in (".$son_str.") ";
            }
            $join = '';
            $join_r = '';
            if($hr_id){
                $join = " left JOIN db_erp_new.prj_project_user u ON a.append_user_code=u.id AND u.u_project_code = '$projectCode' left JOIN db_erp_new.prj_hr_sonenter_relation hsr ON u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= ".$hr_id;
                $join_r = " left JOIN db_erp_new.prj_hr_sonenter_relation hsr ON u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id= ".$hr_id;
            }

            if ($u_son_code) {
                if ($join) {
                    $join .= ' and u.u_son_code="'.$u_son_code.'"';
                }

                $join_r .= ' and u.u_son_code="'.$u_son_code.'"';
            }

            $searchSql1 = "SELECT  ".
            "   count( a.id ) AS num   ".
            "FROM  ".
            "   db_erp_new.ser_append AS a   ". $join .
            " WHERE  ".
            "   1   ".
            "   AND a.`append_status` IN (1,2)  ".
            "   AND a.`append_type` = $countType   ".
            "   AND a.`append_project_code` = '$projectCode'   ".
            "   AND a.`create_time` >= '$now_date"." 00:00:00'   ".
            "   AND a.`create_time` <= '$now_date"." 23:59:59'   ".$where.
            "ORDER BY  ".
            "   a.id DESC";

            $searchSql2 = "SELECT ".
            "   count( a.id ) AS num  ".
            "FROM ".
            "   db_erp_new.ser_append AS a  ". $join .
            " WHERE ".
            "   1  ".
            "   AND a.`append_status` = 8  ".
            "   AND a.`append_type` = $countType  ".
            "   AND a.`append_project_code` = '$projectCode'  ".
            "   AND a.`update_time` >= '$now_date"." 00:00:00'  ".
            "   AND a.`update_time` <= '$now_date"." 23:59:59'  ".$where.
            "ORDER BY ".
            "   a.id DESC";

            $searchSql3 = "SELECT ".
            "   count( a.id ) AS num  ".
            "FROM ".
            "   db_erp_new.ser_append AS a  ". $join .
            " WHERE ".
            "   1  ".
            "   AND a.`append_status` IN ( 1, 2 )  ".
            "   AND a.`append_type` = $countType  ".
            "   AND a.`append_project_code` = '$projectCode'  ".
            "   AND a.`append_schedule` = '$now_date'  ".$where.
            "ORDER BY ".
            "   a.id DESC";

            if($countType==1){
                $searchSql4 = "SELECT ".
                "   count( u.id ) AS num  ".
                "FROM ".
                "   db_erp_new.prj_project_user AS u  ". $join_r .
                " WHERE ".
                "   1  ".
                // "    AND u.`u_status` in (1,2)  ".
                "   AND u.`u_type` < 4  ".
                "   AND u.`u_project_code` = '$projectCode'  ".$where.
                "ORDER BY ".
                "   u.id DESC";

            }else{
                $searchSql4 = "SELECT ".
                "   count( u.id ) AS num  ".
                "FROM ".
                "   db_erp_new.prj_project_user AS u  ". $join_r .
                " WHERE ".
                "   1  ".
                // "    AND u.`u_status` in (1,2) ".
                "   AND u.`u_type` = 4  ".
                "   AND u.`u_project_code` = '$projectCode'  ".$where.
                "ORDER BY ".
                "   u.id DESC";

            }

            $searchSql5 = "SELECT  ".
            "    count( a.id ) AS num   ".
            "FROM  ".
            "   db_erp_new.ser_append AS a   ". $join .
            " WHERE  ".
            "   1   ".
            "   AND a.`append_status` IN (1,2)  ".
            "   AND a.`append_type` = $countType   ".
            "   AND a.`append_project_code` = '$projectCode'   ".$where.
            "ORDER BY  ".
            "   a.id DESC";


        //已到检人数
            $searchSql6 = "SELECT ".
            "   count( a.id ) AS num  ".
            "FROM ".
            "   db_erp_new.ser_append AS a  ".$join .
            " WHERE ".
            "   1  ".
            "   AND a.`append_arrive_status` = 2  ".
            "   AND a.`append_status` NOT IN ( 9 )  ".
            "   AND a.`append_type` = $countType  ".
            "   AND a.`append_project_code` = '$projectCode'  ".$where.
            "ORDER BY ".
            "   a.id DESC";


            $searchSql7 = "SELECT ".
            "   count( a.id ) AS num  ".
            "FROM ".
            "   db_erp_new.ser_append AS a  ".$join .
            " WHERE ".
            "   1  ".
            "   AND a.`append_status` IN ( 1, 2 )  ".
            "   AND a.`append_arrive_status` = 1  ".
            "   AND a.`append_type` = $countType  ".
            "   AND a.`append_project_code` = '$projectCode'  ".$where.
            "ORDER BY ".
            "   a.id DESC";

            $num1 = self::query($searchSql1);

            $num2 = self::query($searchSql2);
            $num3 = self::query($searchSql3);
            $num4 = self::query($searchSql4);
// $result=$searchSql4;
            $num5 = self::query($searchSql5);
            $num6 = self::query($searchSql6);
            $num7 = self::query($searchSql7);

            $result['appendToday'] = $num1[0]['num'];
            $result['cancelToday'] = $num2[0]['num'];
            $result['checkToday'] = $num3[0]['num'];
            $result['appendTotal'] = $num4[0]['num'];
            $result['append'] = $num5[0]['num'];
            $result['haveCheck'] = $num6[0]['num'];
            $result['waitCheck'] = $num7[0]['num'];

            return $result;
        }


      /**
     * 获取员工家属预约信息
     *
     * @param    string     $projectCode       项目编码
     * @param    string     $whereSql          查询条件
     * @return   array      $result            员工预约信息
     */
      public function getFamilyAppendList($projectCode,$whereSql,$hr_id=0,$page=10,$son_str){

        $result = array();

        if(empty($projectCode)){
            return $result;
        }
        
        $where = '';
        if (isset($son_str) && !empty($son_str)) {
             $where = " AND u1.u_son_code in (".$son_str.") ";
        }
        $join_r = '';

        $whereSql =  " 1 ".$whereSql." AND a.append_type = 2 AND a.append_project_code = '$projectCode' AND o.order_action_key = 'append' AND o.order_type_code in (2,3) AND u.u_type <= 4 ".$where;
        if($hr_id){
            $result = db('db_erp_new.ser_append')
            ->alias('a')
            ->join("db_erp_new.prj_project_user u","u.id = a.append_user_code AND u.u_project_code = '$projectCode'","left")
            ->join('db_erp_new.prj_hr_sonenter_relation hsr','u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id='.$hr_id,'left')
            ->join('db_erp_new.pro_store s','s.store_code = a.append_store_code','left')
            ->join('db_erp_new.pro_supplier su','su.supplier_code = s.store_supplier_code','left')
            ->join('db_erp_new.prj_product p','p.p_code = a.append_product_code','left')
            ->join('db_erp_new.ser_order o','o.order_code = a.append_order_code','left')
            ->join('db_erp_new.ser_report rport','rport.report_order_code = a.append_order_code','left')
            ->join('db_erp_new.prj_project j','j.project_code = a.append_project_code','left')
            ->join('db_erp_new.cus_enterprise e','j.enterprise_code = e.e_code','left')
            ->join('db_erp_new.prj_project_user u1', 'u1.id = u.u_parent_id','left')
            ->field(['a.*','s.store_name','p.p_name','o.order_status','o.order_total_price','o.order_pay_price','o.order_type_code','e.e_code','rport.report_upload_time','e.e_name','u.u_job_code'])
            ->where($whereSql)
            ->order('a.id desc')
            ->paginate($page,false,['query' => request()->param()]);



        } else {
            $result = db('db_erp_new.ser_append')
            ->alias('a')
            // ->join("db_erp_new.prj_project_user u","u.id = a.append_user_code AND u.u_project_code = '$projectCode'","left")
        //     ->join('db_erp_new.prj_hr_sonenter_relation hsr','u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id='.$hr_id,'left')
            ->join('db_erp_new.pro_store s','s.store_code = a.append_store_code','left')
            ->join('db_erp_new.pro_supplier su','su.supplier_code = s.store_supplier_code','left')
            ->join('db_erp_new.prj_product p','p.p_code = a.append_product_code','left')
            ->join('db_erp_new.ser_order o','o.order_code = a.append_order_code','left')
            ->join('db_erp_new.ser_report rport','rport.report_order_code = a.append_order_code','left')
            ->join('db_erp_new.prj_project j','j.project_code = a.append_project_code','left')
            ->join('db_erp_new.cus_enterprise e','j.enterprise_code = e.e_code','left')
            ->join('db_erp_new.prj_project_user u1', 'u1.id = u.u_parent_id','left')
            ->field(['a.*','s.store_name','p.p_name','o.order_status','o.order_total_price','o.order_pay_price','o.order_type_code','e.e_code','rport.report_upload_time','e.e_name'])
            ->where($whereSql)
            ->order('a.id desc')
            ->paginate($page,false,['query' => request()->param()]);
        }



        return $result;
      }


      /**
     * 获取员工信息
     *
     * @param     string       $projectCode          项目编码
     * @param     int          $append_user_code     用户编号
     * @return    array        $result               员工信息
     */
      public  function getStaffInfo($projectCode,$append_user_code){
        $result =array();

        if(empty($projectCode) || empty($append_user_code)){
            return $result;
        }

        $wheresql = " 1 AND `u_project_code` = '$projectCode' AND `id` = '$append_user_code'";
        $result = db('db_erp_new.prj_project_user')
        ->where($wheresql)
        ->field('u_name')
        ->find();

        return $result;
      }

      //输出所有满足条件的员工预约信息
      public function getEmployeeAppendListAll($projectCode,$whereSql,$hr_id=0,$son_str){

        $result = array();

        if(empty($projectCode)){
            return $result;
        }

        $where = '';
        if (isset($son_str) && !empty($son_str)) {
             $where = " AND u.u_son_code in (".$son_str.") ";
        }

        $join_r = '';

        $whereSql =  " 1 ".$whereSql." AND a.append_type = 1 AND a.append_project_code = '$projectCode' AND o.order_action_key = 'append' AND o.order_type_code = 1 AND u.u_type < 4 ".$where ;
        if ($hr_id) {
            $result = db('db_erp_new.ser_append')
            ->alias('a')
            ->join('db_erp_new.prj_project_user u','u.id = a.append_user_code','left')
            ->join('db_erp_new.prj_hr_sonenter_relation hsr','u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id='.$hr_id,'left')
            ->join('db_erp_new.pro_store s','s.store_code = a.append_store_code','left')
            ->join('db_erp_new.pro_supplier su','su.supplier_code = s.store_supplier_code','left')
            ->join('db_erp_new.prj_product p','p.p_code = a.append_product_code','left')
            ->join('db_erp_new.ser_order o','o.order_code = a.append_order_code','left')
            ->join('db_erp_new.ser_report rport','rport.report_order_code = a.append_order_code','left')
            ->join('db_erp_new.prj_project j','j.project_code = a.append_project_code','left')
            ->join('db_erp_new.cus_enterprise e','j.enterprise_code = e.e_code','left')
            ->field(['a.*','s.store_name','p.p_name','o.order_status','o.order_total_price','o.order_pay_price','j.project_name','e.e_code','rport.report_upload_time','e.e_name','u.u_bind_addon','u.u_job_code','u.u_type'])
            ->where($whereSql)
            ->order('a.id desc')
            ->select();
        } else {
            $result = db('db_erp_new.ser_append')
            ->alias('a')
            ->join('db_erp_new.prj_project_user u','u.id = a.append_user_code','left')
            // ->join('db_erp_new.prj_hr_sonenter_relation hsr','u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id='.$hr_id,'left')
            ->join('db_erp_new.pro_store s','s.store_code = a.append_store_code','left')
            ->join('db_erp_new.pro_supplier su','su.supplier_code = s.store_supplier_code','left')
            ->join('db_erp_new.prj_product p','p.p_code = a.append_product_code','left')
            ->join('db_erp_new.ser_order o','o.order_code = a.append_order_code','left')
            ->join('db_erp_new.ser_report rport','rport.report_order_code = a.append_order_code','left')
            ->join('db_erp_new.prj_project j','j.project_code = a.append_project_code','left')
            ->join('db_erp_new.cus_enterprise e','j.enterprise_code = e.e_code','left')
            ->field(['a.*','s.store_name','p.p_name','o.order_status','o.order_total_price','o.order_pay_price','j.project_name','e.e_code','rport.report_upload_time','e.e_name','u.u_bind_addon','u.u_job_code','u.u_type'])
            ->where($whereSql)
            ->order('a.id desc')
            ->select();

        }

        return $result;
      }
      
      //输出所有满足条件的家属预约信息用于导出信息
      public function getFamilyAppendListAll($projectCode,$whereSql,$hr_id=0,$son_str){

        $result = array();

        if(empty($projectCode)){
            return $result;
        }

        $where = '';
        if (isset($son_str) && !empty($son_str)) {
             $where = " AND u1.u_son_code in (".$son_str.") ";
        }

        $join_r = '';

        $whereSql =  " 1 ".$whereSql." AND (a.append_type = 2 AND u.u_type <= 4) AND a.append_project_code = '$projectCode' AND o.order_action_key = 'append' AND o.order_type_code in (2,3) ".$where;
        if($hr_id){
            $result = db('db_erp_new.ser_append')
            ->alias('a')
            ->join("db_erp_new.prj_project_user u","u.id = a.append_user_code AND u.u_project_code = '$projectCode'","left")
            ->join('db_erp_new.prj_hr_sonenter_relation hsr','u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id='.$hr_id,'left')         
            ->join('db_erp_new.pro_store s','s.store_code = a.append_store_code','left')
            ->join('db_erp_new.pro_supplier su','su.supplier_code = s.store_supplier_code','left')
            ->join('db_erp_new.prj_product p','p.p_code = a.append_product_code','left')
            ->join('db_erp_new.ser_order o','o.order_code = a.append_order_code','left')
            ->join('db_erp_new.ser_report rport','rport.report_order_code = a.append_order_code','left')
            ->join('db_erp_new.prj_project j','j.project_code = a.append_project_code','left')
            ->join('db_erp_new.cus_enterprise e','j.enterprise_code = e.e_code','left')
            ->join('db_erp_new.prj_project_user u1', 'u1.id = u.u_parent_id','left')
            ->field(['a.*','s.store_name','p.p_name','o.order_status','o.order_total_price','o.order_pay_price','o.order_type_code','e.e_code','rport.report_upload_time','e.e_name','u.u_job_code'])
            ->where($whereSql)
            ->order('a.id desc')
            ->select();
          } else {
            $result = db('db_erp_new.ser_append')
            ->alias('a')
            ->join("db_erp_new.prj_project_user u","u.id = a.append_user_code AND u.u_project_code = '$projectCode'","left")
        //     ->join('db_erp_new.prj_hr_sonenter_relation hsr','u.u_son_code=hsr.s_enter_code AND hsr.status=1 AND hsr.hr_id='.$hr_id,'left')          
            ->join('db_erp_new.pro_store s','s.store_code = a.append_store_code','left')
            ->join('db_erp_new.pro_supplier su','su.supplier_code = s.store_supplier_code','left')
            ->join('db_erp_new.prj_product p','p.p_code = a.append_product_code','left')
            ->join('db_erp_new.ser_order o','o.order_code = a.append_order_code','left')
            ->join('db_erp_new.ser_report rport','rport.report_order_code = a.append_order_code','left')
            ->join('db_erp_new.prj_project j','j.project_code = a.append_project_code','left')
            ->join('db_erp_new.cus_enterprise e','j.enterprise_code = e.e_code','left')
            ->join('db_erp_new.prj_project_user u1', 'u1.id = u.u_parent_id','left')
            ->field(['a.*','s.store_name','p.p_name','o.order_status','o.order_total_price','o.order_pay_price','o.order_type_code','e.e_code','rport.report_upload_time','e.e_name','u.u_job_code'])
            ->where($whereSql)
            ->order('a.id desc')
            ->select();
          }
        return $result;
      }
      
      //获取hr所拥有权限下的所有子公司
      public function getSonEnterpriInfo($app_id){
       return $son_list = db('db_erp_new.prj_hr_sonenter_relation')
            ->alias('t1')
            ->field('t2.s_code value ,t2.s_name name ')
            ->leftJoin('db_erp_new.cus_son_enterprise t2', 't2.s_code = t1.s_enter_code')
            ->where('t1.status=1 and t1.hr_id = '.$app_id)
            ->select();
      }

  }