<?php
namespace app\admin\controller;
use think\Db;
use think\Cache;


use PHPExcel_IOFactory;
use PHPExcel;



class Adaochu extends Base{
	
   
    public function index(){
       $table='xztxl';
        $file='xztxl';
        $data= Db::name('xztxl')->order('listorder asc,dep asc')->select();
        error_reporting(E_ALL);
        date_default_timezone_set('Asia/chongqing');
        $objPHPExcel = new \PHPExcel();
        /*设置excel的属性*/
        $objPHPExcel->getProperties()->setCreator("aaa")//创建人
        ->setLastModifiedBy("aaa")//最后修改人
        ->setKeywords("excel")//关键字
        ->setCategory("result file");//种类
        //第一行数据
        $objPHPExcel->setActiveSheetIndex(0);
        $active = $objPHPExcel->getActiveSheet();
        $field_titles=array(
        'dep'=>'部门',
        'room'=>'房间号',
        'officep1'=>'外线号码',
        'officep2'=>'短号',
        'pname'=>'人员名称',
        'mobile'=>'手机号',
        'mobile_s'=>'手机短号',
        'listorder'=>'排列序号',
        );
        $i=0;
        foreach($field_titles as $key=>$name){
            $ck = num2alpha($i++) . '1';
            $active->setCellValue($ck, $name);
        }
        //填充数据
        foreach($data as $k => $v){
            $k=$k+1;
            $num=$k+1;//数据从第二行开始录入
            $objPHPExcel->setActiveSheetIndex(0);
            $i=0;
            foreach($field_titles as $key=>$name){
                $ck = num2alpha($i++) . $num;
                $active->setCellValue($ck, $v[$key]);
            }
        }
        $objPHPExcel->getActiveSheet()->setTitle($table);
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$file.'.xls"');
        header('Cache-Control: max-age=0');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');

    }

	
    
}