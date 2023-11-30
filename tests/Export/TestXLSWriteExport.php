<?php
namespace Tests\Export;
use Hexin\Library\Helpers\ExportXLSHelper;
use Tests\TestCase;

class TestXLSWriteExport extends TestCase
{
    /**
     *  逐行写入数据 导出
     */
    public function testWriteData()
    {
         $fieldArr = ['id' => 'ID', 'name' => '名称']; // 表头
         $file_name = '文件名';

        $list = [
            [
                'id' => 1,
                'name' => '名称'
            ],
            [
                'id' => 2,
                'name' => '名称2'
            ]
        ];

        $file_name = $file_name . '.xlsx';

        $XLSWriteExcelExport = (new ExportXLSHelper($fieldArr, $file_name));

        foreach ($list as $v) {
            $XLSWriteExcelExport->writeData($v);
        }

        $filePath = $XLSWriteExcelExport->store();
        return response()->download($filePath, $file_name, $headers = ['Content-Type' => 'application/vnd.ms-excel;charset=utf-8']);
    }

    /**
     *  一次性写入数据 导出
     */
    public function testWriteDataList()
    {
        $fieldArr = ['id' => 'ID', 'name' => '名称']; // 表头
        $file_name = '文件名';

        $list = [
            [
                'id' => 1,
                'name' => '名称'
            ],
            [
                'id' => 2,
                'name' => '名称2'
            ]
        ];

        $file_name = $file_name . '.xlsx';

        $XLSWriteExcelExport = (new ExportXLSHelper($fieldArr, $file_name));

        $XLSWriteExcelExport->writeDataList($list);

        $filePath = $XLSWriteExcelExport->store();
        return response()->download($filePath, $file_name, $headers = ['Content-Type' => 'application/vnd.ms-excel;charset=utf-8']);
    }
}