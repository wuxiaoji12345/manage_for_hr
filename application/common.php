<?php


use think\Db;

function msg_return($msg = "操作成功！", $code = 1, $data = [], $redirect = 'parent', $alert = '', $close = false, $url = '')
{
    $ret = ["code" => $code, "msg" => $msg, "data" => $data];
    $extend['opt'] = [
        'alert' => $alert,
        'close' => $close,
        'redirect' => $redirect,
        'url' => $url,
    ];
    $ret = array_merge($ret, $extend);
    return Response::create($ret, 'json');

}

function diffTwoDate($start_date = '', $end_date = '')
{
    $timestamp1 = strtotime($start_date);
    $timestamp2 = strtotime($end_date);
    if ($timestamp1 < $timestamp2) {
        $tmp = $timestamp2;
        $timestamp2 = $timestamp1;
        $timestamp1 = $tmp;
    }
    $diff_days = ($timestamp1 - $timestamp2) / 86400;
    return $diff_days;
}

/**
 * 根据数组返回指定key的值 无返回默认值
 *
 */
function arrval($arr = [], $key = '', $val = '', $type = 'str')
{
    if (empty($arr)) return $val;

    if (empty($key) && $key !== 0) return $val;

    if (isset($arr[$key])) {

        if ($type == 'int') {

            $value = intval($arr[$key]);
        } else {

            $value = trim($arr[$key]);
        }

        if (empty($value) && $value !== '0') {
            return $val;
        }

        return $value;
    }

    return $val;

}


/**
 * 根据字典分类名称 获取 对应信息
 * @param string $type
 * @return array
 */
function get_dictionaries_list($type = '', $is_all = 1, $select_value = 0)
{
    if (empty($type)) {
        return [];
    }

    $where = ' AND `status` = 1 ';
    if ($is_all !== 1) {
        $where = "";
    }
    //看查询全部还是只查询一个
    if (!empty($select_value)) {
        //需要查询一个指定的
        $dictionaryData = Db::table('db_erp_new.et_dictionary')
            ->where('1=1 ' . $where . ' AND class_type_code ="' . $type . '" and field_value=' . $select_value)
            ->order('id')
            ->value('field_name');
        return !empty($dictionaryData) ? $dictionaryData : '';
    } else {
        //需要查询多个
        $dictionaryList = Db::table('db_erp_new.et_dictionary')
            ->field("field_name as name,field_value as id")
            ->where('1=1 ' . $where . ' AND class_type_code ="' . $type . '"')
            ->order('id')
            ->select();
        return $dictionaryList;
    }

}