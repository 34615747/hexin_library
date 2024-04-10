<?php

namespace Hexin\Library\Export\Format;

use Vtiful\Kernel\Excel;

class ExcelExport extends AbstractFormatExport
{
    public function __construct(string $absoluteSavePath, string $saveFilename)
    {
        if (!class_exists("Vtiful\Kernel\Excel")){
            throw new \Exception("请先安装xlswriter 扩展");
        }
        $saveFilename = preg_match('/\.(xls|xlsx)$/', $saveFilename) ? $saveFilename : $saveFilename . '.xlsx';
        parent::__construct($absoluteSavePath, $saveFilename);
        if (!is_dir($absoluteSavePath)) {
            mkdir($absoluteSavePath, 0755, true);
        }
        $config = [
            'path' => $absoluteSavePath  // xlsx文件保存路径
        ];
        // 如果文件名没有后缀xls|xlsx，自动加上

        $this->fileHandle = new Excel($config);
        $this->fileHandle = $this->fileHandle->fileName($saveFilename, 'sheet1');
    }

    function batchWriteList(array $dataList)
    {
        $this->fileHandle->data($dataList);
    }

    function write(array $data)
    {
        $this->write([$data]);
    }

    function setHeader(array $header)
    {
        $this->fileHandle = $this->fileHandle->header(array_values($header));
    }

    function close()
    {
        $this->fileHandle = null;
    }

    function setColumnStyle($cellRange, $backgroundColor, int $border = 0,$width = 80)
    {
        $format = new \Vtiful\Kernel\Format($this->fileHandle->getHandle());
        $format->background($backgroundColor);
        empty($border) or $format->border($border);
        $numberStyle = $format->toResource();
        $this->fileHandle->setColumn($cellRange, $width, $numberStyle);
    }

    function setRowStyle($cellRange, $backgroundColor, int $border = 0, $height = 25)
    {
        $format = new \Vtiful\Kernel\Format($this->fileHandle->getHandle());
        $format->background($backgroundColor);
        empty($border) or $format->border($border);
        $backgroundStyle = $format->toResource();
        $this->fileHandle->setRow($cellRange, $height, $backgroundStyle);
    }

    public function store(){
        $this->fileHandle->output();
    }

    public function getFileHandle() :Excel
    {
        return $this->fileHandle;
    }

}