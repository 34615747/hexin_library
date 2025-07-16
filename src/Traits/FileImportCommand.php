<?php

namespace Hexin\Library\Traits;

use Hexin\Library\Helpers\CommonHelper;
use Hexin\Library\Model\FileImportTaskModel;

trait FileImportCommand
{

    /**
     * 本地保存的目录
     * @return string
     */
    public function traitLocalSaveDirName()
    {
        return 'import_temp';
    }

    public static function traitInfo($msg)
    {
        if (CommonHelper::isCli()) {
            echo $msg.PHP_EOL;
        }
    }

    /**
     * @param $fileQuery
     * @param $businessType
     */
    public function traitRunCommand($fileQuery,$businessTypes)
    {
        $fileQuery->whereIn("business_type", $businessTypes);
        $fileQuery->where("handle_status", FileImportTaskModel::HANDLE_STATUS_TODO);
        $fileQuery->chunkById($this->traitGetRunStep(), function ($fileModels) {
           /** @var FileImportTaskModel $fileModel */
           foreach ($fileModels as $fileModel) {
               $fileModel = $fileModel->refresh();
               if ($fileModel->handle_status != FileImportTaskModel::HANDLE_STATUS_TODO){
                   self::traitInfo("文件-{$fileModel->id}不是待处理状态,跳过");
                   continue;
               }
               $userInfo = [
                   'uuid'=>$fileModel->admin_uuid,
                   'name'=>$fileModel->admin_name,
               ];
               CommonHelper::setUserInfo($userInfo);
               $fileInfo = $fileModel->toArray();
          
               self::traitInfo(date("Y-m-d H:i:s") . " 开始处理文件-{$fileInfo['id']}:" . $fileInfo['save_file_name']);
               try {
                   $localPath = $fileInfo['server_local_path'];
                   if (empty($localPath) || !file_exists($localPath)) {
                       $localPath = $this->traitSaveToLocal($fileInfo['save_file_path'], $this->traitLocalSaveDirName());
                       $fileModel->server_local_path = $localPath;
                   }
                   $fileModel->handle_status = FileImportTaskModel::HANDLE_STATUS_IMPORT_ING;
                   $fileModel->import_start_time = time();
                   $fileModel->save();

                   //执行业务
                   $class_name = resolve($fileModel->class_name);
                   $mothod = $fileModel->method;
                   list($success_num,$fail_num,$total_nums,$handle_status) = $class_name->{$mothod}($fileModel, $localPath);

                   $fileModel->success_num = $success_num;
                   $fileModel->fail_num = $fail_num;
                   $fileModel->total_nums = $total_nums;
                   $fileModel->handle_status = $handle_status;
                   $fileModel->import_end_time = time();
                   $fileModel->save();
               } catch (\Throwable $e) {
                   $fileModel->handle_status = FileImportTaskModel::HANDLE_STATUS_IMPORT_FAIL;
                   $fileModel->fail_msg = json_encode([$e->getMessage()]);
                   $fileModel->save();
                   self::traitInfo(date("Y-m-d H:i:s") . " 处理文件-{$fileInfo['id']}:" . $fileInfo['save_file_name'] . " 异常：" . $e->getMessage());
               }
               @unlink($localPath);
               self::traitInfo(date("Y-m-d H:i:s") . "结束处理文件-{$fileInfo['id']}:" . $fileInfo['save_file_name']);
           }
        });
    }
    
    /**
     * 保存文件到本地
     * @param $url
     * @param $localSaveDirName
     * @return string
     */
    private function traitSaveToLocal($url, $localSaveDirName): string
    {
        $fileInfo = file_get_contents($url);
        $sub_dir = storage_path('app/import/'.$localSaveDirName.'/') . date("Y-m-d") . '/';
        if (!(is_dir($sub_dir))) {
            mkdir($sub_dir, 0777, 1);
        }
        $extension = pathinfo($url);
        $file_name = date("YmdHis")."_".rand(11111,99999) . '.' . $extension['extension'];
        $localUrl = $sub_dir . $file_name;
        file_put_contents($localUrl, $fileInfo);
        return $localUrl;
    }


    /**
     * 分块大小
     * @return int
     */
    private function traitGetRunStep(): int
    {
        return 200;
    }
}