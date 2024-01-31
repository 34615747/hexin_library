<?php

namespace Hexin\Library\Export\Format;

abstract class AbstractFormatExport
{
    protected $absoluteSavePath;
    protected $saveFileName;
    protected $fileHandle;

    public function __construct(string $absoluteSavePath, string $saveFilename)
    {
        $this->absoluteSavePath = $absoluteSavePath;
        $this->saveFileName = $saveFilename;
    }
    abstract function setHeader(array $header);
    abstract function batchWriteList(array $dataList);
    abstract function write(array $data);
    abstract function close();


    public function setRowStyle($cellRange, int $backgroundColor,int $border = 0, int $height = 25){

    }
    public function setColumnStyle($cellRange,int $backgroundColor, int $border = 0, int $width = 80) {

    }

    function store()
    {
    }

    public function __destruct()
    {
        $this->close();
    }

}