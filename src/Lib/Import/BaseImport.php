<?php

namespace Hexin\Library\Lib\Import;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithColumnLimit;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class BaseImport extends MultipleSheetImport implements ToCollection, WithStartRow, WithCalculatedFormulas, WithCustomCsvSettings, SkipsEmptyRows, WithColumnLimit, WithChunkReading
{
    /**
     * 读取的sheet，默认第一个
     * @var int
     */
    protected $sheet = 1;

    /**
     * 开始行数
     * @var int
     */
    protected $start_row = 1;

    /**
     * 终止列名
     * @var string
     */
    protected $end_column = '';

    /**
     * 分块行数
     * @var int
     */
    protected $chunk_size = 10;

    /**
     * 批量插入数
     * @var int
     */
    protected $insert_size = 1000;

    /**
     * 是否第一分块
     * @var bool
     */
    protected $first_chunk = true;

    /**
     * 成功行数
     * @var int
     */
    public static $success_count = 0;
    /**
     * 总行数
     * @var int
     */
    public static $total_count = 0;
    /**
     * 错误信息
     * @var array
     */
    public static $errorDetails = [];


    /**
     * 字段映射 ['表格实际名称' => '数据表字段名称']
     * @var array
     */
    protected $header;

    /**
     * 导入任务对象
     * @var mixed|null
     */
    protected $importTaskInfo;


    /**
     * 模板是否验证通过    当执行分块导入后，后面的数据时不执行验证模板
     * @var bool
     */
    protected $checkHeaderSuccess = true;

    protected $indexMapping = [];

    protected $header_row = 0;

    /**
     * BaseJournalImport constructor.
     * @param array $request
     */
    public function __construct($request = [])
    {
        $this->importTaskInfo = $request['importTaskInfo'] ?? null;
    }

    /**
     * 设置读取的sheet
     * @return array
     */
    public function sheets(): array
    {
        return [
            ($this->sheet - 1) => new static(['importTaskInfo' => $this->importTaskInfo])
        ];
    }

    /**
     * 设置开始行数
     * @return int
     */
    public function startRow(): int
    {
        return $this->start_row;
    }

    /**
     * 设置结束列
     * @return string
     */
    public function endColumn(): string
    {
        return $this->end_column ?: Coordinate::stringFromColumnIndex(count($this->header));
    }

    /**
     * 设置csv属性
     * delimiter,enclosure,line_ending,use_bom,include_separator_line,excel_compatibility,escape_character,contiguous,input_encoding
     * @return array
     */
    public function getCsvSettings(): array
    {
        return [
            // 'input_encoding' => 'GBK'
            'input_encoding' => 'UTF-8',
        ];
    }

    /**
     * 设置分块行数
     * @return int
     */
    public function chunkSize(): int
    {
        return $this->chunk_size;
    }

    /**
     * 设置批量插入数
     * @return int
     */
    public function insertSize(): int
    {
        return $this->insert_size ?: $this->chunk_size;
    }

    /**
     * 验证行
     * @param $key
     * @param $row
     * @return mixed
     */
    public function validationRow($key, $row)
    {
        return $row;
    }

    /**
     * 填充数据
     * @param $data
     * @return mixed
     */
    public function fillRowData($key,$data)
    {
        return $data;
    }

    /**
     * 验证数据填充行
     * @param $data
     * @return mixed
     */
    public function validationFillRowData($key,$data)
    {
        return true;
    }

    /**
     * 验证header
     * @param $collection
     * @return bool
     */
    public function validationHeader($collection)
    {
        if ($collection->isEmpty()) {
            self::$errorDetails[] = '上传文件与模板不一致';
            return false;
        }
        $excel_header = $collection[$this->header_row]->toArray();
        $header_fields = array_keys($this->header);
        //别删除后续优化需使用
        if(count($excel_header) > count($header_fields)){//上传文件的表头字段不能比模板的表头字段多多
            self::$errorDetails[] = '上传文件与模板不一致';
            return false;
        }
        foreach ($excel_header as $key => $value) {
            if(empty($value)){continue;}
            $index = array_search($value, $header_fields);
            if($index === false){
                self::$errorDetails[] = '上传文件与模板不一致';
                return false;
            }else{
                $this->indexMapping[$key] = $index;
            }
        }

        return true;
    }

    /**
     * @param Collection $collection
     * @return bool
     */
    public function collection(Collection $collection)
    {
        if(!$this->checkHeaderSuccess){
            return  false;
        }
        if (empty($this->importTaskInfo)) {
            self::$errorDetails[] = '任务发生变动';
            return false;
        }

        if ($this->first_chunk) {
            $this->first_chunk = false;
            //验证header
            if (!$this->validationHeader($collection)) {
                $this->checkHeaderSuccess = false;
                return false;
            }
        }
        if ($collection->isEmpty()) {
            return false;
        }


        $insert_data = [];

        $fields = array_values($this->header);
        foreach ($collection as $key => $row) {
            $key += self::$total_count;
            if ($key < 1 || $key < $this->header_row + 1) {
                continue;
            }
            $data = [];
            foreach ($row as $column => $value) {
                if (isset($fields[$column])) {//防止多余的列
                    $data[$fields[$column]] = trim($value);
                }
            }

            //验证行
            if (!$this->validationRow($key, $data)) {
                continue;
            }


            //数据填充
            $data = $this->fillRowData($key,$data);
            //验证数据填充行
            if (!$this->validationFillRowData($key, $data)) {
                continue;
            }
            $insert_data[] = $this->formatAddData($data);
        }

        $res = 0;
        if($this->isBatchAddData($insert_data)){
            $res = $this->batchAddData($insert_data);
        }

        self::$success_count += $res;
        self::$total_count += $collection->count();

        return true;
    }

    /**
     * 子类实现，插入/更新模型对象
     * @param array $data
     * @return null
     */
    public function getModel($data = [])
    {
        return null;
    }

    /**
     * 是否批量插入数据
     * @param array $insert_data
     * @return bool
     */
    public function isBatchAddData($insert_data = [])
    {
        return true;
    }

    /**
     * 批量新增数据
     * @param $insert_data
     * @param $unique_field_lines
     * @param $union_unique_field_lines
     * @return int
     */
    public function batchAddData(&$insert_data = [])
    {
        if (empty($insert_data)) {
            return 0;
        }
        $insert_res = 0;//插入成功数
        $delete_res = 0;//删除成功数
        //先批量插入
        $data_chunk = array_chunk($insert_data, $this->insertSize());
        unset($insert_data);//释放内存
        $model = $this->getModel();

        if (!is_null($model)) {
            foreach ($data_chunk as $chunk) {
                if ($model->insert($chunk)) {
                    $insert_res += count($chunk);
                }
            }
        }

        return $insert_res - $delete_res;
    }

    /**
     * 格式化要新增的数据
     * @param array $data
     * @return array
     */
    protected function formatAddData($data = [])
    {
        return $data;
    }

    /**
     * 获取成功行数
     * @return int
     */
    public function getSuccessCount()
    {
        return self::$success_count;
    }

    /**
     * 获取总行数
     * @return int
     */
    public function getTotalCount()
    {
        return self::$total_count;
    }

    /**
     * 获取失败行数
     * @return int
     */
    public function getFailCount()
    {
        return self::$total_count - self::$success_count - ($this->header_row+1);
    }

    /**
     * 获取错误信息
     * @return int
     */
    public function getErrorDetail()
    {
        return self::$errorDetails;
    }
}
