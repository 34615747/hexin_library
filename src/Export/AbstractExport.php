<?php

namespace Hexin\Library\Export;

use Hexin\Library\Export\Format\AbstractFormatExport;
use Hexin\Library\Export\Format\CsvExport;
use Hexin\Library\Export\Format\ExcelExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

abstract class AbstractExport
{
    protected $exportParams;
    /**@var $exportHelper AbstractFormatExport */
    protected $exportHelper;

    const FORMAT_FLOAT = "float";
    const FORMAT_INT = "int";

    const EXPORT_EXCEL = "excel";
    const EXPORT_CSV = "csv";

    protected $absoluteSavePath;
    protected $saveFileName;

    /**
     * @throws \Exception
     */
    public function __construct(array $exportParams, string $saveFilename, string $absoluteSavePath)
    {
        $this->exportParams = $exportParams;
        $this->absoluteSavePath = $absoluteSavePath;
        $this->saveFileName = $saveFilename;
        $this->exportHelper = $this->getExportHelper();
        $this->exportHelper->setHeader($this->getExportHeader());
    }

    /**
     * Desc: 导出头部 格式 key-value  格式 key 取值字段  val 表头对应的名称
     * Author: @zyouan
     * Date: 2024/1/30
     * @return array
     */
    protected abstract function getExportHeader() : array;

    /**
     * Desc: 需要格式化的字段
     * Author: @zyouan
     * Date: 2024/1/30
     * @return array
     */
    protected abstract function getFormatFieldArray() : array;

    protected abstract function query() :Builder;

    protected abstract function chunkSize(): int;

    protected abstract function getChunkIdColumn(): string;

    protected abstract function getChunkIdColumnAlias(): string;

    protected abstract function handleData(Array $dataList);

    protected abstract function setStyle(array $data, int $startRow, int $endRow);

    protected abstract function getExportType();

    function chunkByIdExport(){
        $totalCount = 2;
        $formatFieldArray = $this->getFormatFieldArray();
        $floatNumberRows = $formatFieldArray[self::FORMAT_FLOAT] ?? [];
        $this->query()->chunkById($this->chunkSize(), function (Collection $modelList) use($floatNumberRows, &$totalCount){
           $this->export($modelList, $floatNumberRows, $totalCount);
        }, $this->getChunkIdColumn() , $this->getChunkIdColumnAlias());
        $this->exportHelper->close();
    }

    function chunkExport(){
        $totalCount = 2;
        $formatFieldArray = $this->getFormatFieldArray();
        $floatNumberRows = $formatFieldArray[self::FORMAT_FLOAT] ?? [];
        $this->query()->chunk($this->chunkSize(), function (Collection $modelList) use($floatNumberRows, &$totalCount){
            $this->export($modelList, $floatNumberRows, $totalCount);
        });
        $this->exportHelper->close();
    }

    private function export(Collection $modelList, $floatNumberRows, &$totalCount) {
        $startRows = $totalCount;
        $count =  $modelList->count();
        $totalCount += $count;
        $endRows = $totalCount;
        $dataList = $this->handleData($modelList->toArray());
        $headerList = $this->getExportHeader();
        $writeList = [];
        foreach ($dataList as $data) {
            $tmp = [];
            foreach ($headerList as $headerKey=> $headerVal) {
                $val = $data[$headerKey] ?? "";
                if (in_array($headerKey, $floatNumberRows)){
                    $val = empty($val) ? 0 : floatval($val);
                }
                $tmp[] =$val;
            }
            $writeList[] = $tmp;
        }
        $this->exportHelper->batchWriteList($writeList);
        $this->setStyle($dataList, $startRows, $endRows);
        $this->exportHelper->store();
    }

    /**
     * @throws \Exception
     */
    private function getExportHelper() :AbstractFormatExport
    {
        $type = $this->getExportType();
        switch($type){
            case self::EXPORT_EXCEL == $type:
                return new ExcelExport($this->absoluteSavePath, $this->saveFileName);
            case self::EXPORT_CSV == $type:
                return new CsvExport($this->absoluteSavePath, $this->saveFileName);
            default:
                throw new \Exception("暂不支持该方式导出");
        }
    }

}