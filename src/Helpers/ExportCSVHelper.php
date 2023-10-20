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
     * 写入文件
     * User: lir 2021/2/26 15:56
     * @param array $array
     */
    public function fwrite(array $array,$is_t=true)
    {
        if(!$array){
            return;
        }
        $str =  "";
        foreach ($array as $key=>$item){
            $item = StringHelper::trimStr(($item));
            if (stripos($item, ",") !== false) { //检查是不是包含逗号 如果包含逗号 用""包着 就会被当作字符串处理了
                $item = '"' . $item . '"';
            }
            if($is_t){
                $item = $item."\t";
            }
            $str .= $item.",";
        }
        $encode = mb_detect_encoding($str, ['ASCII','GB2312','GBK','UTF-8']);
        !$encode && $encode = 'GBK';
        //mb_convert_variables('GBK', 'UTF-8', $str);
//        if(!in_array($encode,['CP936'])){
//            mb_convert_variables('UTF-8', $encode, $str);
//        }
        $str .= PHP_EOL;
        fwrite($this->fileHandle, $str);
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