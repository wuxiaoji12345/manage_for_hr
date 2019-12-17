<?php
namespace app\process\controller;

use app\common\controller\Base;
use think\Cache;
use think\Db;
use app\common\controller\Captcha;
use app\common\controller\Curl;

class CompanyReport extends Base
{
    public function index()
    {
        $list=[];
        $count=0;
        if (request()->isPost()||request()->isGet()){
            $u_job_code = input('request.u_job_code');
            $u_identify_code = input('request.u_identify_code');
            if(!empty($u_job_code)||!empty($u_identify_code)) {
                $order_project_code = $this->project_code;
                $searchData = [];
                $whereSql = "1=1 ";
                if (!empty($u_job_code)) {
                    $searchData['u_job_code']=$u_job_code;
                    $whereSql .= ' AND u.u_job_code like \'%' . $u_job_code . '%\' ';
                }
                if (!empty($u_identify_code)) {
                    $searchData['u_identify_code']=$u_identify_code;
                    $whereSql .= ' AND u.u_identify_code like \'%' . $u_identify_code . '%\' ';
                }
                $whereSql.=" AND a.append_type = 1 AND o.order_project_code ='".$order_project_code."'";
                $son_str=$this->son_str;
                if(!empty($son_str)){
                    $whereSql.=" AND u.u_son_code in (".$son_str.")";
                }
                $list = Db::table('db_erp_new.ser_report')
                    ->alias('r')
                    ->field('o.order_project_code,a.append_name, r.*,a.append_schedule,a.append_status,a.append_ext,p.p_name,p.p_name_en,s.store_name,s.store_name_en,o.order_type_code,j.project_name,u.u_job_code,u.u_name')
                    ->leftJoin('db_erp_new.ser_order o', 'r.report_order_code = o.order_code')
                    ->leftJoin('db_erp_new.ser_append a', 'r.report_append_code = a.append_code')
                    ->leftJoin('db_erp_new.prj_project_user u', 'r.report_user_id = u.id')
                    ->leftJoin('db_erp_new.prj_product p', 'a.append_product_code = p.p_code')
                    ->leftJoin('db_erp_new.pro_store s', 'a.append_store_code = s.store_code')
                    ->leftJoin('db_erp_new.prj_project j', 'o.order_project_code = j.project_code')
                    ->where($whereSql)
                    ->limit(10)
                    ->paginate(10);
                $for_list=$list->all();
                if (!empty($for_list)) {
                    foreach ($for_list as $k => $v) {
                        //报告信息状态更改
                        $for_list[$k]['status']=$v['status']==1?'未上传':'已上传';
                        $list->offsetSet($k,$for_list[$k]);
                    }
                }
                $count = $list->total();
                $this->assign('searchData', $searchData);
                $this->assign('list', $list);
                $this->assign('count', $count);
            }
        }
        $this->assign('title', '体检报告查询');
        return $this->fetch();
    }

    public function login_captcha_code() {

        $project_code = $this->project_code;
        $report_file_path = input('get.report_file_path');

        if (!empty($report_file_path)) {
            $key_redis = md5($report_file_path);
        } elseif (!empty($project_code)){
            $key_redis = md5($project_code);
        }
        $width = 75; //验证码宽度
        $height = 32; //验证码高度
        $length = 4; //验证码长度
        $session_key = 'pdf_login_captcha_code'; //session中的标识 $_SESSION[$session_key]
        $captcha_str = rand(1111,9999); //参与生成验证码的字符串
        $cache = new Cache($this->app);
        $redis = $cache->store('redis');
        $redis->set($key_redis, $captcha_str,300);
        $image = new Captcha();
        $image->config($width, $height, $length, $session_key, "$captcha_str");
        $image->create();
    }

    /**
     * 预览阿里云的体检报告
     * */
    public function previewReport()
    {
        $project_code=$this->project_code;
        $report_file_path = input('get.report_file_path');
        $yanzhengma = input('get.yanzhengma');
        if (empty($report_file_path)) {
            return msg_return("文件路径为空",-1);
        }
        if (empty($yanzhengma)) {
            return msg_return("验证码不能为空",-1);
        }
        if (!empty($report_file_path)) {
            $key_redis = md5($report_file_path);
        } elseif (!empty($project_code)) {
            $key_redis = md5($project_code);
        }
        $cache = new Cache($this->app);
        $redis = $cache->store('redis');
        $yzm =$redis->get($key_redis);
        if ($yanzhengma != $yzm) {
            return msg_return("验证码错误",-1);
        }
        $redis->dec($key_redis);
        //阿里云OSS域名
        $url=config('app.ALIYUN_OSS_URL');
        $curl = new Curl();
        //是否开启调试模式(即为测试环境) 如果 API_DEBUG = true 则为测试环境。
        $api_debug=config('app.app_debug');
        //测试接口函数名称
        $API_FUNCTION_VERSION_TEST=config('app.API_FUNCTION_VERSION_TEST');
        //接口生产环境默认密钥
        $API_SECRET_PROD=config('app.API_SECRET_PROD');
        $secret = $api_debug ? $API_FUNCTION_VERSION_TEST : $API_SECRET_PROD;
        $data['file_type'] = 'report';
        if (strpos($report_file_path, 'report') !== false) {
            $data['file_name'] = str_replace('/report', 'newerp', $report_file_path);
        } else {
            $data['file_name'] = $report_file_path;
        }
        $data['time_stamp'] = time();
        $data['token'] = md5($secret . $data['time_stamp'] . $data['file_type']);

        $url .= '?file_type=' . $data['file_type'] . '&file_name=' . $data['file_name'] . '&time_stamp=' . $data['time_stamp'] . '&token=' . $data['token'];
        $ret = $curl->get($url, $data);
        // erp正式域名
        $erp_domain=config('app.ERP_DOMAIN');
        if (empty($ret->response)) {
            $url = $erp_domain . '/upload/' . $report_file_path;
            if (file_exists($url)) {
                return msg_return("获取体检报告成功",1,$url);
            } else {
                return msg_return("获取体检报告失败",0,$ret->response);
            }
        }
        $getResult = json_decode($ret->response, true);
        if (!is_array($getResult)) {
            $url = $erp_domain . '/upload/' . $report_file_path;
            if (file_exists($url)) {
                return msg_return("获取体检报告成功",1,$url);
            } else {
                return msg_return("获取体检报告失败",0,$url);
            }
        }

        //调取阿里云的体检报告
        if ($getResult['status'] == 1 && $getResult['errorCode'] == 0 && !empty($getResult['data'])) {
            return msg_return("获取体检报告成功",1,$getResult['data']);
        } else {
            //从newerp获取数据  http://newerp.etong-online.com/upload/report/20181205/20181205150108_217075332.pdf
            $url = $erp_domain . '/upload/' . $report_file_path;
            if (file_exists($url)) {
                return msg_return("获取体检报告成功",1,$url);
            } else {
                return msg_return("获取体检报告失败",0,$url);
            }
        }

    }

}
