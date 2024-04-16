<?php

namespace Hexin\Library\Export\Format;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 此方式缺点：处理大文件时内存消耗大，可能导致内存溢出错误。 40W数据 要4G内存
 */
class PhpSpreadExcelExport extends AbstractFormatExport
{
    /** @var $fileHandle Worksheet */
    protected $fileHandle;
    /** @var $spreadsheet Spreadsheet */
    protected $spreadsheet;

    private $currentRowIndex = 0;//当前行

    public function __construct(string $absoluteSavePath, string $saveFilename)
    {
        if (!class_exists("PhpOffice\PhpSpreadsheet\Spreadsheet")){
            throw new \Exception("请先composer require phpoffice/phpspreadsheet");
        }
        // 如果文件名没有后缀xls|xlsx，自动加上
        $saveFilename = preg_match('/\.(xls|xlsx)$/', $saveFilename) ? $saveFilename : $saveFilename . '.xlsx';
        parent::__construct($absoluteSavePath, $saveFilename);
        if (!is_dir($absoluteSavePath)) {
            mkdir($absoluteSavePath, 0755, true);
        }
        $this->initFileHandle();

    }
    function setHeader(array $header, $style = null)
    {
        $rowIndex = 1;
        $columnIndex = 1;
        foreach ($header as $key => $val) {
            $this->fileHandle->setCellValueByColumnAndRow($columnIndex, $rowIndex, $val);
            $columnIndex ++;
        }
        $this->currentRowIndex += 1;
    }

    function batchWriteList(array $dataList)
    {
        foreach ($dataList as $rowIndex => $valList) {
            $row = $this->currentRowIndex + $rowIndex + 1;
            $column = 1;
            foreach ($valList as $columnIndex => $val) {
                $this->fileHandle->setCellValueByColumnAndRow($column, $row, $val);
                $column ++;
            }
        }
        $this->currentRowIndex += count($dataList);
    }

    function write(array $data)
    {
        $column = 1;
        $row = $this->currentRowIndex + 1;
        foreach ($data as $columnIndex => $val) {
            $this->fileHandle->setCellValueByColumnAndRow($column, $row, $val);
            $column ++;
        }
        $this->currentRowIndex += 1;
    }

    function close()
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($this->getFileSaveAbsolutePath());
    }
    public function initFileHandle()
    {
        if (!file_exists($this->getFileSaveAbsolutePath())){
            // 创建新的Spreadsheet对象
            $spreadsheet = new Spreadsheet();
            // 创建Excel写入器
            $writer = new Xlsx($spreadsheet);
            // 保存Excel文件到服务器的某个位置
            $writer->save($this->getFileSaveAbsolutePath());
        }
        $this->spreadsheet = IOFactory::load($this->getFileSaveAbsolutePath());
        // 获取活动工作表
        $this->fileHandle = $this->spreadsheet->getActiveSheet();
    }

    public function getFileHandle() :Worksheet
    {
        return $this->fileHandle;
    }
}