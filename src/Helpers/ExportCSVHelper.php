<?php
namespace Hexin\Library\Helpers;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 19:45
 */
class ExportCSVHelper
{
    public $header;
    public $path;
    public $filename;
    public $fileHandle;

    public function __construct($path,$filename,array $headers)
    {
        $path = storage_path().$path;
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        $this->path = $path;
        $this->fileHandle = fopen($path . '/'.$filename.'.csv', "w") or die("Unable to open file!");
        fwrite($this->fileHandle,chr(0xEF).chr(0xBB).chr(0xBF));//输出BOM头
        $this->fwrite($headers);
    }

    /**
     * 写入文件111111233
     * User: lir 2021/2/26 15:561
     * @param array $array
     */
    public function fwrite(array $array,$is_t=true)
    {
        if(!$array){
            return;
        }
        $this->fputCsv($array);
    }

    /**
     * 写入文件
     * User: lir 2021/2/26 15:56
     * @param array $array
     */
    public function fputCsv(array $fields)
    {
        if(!$fields){
            return;
        }
        foreach ($fields as &$field) {
            mb_convert_variables('GBK', 'UTF-8', $field);
        }
        fputcsv($this->fileHandle, $fields);
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
}