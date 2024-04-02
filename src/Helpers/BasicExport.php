<?php

namespace Hexin\Library\Helpers;;

interface BasicExport
{
    //写入数据
    public function fwrite($array);
    //批量写入数据
    public function writeDataList($array);
    //保存问件
    public function fclose();
    //获取文件地址(不含域名)
    public function getFilePath();
    //获取完整的文件地址(不含域名)
    public function getFileFullPath();
    //设置列为会计专用保留两位小数
    public function setFinancialNumFormat($cell, $cellWidth = 13);
    //设置列为会计专用整数
    public function setFinancialIntFormat($cell, $cellWidth = 13);
    //导出文件
    public function download();
}