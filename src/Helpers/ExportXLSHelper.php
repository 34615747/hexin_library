<?php
namespace Hexin\Library\Helpers;
// 需安装扩展包 https://pecl.php.net/package/xlswriter
use Vtiful\Kernel\Excel;

/**
 * Excel导出 逐行写入数据
 * 适用于大数据量导出 减少内存占用
 * Class XLSWriteExcelExportV1
 * @package App\Exports
 * $fieldArr = ['id' => 'ID', 'name' => '名称']; // 表头
 * $file_name = '文件名';
 * 用法：
 *        $file_name = $file_name . '.xlsx';
 *       $XLSWriteExcelExport = (new XLSWriteExcelExportV1($fieldArr, $file_name));
 *      $query->chunk(1000, function ($Models) use ($XLSWriteExcelExport) {
 *         if ($Models->isNotEmpty()) {
 *            foreach ($Models as $v) {
 *               $this->formatItem($v);
 *              $v = $v->toArray();
 *             $XLSWriteExcelExport->writeData($v);
 *        }
 *  }
 * });
 *
 * $filePath = $XLSWriteExcelExport->store();
 * return response()->download($filePath, $file_name, $headers = ['Content-Type' => 'application/vnd.ms-excel;charset=utf-8']);
 */
class ExportXLSHelper implements BasicExport
{
    protected $excel;
    protected $fieldsArr, $filename;

    protected $path = 'app/xls_excel_export';
    protected $full_path = '';
    /**
     * @param $fieldsArr ['id' => 'ID', 'name' => '名称'] 表头
     * @param $fileName //文件名
     * @param $path //保存路径
     */
    public function __construct($fieldsArr, $fileName, $path = 'app/xls_excel_export')
    {
        if(!StringHelper::isContainsDate($path)){
            $path = $path . '/' . date('Y-m-d');
        }
        $full_path = storage_path().$path;
        if (!is_dir($full_path)) {
            mkdir($full_path, 0777, true);
        }
        $config = [
            'path' => $full_path  // xlsx文件保存路径
        ];
        $this->path     = $path;
        $this->full_path     = $full_path;
        // 如果文件名没有后缀xls|xlsx，自动加上
        $fileName = preg_match('/\.(xls|xlsx)$/', $fileName) ? $fileName : $fileName . '.xlsx';
        $this->filename = $fileName;
        $this->fieldsArr = $fieldsArr;
        $this->excel = new Excel($config);
        $this->excel = $this->excel->fileName($fileName, 'sheet1');
        $this->excel = $this->excel->header(array_values($fieldsArr));
    }

    //写入数据
    public function fwrite($array)
    {
        $this->writeData($array);
    }
    //保存问件
    public function fclose()
    {
        $this->excel->output();
    }
    //获取文件地址(不含域名)
    public function getFilePath()
    {
        return $this->path . '/' . $this->filename;
    }

    public function getFileFullPath()
    {
        return $this->full_path . '/' . $this->filename;
    }

    /**
     * 设置单元格样式 设置成会计格式 数字保留两位小数
     * @param $cell //单元格  K:K
     * @param int $cell_with 列宽
     * @return mixed
     */
    public function setFinancialNumFormat($cell, $cell_with = 13)
    {
        $this->setNumberFormat($cell, '_ * #,##0.00_ ;_ * -#,##0.00_ ;_ * "-"??_ ;_ @_ ', $cell_with);
    }
    /**
     * 设置单元格样式 设置成会计格式 整数
     * @param $cell //单元格  K:L
     * @param int $cell_with 列宽
     *
     */
    public function setFinancialIntFormat($cell, $cell_with = 13)
    {
        $this->setNumberFormat($cell, '_ * #,##0_ ;_ * -#,##0_ ;_ * "-"_ ;_ @_ ', $cell_with);
    }
    public function download()
    {
        $filePath = $this->excel->output();
        // Set Header
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Disposition: attachment;filename="' . $this->filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        ob_clean();
        flush();

        if (copy($filePath, 'php://output') === false) {
            // Throw exception
        }

//        @unlink($filePath);
    }

