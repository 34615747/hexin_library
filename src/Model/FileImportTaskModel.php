<?php

namespace Hexin\Library\Model;

abstract class FileImportTaskModel extends Model
{
    public $table = 'file_import_task';

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
}