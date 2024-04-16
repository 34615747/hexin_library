<?php

namespace Hexin\Library\Export\Format;
/**
 * PHP_XLSXWriter是一个小而强悍的Excel读写插件，它并没有PHPExcel功能丰富，很多高级操作比如冻结表头，并不具备，但是它导出速度非常快，非常适合于数据量特别大，
 * 报表格式不是很复杂的导出需求
 */
class XlsxWriterExcelExport extends AbstractFormatExport
{
    /** @var $fileHandle \XLSXWriter  */
    protected $fileHandle;
    public function __construct(string $absoluteSavePath, string $saveFilename)
    {
        if (!class_exists("XLSXWriter")){
            throw new \Exception("请先composer require mk-j/php_xlsxwriter");
        }
        // 如果文件名没有后缀xls|xlsx，自动加上
        $saveFilename = preg_match('/\.(xls|xlsx)$/', $saveFilename) ? $saveFilename : $saveFilename . '.xlsx';
        parent::__construct($absoluteSavePath, $saveFilename);
        if (!is_dir($absoluteSavePath)) {
            mkdir($absoluteSavePath, 0755, true);
        }
        $this->fileHandle = new \XLSXWriter();
    }

    function setHeader(array $header, $style = null)
    {
        $this->fileHandle->writeSheetHeader('Sheet1', $header, $style);
    }

    function batchWriteList(array $dataList)
    {
        $this->fileHandle->writeSheet($dataList, 'Sheet1');

    }

    function write(array $data)
    {
        $this->fileHandle->writeSheetRow('Sheet1', $data);
    }

    function close()
    {
        $this->fileHandle->writeToFile($this->getFileSaveAbsolutePath());
    }

    public function getFileHandle() :\XLSXWriter
    {
       return $this->fileHandle;
    }
}