    /**
     * 设置列宽
     *  @param $cell //单元格  A:K
     *  @param int $cell_with 列宽
     */
    public function setCellWith($cell, $cell_with = 13)
    {
        $this->excel->setColumn($cell, $cell_with);
    }

    /**
     * 设置单元格样式
     * @param $cell //单元格  K:K
     * @param int $cell_with 列宽
     * @param string $number_format 格式
     * @return mixed
     */
    public function setNumberFormat($cell, $number_format, $cell_with = 13)
    {
        $fileHandle = $this->excel->getHandle();
        $format = new \Vtiful\Kernel\Format($fileHandle);
        $numberStyle = $format->number($number_format)->toResource();
        $this->excel->setColumn($cell, $cell_with, $numberStyle);
    }

    /**
     * 设置单元格背景颜色
     * @param $cell //单元格  A1:L1
     * @param int $cell_with 列宽
     * @param string $color 颜色
     * @return mixed
     */
    public function setCellGroupColor($cell, $color, $cell_with = 13)
    {
        $fileHandle = $this->excel->getHandle();
        $format = new \Vtiful\Kernel\Format($fileHandle);
        $colorFormat = $this->mappingColor($format, $color);
        $style = $format->background($colorFormat)->toResource();

        $this->excel->setRow($cell, $cell_with, $style);
    }

    public function mappingColor($format, $color)
    {
        switch ($color){
            case 'COLOR_BLACK':
                return $format::COLOR_BLACK;
            case 'COLOR_BLUE':
                return $format::COLOR_BLUE;
            case 'COLOR_BROWN':
                return $format::COLOR_BROWN;
            case 'COLOR_CYAN':
                return $format::COLOR_CYAN;
            case 'COLOR_GRAY':
                return $format::COLOR_GRAY;
            case 'COLOR_GREEN':
                return $format::COLOR_GREEN;
            case 'COLOR_LIME':
                return $format::COLOR_LIME;
            case 'COLOR_MAGENTA':
                return $format::COLOR_MAGENTA;
            case 'COLOR_ORANGE':
                return $format::COLOR_ORANGE;
            case 'COLOR_PINK':
                return $format::COLOR_PINK;
            case 'COLOR_NAVY':
                return $format::COLOR_NAVY;
            case 'COLOR_PURPLE':
                return $format::COLOR_PURPLE;
            case 'COLOR_SILVER':
                return $format::COLOR_SILVER;
            case 'COLOR_YELLOW':
                return $format::COLOR_YELLOW;
            default:
                return $format::COLOR_WHITE;
        }
    }

    /**
     * 逐行写入数据
     */
    public function writeData($item)
    {
        $row = $this->formatData($item);
        $this->excel->data([$row]);
    }

    /**
     * 批量写入数据
     */
    public function writeDataList($list)
    {
        $row = $this->formatDataList($list);
        $this->excel->data($row);
    }

    // 新增一个sheet
    public function addSheet($sheetName)
    {
        $this->excel->addSheet($sheetName);
    }

    // 写入图片
    public function insertImage(int $row, int $column, string $imagePath, float $width, float $height)
    {
        $this->excel->insertImage($row, $column, $imagePath, $width, $height);
    }

    // 本地保存
    public function store()
    {
        $filePath = $this->excel->output();

        return $filePath;
    }

    public function getDownloadUrl()
    {
        return env('ASYNC_EXCEL_HOST') . '/storage/' . $this->path . '/' . $this->filename;
    }

    public function getDownloadPath()
    {
        return $this->path . '/' . $this->filename;
    }

    protected function formatData($item)
    {
        return array_map(function($k) use ($item) {
            return $item[$k] ?? '';
        }, array_keys($this->fieldsArr));
    }

    protected function formatDataList($list)
    {
        return array_map(function($item) {
            return array_map(function($k) use ($item) {
                return $item[$k] ?? '';
            }, array_keys($this->fieldsArr));
        }, $list);
    }
}

