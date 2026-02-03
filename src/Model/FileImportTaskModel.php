<?php

namespace Hexin\Library\Model;


use Hexin\Library\Helpers\CommonHelper;

abstract class FileImportTaskModel extends Model
{
    public $table = 'file_import_task';

    protected $guarded = [];

    /**
     * 处理状态 1 待处理 20校验数据中 21数据校验失败 22数据校验通过 30等待数据导入，31 数据导入中 32 部分数据导入 33数据导入完成 34导入失败 40 业务处理中  41部分完成 42已完成
     */
    const HANDLE_STATUS_TODO = 1;
    const HANDLE_STATUS_CHECK_ING = 20;
    const HANDLE_STATUS_CHECK_FAIL = 21;
    const HANDLE_STATUS_CHECK_PASS = 22;
    const HANDLE_STATUS_WAIT_IMPORT = 30;
    const HANDLE_STATUS_IMPORT_ING = 31;
    const HANDLE_STATUS_IMPORT_PART = 32;
    const HANDLE_STATUS_IMPORT_ALL = 33;
    const HANDLE_STATUS_IMPORT_FAIL = 34;
    const HANDLE_STATUS_BUSINESS_ING = 40;
    const HANDLE_STATUS_FINNISH_PART = 41;
    const HANDLE_STATUS_FINNISH_ALL = 42;

    //!! 处理状态的名称 各个业务可以根据自己的需求去自定义
    const HANDLE_STATUS_DESC = [
        self::HANDLE_STATUS_TODO => "待处理",
        self::HANDLE_STATUS_CHECK_ING => "数据校验中",
        self::HANDLE_STATUS_CHECK_FAIL => "数据校验失败",
        self::HANDLE_STATUS_CHECK_PASS => "数据校验通过",
        self::HANDLE_STATUS_WAIT_IMPORT => "等待导入数据中",
        self::HANDLE_STATUS_IMPORT_ING => "数据导入中",
        self::HANDLE_STATUS_IMPORT_PART => "数据部分导入",
        self::HANDLE_STATUS_IMPORT_ALL => "数据全部导入",
        self::HANDLE_STATUS_IMPORT_FAIL => "数据导入异常",
        self::HANDLE_STATUS_BUSINESS_ING => "业务处理中",
        self::HANDLE_STATUS_FINNISH_PART => "业务部分完成",
        self::HANDLE_STATUS_FINNISH_ALL => "业务全部完成",
    ];

    const BUSINESS_TYPE_SHOPIFY_TAX_IMPORT = 1; //shopify 税表导入

    public function viewHandleStatusText()
    {
        return static::HANDLE_STATUS_DESC[$this->handle_status]??'';
    }

    public function viewImportStartTime()
    {
        return $this->import_start_time ? date('Y-m-d H:i:s',$this->import_start_time) : '';
    }

    public function viewImportEndTime()
    {
        return $this->import_end_time ? date('Y-m-d H:i:s',$this->import_end_time) : '';
    }

    /**
     * 获取查询条件
     * @param $query
     * @param array $params
     * @return mixed
     */
    public static function getQueryConditions($query,$params = [])
    {
        if($params['handle_status']??''){
            $query->where('handle_status',$params['handle_status']);
        }
        if($params['business_type']??''){
            $query->where('business_type',$params['business_type']);
        }
        return $query;
    }


    /**
     * 添加数据
     * @param $data
     */
    public function addItem($data){
        $saveData = [];
        $saveData['business_type'] = intval($data['business_type']);
        $saveData['handle_status'] = self::HANDLE_STATUS_TODO;
        $saveData['admin_name'] =  CommonHelper::getUserName();
        $saveData['admin_uuid'] =  CommonHelper::getUUid();
        $saveData['file_size'] = $data['size']??0;
        $saveData['merchant_id'] = $data['merchant_id'];
        $saveData['original_file_name'] = $data['original_name']??'';
        $saveData['save_file_path'] = $data['save_file_path'];
        $saveData['save_file_name'] = $data['save_file_name'];
        $saveData['class_name'] = $data['class_name'];
        $saveData['method'] = $data['method'];
        $saveData['remark'] = addslashes($data['remark'] ?? "");
        $saveData['create_time'] = date("Y-m-d H:i:s");
        $saveData['update_time'] = date("Y-m-d H:i:s");
        if (!empty($data['related_condition1'])){
            if (is_array($data['related_condition1'])){
                $saveData['related_condition1'] = json_encode($data['related_condition1']);
            }else{
                $saveData['related_condition1'] = $data['related_condition1'];
            }
        }
        if (!empty($data['related_condition2'])){
            if (is_array($data['related_condition2'])){
                $saveData['related_condition2'] = json_encode($data['related_condition2']);
            }else{
                $saveData['related_condition2'] = $data['related_condition2'];
            }
        }
        if (!empty($data['related_condition3'])){
            if (is_array($data['related_condition3'])){
                $saveData['related_condition3'] = json_encode($data['related_condition3']);
            }else{
                $saveData['related_condition3'] = $data['related_condition3'];
            }
        }
        return self::insert($saveData);
    }

    /**
     * 是否支持的格式
     */
    public static function isCanExt($file_url,$exts=['xlsx', 'xlsm', "xls"])
    {
        $file_path_info = pathinfo($file_url);
        if (empty($file_path_info['extension']) || !in_array($file_path_info['extension'], $exts)) {
            return false;
        }
        return true;
    }
}