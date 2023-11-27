<?php

namespace Hexin\Library\Traits;

use Hexin\Library\Helpers\CommonHelper;
use Hexin\Library\Model\ExportJobModel;

trait ExportDataTrait
{

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function traitRunCommand($query)
    {
        ini_set('memory_limit', '1024M');
        $ExportJobModels = $query->get();
        echo '开始导出，数量:' . count($ExportJobModels) . PHP_EOL;
        foreach ($ExportJobModels as $ExportJobModel) {
            /**@var $ExportJobModel \Hexin\Library\Model\ExportJobModel */
            echo '导出：' . $ExportJobModel->viewTypeText() . PHP_EOL;
            try {
                $file_name = $ExportJobModel->file_name;
                if (empty($file_name)) {
                    throw new \Exception('初始化文件名失败');
                }

                $params = json_decode($ExportJobModel->conditions, true);
                foreach ($params as $field => $value) {
                    if ($value === null) {
                        unset($params[$field]);
                    }
                }

                $path = $storage_path = 'app/exports/' . $ExportJobModel->dir_name . '/' . date('Y-m-d');
                $path = '/' . $path;
                $path_all = storage_path($storage_path);
                if (!is_dir($path_all)) {
                    mkdir($path_all, 0777, true);
                }
                $ExportJobModel->start_time = date('Y-m-d H:i:s');

                //执行导出
                $class_name = resolve($ExportJobModel->class_name);
                $mothod = $ExportJobModel->method;
                $class_name->{$mothod}($params, $path, $file_name);

                $ExportJobModel->download_addreee = env('ASYNC_EXCEL_HOST') . '/storage' . $path . '/' . $file_name . '.csv';
                $ExportJobModel->end_time = date('Y-m-d H:i:s');
                $ExportJobModel->handling_status = ExportJobModel::HANDLING_STATUS_SUCCESS;
                $ExportJobModel->successToUpdateStorageExport(); //执行成功更新导出记录
                if (CommonHelper::isCli()) {
                    echo '已处理任务id:' . $ExportJobModel->id . PHP_EOL;
                }
            } catch (\Exception $e) {
                $remark = [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'msg' => $e->getMessage(),
                ];
                $ExportJobModel->remark = json_encode($remark, JSON_UNESCAPED_UNICODE);
                $ExportJobModel->end_time = date('Y-m-d H:i:s');
                $ExportJobModel->handling_status = ExportJobModel::HANDLING_STATUS_FAIL;
                $ExportJobModel->failToUpdateStorageExport(); //执行失败更新导出记录
                if (CommonHelper::isCli()()) {
                    echo '失败:' . $ExportJobModel->id . ',' . $e->getMessage() . PHP_EOL;
                }
            }
        }
        echo '结束';
    }
}
