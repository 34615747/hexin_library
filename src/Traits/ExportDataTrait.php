<?php

namespace Hexin\Library\Traits;

use Hexin\Library\Helpers\CommonHelper;
use Hexin\Library\Model\ExportJobModel;

trait ExportDataTrait
{

    /**
     * 内存限制
     * @return string
     */
    public function traitMemoryLimit()
    {
        return '1024M';
    }

    /**
     * Execute the console command.
     * $ExportJobs 任务队列
     * $ExportJobModelClassName 任务模型，无则走rpc更新队列数据
     * @return mixed
     */
    public function traitRunCommand($ExportJobs,$ExportJobModelClassName = '')
    {
        ini_set('memory_limit', $this->traitMemoryLimit());
        echo '开始导出，数量:' . count($ExportJobs) . PHP_EOL;
        foreach ($ExportJobs as $ExportJob) {
            //获取锁，获取不到则退出,或休眠1秒再获取
            $RedisKey = 'hexin_library:lock:export_data:'.($ExportJob['template_type']);
            $Lock = new \Hexin\Library\Cache\Redis\Lock();
            if(!$Lock->getLocalLock($RedisKey,300)){
                sleep(1);
                continue;
            }

            //读取1条待处理任务，无则退出
            $params = [
                'where'=>"id = ({$ExportJob['id']}) and handling_status=".ExportJobModel::HANDLING_STATUS_WAIT,
            ];
            $ErpExportTasks = ExportJobModel::getStorageExport($params,ExportJobModel::class);
            if (!$ErpExportTasks) {
                //释放锁
                \Hexin\Library\Cache\Redis\Lock::releaseLock($RedisKey);
                continue;
            }

            //状态改成进行中
            $ExportJob['handling_status'] = ExportJobModel::HANDLING_STATUS_ING;
            ExportJobModel::updateStorageExport($ExportJob,$ExportJobModelClassName);

            //释放锁
            \Hexin\Library\Cache\Redis\Lock::releaseLock($RedisKey);

            //执行导出
            /**@var $ExportJob \Hexin\Library\Model\ExportJobModel */
            $userInfo = [
                'uuid'=>$ExportJob['create_uuid'],
                'name'=>$ExportJob['create_name'],
            ];
            CommonHelper::setUserInfo($userInfo);
            echo $ExportJob['create_name'].'====》导出：【' . ExportJobModel::viewTypeText($ExportJob['type']).'】'.$ExportJob['file_name'] . PHP_EOL;
            try {
                $file_name = $ExportJob['file_name'];
                if (empty($file_name)) {
                    throw new \Exception('初始化文件名失败');
                }

                $params = json_decode($ExportJob['conditions'], true);
                foreach ($params as $field => $value) {
                    if ($value === null) {
                        unset($params[$field]);
                    }
                }

                $path = $storage_path = 'app/exports/' . $ExportJob['dir_name'] . '/' . date('Y-m-d');
                $path = '/' . $path;
                $path_all = storage_path($storage_path);
                if (!is_dir($path_all)) {
                    mkdir($path_all, 0777, true);
                }
                $ExportJob['start_time'] = date('Y-m-d H:i:s');

                //执行导出
                $class_name = resolve($ExportJob['class_name']);
                $mothod = $ExportJob['method'];
                $filePath = $class_name->{$mothod}($params, $path, $file_name);

                if(stripos($filePath, 'app') !== false){
                    $ExportJob['download_addreee'] = env('ASYNC_EXCEL_HOST') . '/storage' . $filePath;
                }else{
                    $ExportJob['download_addreee'] = env('ASYNC_EXCEL_HOST') . '/storage' . $path . '/' . $file_name . '.csv';
                }

                $ExportJob['end_time'] = date('Y-m-d H:i:s');
                $ExportJob['handling_status'] = ExportJobModel::HANDLING_STATUS_SUCCESS;
                ExportJobModel::successToUpdateStorageExport($ExportJob,$ExportJobModelClassName); //执行成功更新导出记录
                if (CommonHelper::isCli()) {
                    echo '已处理任务:' . $ExportJob['file_name'] . PHP_EOL;
                }
            } catch (\Exception $e) {
                $remark = [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'msg' => $e->getMessage(),
                ];
                $ExportJob['remark'] = json_encode($remark, JSON_UNESCAPED_UNICODE);
                $ExportJob['end_time'] = date('Y-m-d H:i:s');
                $ExportJob['handling_status'] = ExportJobModel::HANDLING_STATUS_FAIL;
                ExportJobModel::failToUpdateStorageExport($ExportJob,$ExportJobModelClassName); //执行失败更新导出记录
                if (CommonHelper::isCli()) {
                    echo '失败:' . $ExportJob['file_name'] . ',' . $e->getMessage() . PHP_EOL;
                }
            }
        }
        echo '结束';
    }
}
