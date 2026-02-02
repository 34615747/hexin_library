<?php

namespace Hexin\Library\Export;

use Hexin\Library\Export\Format\AbstractFormatExport;
use Hexin\Library\Export\Format\CsvExport;
use Hexin\Library\Export\Format\ExcelExport;
use Hexin\Library\Export\Format\PhpSpreadExcelExport;
use Hexin\Library\Export\Format\XlsxWriterExcelExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

abstract class AbstractExport
{
    protected $exportParams;
    /**@var $exportHelper AbstractFormatExport */
    protected $exportHelper;

    const FORMAT_FLOAT = "float";
    const FORMAT_INT = "int"; 

    const EXPORT_EXCEL = "excel";
    const EXPORT_CSV = "csv";
    const EXPORT_PHP_SPREAD_EXCEL = 'php_spread_excel';
    const EXPORT_XLSX_WRITER_EXCEL = 'xlsx_writer_excel';

    protected $absoluteSavePath;
    protected $saveFileName;
    protected $maxRows; //最大行数

    /**
     * @throws \Exception
     */
    public function __construct(array $exportParams, string $saveFilename, string $absoluteSavePath)
    {
        if (empty($absoluteSavePath)) throw new \Exception("保存的绝对路径不能为空");
        $this->exportParams = $exportParams;
        $this->absoluteSavePath = $absoluteSavePath;
        $this->saveFileName = empty($saveFilename) ? "export_".time()."_".rand(10000, 99999) : $saveFilename;
        $this->exportHelper = $this->getExportHelper();
        $this->exportHelper->setHeader($this->getExportHeader(), $this->getExportHeaderStyle());
    }

    /**
     * Desc: 导出头部 格式 key-value  格式 key 取值字段  val 表头对应的名称
     * Author: @zyouan
     * Date: 2024/1/30
     * @return array
     */
    protected abstract function getExportHeader() : array;

    protected function getExportHeaderStyle() : ?array
    {
        return null;
    }

    protected function getExportHeaderKeyMap() : array
    {
        return $this->getExportHeader();
    }

    /**
     * Desc: 需要格式化的字段
     * Author: @zyouan
     * Date: 2024/1/30
     * @return array
     */
    protected abstract function getFormatFieldArray() : array;

    protected abstract function query() :Builder;

    protected abstract function chunkSize(): int;

    protected function getChunkIdColumn(): string
    {
        return "";
    }

    protected function getChunkIdColumnAlias(): string {
        return "";
    }

    protected abstract function handleData(Array $dataList) :array;

    protected abstract function setStyle(array $data, int $startRow, int $endRow);

    protected abstract function getExportType() :string;

    public abstract function exportData();

    /**
     * Desc: 按chunkById 导出
     * Author: @zyouan
     * Date: 2024/1/31
     */
    protected function chunkByIdExport(){
        if (empty($this->getChunkIdColumn()) || empty($this->getChunkIdColumnAlias())){
            throw new \Exception("用chunkById的时候 getChunkIdColumn 和getChunkIdColumnAlias 要重写 并且不能为空");
        }
        $totalCount = 2;
        $formatFieldArray = $this->getFormatFieldArray();
        $floatNumberRows = $formatFieldArray[self::FORMAT_FLOAT] ?? [];
        $this->query()->chunkById($this->chunkSize(), function (Collection $modelList) use($floatNumberRows, &$totalCount){
           $this->handleExport($modelList, $floatNumberRows, $totalCount);
        }, $this->getChunkIdColumn() , $this->getChunkIdColumnAlias());
        $this->exportHelper->close();
    }

    /**
     * Desc:按chunk 导出
     * Author: @zyouan
     * Date: 2024/1/31
     */
    protected function chunkExport(){
        $totalCount = 2;
        $formatFieldArray = $this->getFormatFieldArray();
        $floatNumberRows = $formatFieldArray[self::FORMAT_FLOAT] ?? [];
        $this->query()->chunk($this->chunkSize(), function (Collection $modelList) use($floatNumberRows, &$totalCount){
            $this->handleExport($modelList, $floatNumberRows, $totalCount);
        });
        $this->exportHelper->close();
    }

    /**
     * Desc: 按分页方式导出
     * Author: @zyouan
     * Date: 2024/1/31
     */
    protected function paginationExport(){
        $totalCount = 2;
        $formatFieldArray = $this->getFormatFieldArray();
        $floatNumberRows = $formatFieldArray[self::FORMAT_FLOAT] ?? [];
        $totalNum = $this->query()->count();
        $totalPageNum = intval(ceil($totalNum/ $this->chunkSize()));
        for($i = 1; $i <= $totalPageNum; $i ++){
            $dataList = $this->query()->forPage($i, $this->chunkSize())->get();
            $this->handleExport($dataList,$floatNumberRows, $totalCount);
        }
        $this->exportHelper->close();
    }

    protected function handleExport(Collection $modelList, $floatNumberRows, &$totalCount) {
        $startRows = $totalCount;
        $count =  $modelList->count();
        $totalCount += ($count -1);
        $endRows = $totalCount;
        $dataList = $this->handleData($modelList->toArray());
        $headerList = $this->getExportHeaderKeyMap();
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
        $this->maxRows = $totalCount;
        $this->exportHelper->store();
    }

    /**
     * @throws \Exception
     */
    private function getExportHelper() :AbstractFormatExport
    {
        $type = $this->getExportType();
        switch($type){
            case self::EXPORT_EXCEL:
                return new ExcelExport($this->absoluteSavePath, $this->saveFileName);
            case self::EXPORT_CSV:
                return new CsvExport($this->absoluteSavePath, $this->saveFileName);
            case self::EXPORT_PHP_SPREAD_EXCEL:
                return new PhpSpreadExcelExport($this->absoluteSavePath, $this->saveFileName);
            case self::EXPORT_XLSX_WRITER_EXCEL:
                return new XlsxWriterExcelExport($this->absoluteSavePath, $this->saveFileName);
            default:
                throw new \Exception("暂不支持该方式导出");
        }
    }

    /**
     * @return mixed
     */
    public function getMaxRows()
    {
        return $this->maxRows;
    }

    protected function echoUseMen($i){
        $memoryUsageBytes = memory_get_usage();
        // 转换为MB，保留两位小数
        $memoryUsageMb = round($memoryUsageBytes / 1024 / 1024, 2);
        echo date("Y-m-d H:i:s")." ". $i." 当前内存使用量：{$memoryUsageMb} MB ".PHP_EOL;
        Log::debug($i." 当前内存使用量：{$memoryUsageMb} MB ");
    }

}