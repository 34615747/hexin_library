<?php

namespace Hexin\Library\Helpers;

class ExportFactory
{
    protected $exportClass = [];
    public function __construct($path, $filename, array $headers, $driver = 'csv')
    {
        switch ($driver){
            case 'xls':
            case 'xlsx':
                $this->exportClass = new ExportXLSHelper($headers,$filename,$path);
                break;
            default:
                $this->exportClass = new ExportCSVHelper($path, $filename, $headers);
                break;
        }
    }

    /**
     * Notes:写入数据
     * @param $item ['key' => value]
     * Author: peng.chen
     * Date:2024/2/19
     * Time:10:51
     */
    public function fwrite($item)
    {
        return $this->exportClass->fwrite($item);
    }

    /**
     * 批量写入
     */
    public function writeDataList($item)
    {
        return $this->exportClass->writeDataList($item);
    }

    /**
     * Notes:保存文件
     * Author: peng.chen
     * Date:2024/2/19
     * Time:10:50
     */
    public function fclose()
    {
        return $this->exportClass->fclose();
    }

    /**
     * Notes:获取文件地址(不带域名)
     * Author: peng.chen
     * Date:2024/2/19
     * Time:10:50
     */
    public function getFilePath()
    {
        return $this->exportClass->getFilePath();
    }

    /**
     * Notes:设置列为会计模式
     * @param $cell 'A:A'
     * Author: peng.chen
     * Date:2024/2/19
     * Time:11:23
     */
    public function setCellFinancialStyle($cell, $styleType)
    {
        switch ($styleType){
            case 'float' :
                $this->exportClass->setFinancialNumFormat($cell);
                break;
            case 'int':
                $this->exportClass->setFinancialIntFormat($cell);
        }

        return true;
    }

    /**
     * Notes:导出
     * Author: peng.chen
     * Date:2024/2/19
     * Time:11:56
     */
    public function download()
    {
        return $this->exportClass->download();
    }
}