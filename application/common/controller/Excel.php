<?php

namespace app\common\controller;


use think\Request;
use think\Controller;
use think\facade\Config;
use think\facade\Session;
use think\Db;
use think\facade\Env;

$VENDOR_PATH = \Env::get('VENDOR_PATH');
require_once $VENDOR_PATH . '/PHPExcel/PHPExcel/IOFactory.php';
require_once $VENDOR_PATH . '/PHPExcel/PHPExcel/Writer/Excel5.php';
require_once $VENDOR_PATH . '/PHPExcel/PHPExcel/Reader/Excel5.php';
require_once $VENDOR_PATH . '/PHPExcel/PHPExcel/Reader/Excel2007.php';
require_once $VENDOR_PATH . '/PHPExcel/PHPExcel/Style/Alignment.php';
require_once $VENDOR_PATH . '/PHPExcel/PHPExcel/Style/Fill.php';
require_once $VENDOR_PATH . '/PHPExcel/PHPExcel.php';

class Excel extends Controller
{
    /**
     * 读excel
     */
    public function readExcel($excel_name, $tmp_name, $is_to_array = 0)
    {
        $return = [];
        //  excel类型
        $type = $this->getExcelType($excel_name);
        $reader = $this->getReader($type);
        // var_dump($reader);die;
        if (empty($reader)) {
            $return['status'] = 0;
            $return['info'] = '文件类型不正确';
            return $return;
        }

        //读excel文件
        $excel = $reader->load($tmp_name, 'utf-8'); // 载入excel文件
        // 读取第一個工作表
        $sheet = $excel->getSheet(0);
        // 取得总行数
        $rows = $sheet->getHighestRow();
        // 取得最后一列列名
        $column = $sheet->getHighestColumn();
        // var_dump($column);die;
        if ($rows > 5001) {
            return [
                'status' => 0,
                'info' => '数据过多'
            ];
        }

        if ($rows < 2) {
            return [
                'status' => 0,
                'info' => '数据为空'
            ];
        }

        if (!$is_to_array) {
            $data = array();
            for ($rowIndex = 1; $rowIndex <= $rows; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
                for ($colIndex = 'A'; $colIndex <= $column; $colIndex++) {
                    $addr = $colIndex . $rowIndex;
                    $cell = $sheet->getCell($addr)->getValue();
                    if ($cell instanceof PHPExcel_RichText) { //富文本转换字符串
                        $cell = $cell->__toString();
                    }
                    $data[$rowIndex][$colIndex] = $cell;
                }
            }

            return [
                'status' => 1,
                'data' => $data
            ];
        }
        //把Excel数据保存数组中
        $data = array();
        $data = $sheet->rangeToArray('A1:' . $column . $rows, null, true, false);
        return [
            'status' => 1,
            'data' => $data
        ];
    }


    /**
     * 写入数据
     */

