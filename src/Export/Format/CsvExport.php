<?php

namespace Hexin\Library\Export\Format;

class CsvExport extends AbstractFormatExport
{
    public function __construct(string $absoluteSavePath, string $saveFilename)
    {
        $saveFilename = preg_match('/\.(csv)$/', $saveFilename) ? $saveFilename : $saveFilename.'.csv';
        parent::__construct($absoluteSavePath, $saveFilename);
        if (!is_dir($absoluteSavePath)) {
            mkdir($absoluteSavePath, 0777, true);
        }
        $this->fileHandle = fopen($absoluteSavePath . '/'.$saveFilename, "w") or die("Unable to open file!");
        fwrite($this->fileHandle,chr(0xEF).chr(0xBB).chr(0xBF));//输出BOM头
    }

    function batchWriteList(array $dataList)
    {
        foreach ($dataList as $item) {
            $this->write($item);
        }
    }

    function write(array $data)
    {
        fputcsv($this->fileHandle, $data);
    }

    function close()
    {
        if($this->fileHandle){
            @fclose($this->fileHandle);
            $this->fileHandle = null;
        }
    }

    function setHeader(array $header, $style = null)
    {
        $this->write($header);
    }
}