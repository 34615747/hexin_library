<?php

namespace Hexin\Library\Helpers;



class ExportCSVHelper implements BasicExport
{
    public $fieldsArr;
    public $path;
    public $full_path;
    public $filename;
    public $fileHandle;

    public function __construct($path, $filename, array $fieldsArrs)
    {
        if(!StringHelper::isContainsDate($path)){
            $path = $path . '/' . date('Y-m-d');
        }
        $full_path = storage_path().$path;
        if (!is_dir($full_path)) {
            mkdir($full_path, 0777, true);
        }

        $this->path     = $path;
        $this->full_path     = $full_path;
        $this->filename = $filename . '.csv';
        $this->fieldsArr   = $fieldsArrs;
        $this->fileHandle = fopen($full_path . '/'.$filename.'.csv', "w") or die("Unable to open file!");
        fwrite($this->fileHandle,chr(0xEF).chr(0xBB).chr(0xBF));//输出BOM头
        $this->fwrite($fieldsArrs);
    }

    /**
     * 写入文件
     * User: lir 2021/2/26 15:56
     * @param array $array
     */
    public function fwrite($fields)
    {
        if(!$fields){
            return;
        }
        $row = $this->formatData($fields);

        fputcsv($this->fileHandle, $row);
    }

    /**
     * 批量写入数据
     */
    public function writeDataList($list)
    {
        foreach ($list as $fields){
            $this->fwrite($fields);
        }
    }

    /**
     * 关闭文件
     * User: lir 2021/2/26 15:58
     */
    public function fclose()
    {
        if($this->fileHandle){
            fclose($this->fileHandle);
        }
    }

    public function getFilePath()
    {
        return $this->path . '/' . $this->filename;
    }

    public function getFileFullPath()
    {
        return $this->full_path . '/' . $this->filename;
    }

    public function setFinancialNumFormat($cell, $width = 13)
    {
        return true;
    }

    public function setFinancialIntFormat($cell, $width = 13)
    {
        return true;
    }

    public function download()
    {
        throw new \Exception('导出失败:csv文件占不支持直接导出');
    }

    /**
     * 写入文件
     * User: lir 2021/2/26 15:56
     * @param array $array
     */
    public function fputCsv(array $fields)
    {
        $this->fwrite($fields);
    }

    public function formatData($item)
    {
        $i = 0;
        return array_map(function($k) use ($item,$i) {
            if(isset($item[$i])){
                return $item[$i];
            }
            return $item[$k] ?? '';
        }, array_keys($this->fieldsArr));
    }
}