    public function writeExcel($data, $header, $filename = '', $type = 'xls', $is_save = 0, $path = '')
    {
        //  文件名
        // $filename = '';
        if (empty($filename)) {
            $filename = time();
        }
        $file = $filename . '.' . $type;
        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();
        // var_dump($val);die;
        foreach ($header as $k => $val) {
            if (is_array($data[$k])) {
                $objPHPExcel = $this->handleSheet($data[$k], $val, $objPHPExcel, $k);
            }
        }

        //名称转换为GB2312
        $file = iconv("utf-8", "gb2312", $file);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        if ($is_save == 0) {
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename=\"$file\"");
            header('Cache-Control: max-age=0');
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output'); //文件通过浏览器下载
            exit;
        } else {

            $path = $path ? $path : 'exception';

            // 文件上传目录
            $save_path = UPLOAD_PATH . '/excel/' . $path . '/' . date("Ymd");

            // 每天一个文件夹
            if (!file_exists($save_path)) {

                mkdir($save_path, 0777, true);
                chmod($save_path, 0777);
            }
            // var_dump($save_path);
            // die;
            $save_name = $save_path . '/' . $filename . date("YmdHis") . '_' . mt_rand(1000, 9999) . '.' . $type;

            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

            $objWriter->save($save_name);


            return str_replace(UPLOAD_PATH, '', $save_name);

        }

    }


    /**
     * 操作sheet
     */

    private function handleSheet($data, $header, $objPHPExcel, $index)
    {
        $length = count($header);

        $headLength = $this->getHeadLength($length);

        $objPHPExcel->getActiveSheet()->getStyle("$headLength")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle("$headLength")->getFill()->getStartColor()->setARGB('#87CEFA');
        $objPHPExcel->getActiveSheet()->getStyle("$headLength")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objProps = $objPHPExcel->getProperties();

        //设置表头
        //$key = ord("A");
        $key = 0;

        foreach ($header as $k => $v) {

            //$colum = chr($key);
            $colum = PHPExcel_Cell::stringFromColumnIndex($key);

            //每列内容居中对齐
            $objPHPExcel->setActiveSheetIndex($index)->getStyle($colum)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

            //设置列宽
            // if( empty($columWidth[$k]) ) {
            $objPHPExcel->setActiveSheetIndex($index)->getColumnDimension($colum)->setAutoSize(true);
            // } else {
            //     $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($colum)->setWidth($columWidth[$k]);
            // }

            //设置列数据类型,默认是文本类型
            if (empty($columType)) {
                $objPHPExcel->setActiveSheetIndex($index)->getStyle($colum)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            } else {
                $objPHPExcel->setActiveSheetIndex($index)->getStyle($colum)->getNumberFormat()->setFormatCode($columType[$k]);
            }

            //向第一行单元格内写入值(标题区域)
            $objPHPExcel->setActiveSheetIndex($index)->setCellValueExplicit($colum . '1', $v,
                PHPExcel_Cell_DataType::TYPE_STRING);

            $key += 1;
        }

        $column = 2;

        $objActSheet = $objPHPExcel->getActiveSheet();

        // $objPHPExcel->getActiveSheet()->setTitle('Simple');;

        foreach ($data as $key => $rows) {
            //行写入(批量数据)
            //$span = ord("A");
            $span = 0;

            foreach ($header as $keyName => $val) {

                // 列写入(批量数据)
                //$j = chr($span);
                $j = PHPExcel_Cell::stringFromColumnIndex($span);

                $objActSheet->setCellValueExplicit($j . $column, arrval($rows, $keyName),
                    PHPExcel_Cell_DataType::TYPE_STRING);

                $span++;
            }

            $column++;
        }

        $objPHPExcel->createSheet();

        return $objPHPExcel;

    }


    public function writeExcelNew($data, $file_name = '', $type = 'xls')
    {

        if (empty($file_name)) {

            $file_name = time();
        }

        $file = $file_name . '.' . $type;


        //创建PHPExcel对象，注意，不能少了\
        $objPHPExcel = new \PHPExcel();

        $objActSheet = $objPHPExcel->getActiveSheet();

        // $objPHPExcel->getActiveSheet()->setTitle('Simple');;
        $column = 1;
        foreach ($data as $key => $rows) {
            //行写入(批量数据)
            //$span = ord("A");
            $span = 0;

            foreach ($rows as $val) {

                // 列写入(批量数据)
                //$j = chr($span);
                $j = PHPExcel_Cell::stringFromColumnIndex($span);

                $objActSheet->setCellValueExplicit($j . $column, $val, PHPExcel_Cell_DataType::TYPE_STRING);

                $span++;
            }

            $column++;
        }

        //名称转换为GB2312
        $file = iconv("utf-8", "gb2312", $file);

        header('Content-Type: application/vnd.ms-excel');

        header("Content-Disposition: attachment;filename=\"$file\"");

        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save('php://output'); //文件通过浏览器下载

        exit;

    }


    /**
     * 获取file类型
     */

    private function getExcelType($excel)
    {

        return substr(strrchr($excel, '.'), 1);
    }


    /**
     * 获取reader
     */

    private function getReader($type)
    {
        switch ($type) {
            case 'xls':
                return \PHPExcel_IOFactory::createReader('Excel5');
                break;
            case 'xlsx':
                return new \PHPExcel_Reader_Excel2007();
                break;
            case 'csv':
                return new \PHPExcel_Reader_CSV();
                break;
            default:
                return false;
        }
    }


    /**
     * 获取表需设置的列
     */

    private function getHeadLength($length)
    {
        $arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];

        return 'A1:' . $arr[$length] . '1';
    }


    /**
     * excel日期转换
     */
    public static function excelDateFormat($date)
    {

        $preg = '/^((\d{4}|\d{2})(\-|\/|\.)\d{1,2}\3\d{1,2})|(\d{4}年\d{1,2}月\d{1,2}日)$/';

        if (intval($date) > 25569) {

            $timestamp = (intval($date) - 25569) * 3600 * 24;

            return date('Y-m-d', $timestamp);

        } elseif (preg_match($preg, $date)) {

            return date('Y-m-d', strtotime($date));
        } else {

            return false;
        }
    }

    function output_excel($fileName = '', $headArr = null, $data = null, $columWidth = null, $columType = null)
    {
        $date = date("Y_m_d", time());
        $fileName .= "{$date}.xls";
        //创建PHPExcel对象，注意，不能少了\
        //导入PHPExcel相关文件
        // require_once LIBRARY_PATH. DIRECTORY_SEPARATOR .'excellib'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'IOFactory.php';
        // require_once LIBRARY_PATH. DIRECTORY_SEPARATOR .'excellib'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Writer'.DIRECTORY_SEPARATOR.'Excel5.php';
        // require_once LIBRARY_PATH. DIRECTORY_SEPARATOR .'excellib'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Style'.DIRECTORY_SEPARATOR.'Alignment.php';
        // require_once LIBRARY_PATH. DIRECTORY_SEPARATOR .'excellib'.DIRECTORY_SEPARATOR.'PHPExcel.php';
        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getActiveSheet()->getStyle('A1:V1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:V1')->getFill()->getStartColor()->setARGB('#87CEFA');
        $objPHPExcel->getActiveSheet()->getStyle('A1:V1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objProps = $objPHPExcel->getProperties();
        //设置表头
        //$key = ord("A");
        $key = 0;
        foreach ($headArr as $k => $v) {
            //$colum = chr($key);
            $colum = \PHPExcel_Cell::stringFromColumnIndex($key);
            //每列内容居中对齐
            $objPHPExcel->setActiveSheetIndex(0)->getStyle($colum)->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置列宽
            if (empty($columWidth[$k])) {
                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($colum)->setAutoSize(true);
            } else {
                $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($colum)->setWidth($columWidth[$k]);
            }
            //设置列数据类型,默认是文本类型
            if (empty($columType)) {
                $objPHPExcel->setActiveSheetIndex(0)->getStyle($colum)->getNumberFormat()->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_TEXT);
            } else {
                $objPHPExcel->setActiveSheetIndex(0)->getStyle($colum)->getNumberFormat()->setFormatCode($columType[$k]);
            }
            //向第一行单元格内写入值(标题区域)
            $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit($colum . '1', $v, \PHPExcel_Cell_DataType::TYPE_STRING);
            $key += 1;
        }
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach ($data as $key => $rows) {
            //行写入(批量数据)
            //$span = ord("A");
            $span = 0;
            foreach ($rows as $keyName => $value) {
                // 列写入(批量数据)
                //$j = chr($span);
                $j = \PHPExcel_Cell::stringFromColumnIndex($span);
                $objActSheet->setCellValueExplicit($j . $column, $value, \PHPExcel_Cell_DataType::TYPE_STRING);
                $span++;
            }
            $column++;
        }
        //名称转换为GB2312
        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        //$objPHPExcel->getActiveSheet()->setTitle('test');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }
}