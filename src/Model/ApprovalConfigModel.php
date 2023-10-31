<?php

namespace Hexin\Library\Model;

class ApprovalConfigModel extends Model
{
    const FORMAT_BETWEEN = 1;
    const FORMAT_GT = 2;
    const FORMAT_GTE = 3;
    const FORMAT_LT = 4;
    const FORMAT_LTE = 5;

    public $formula = [
        self::FORMAT_BETWEEN => 'between',
        self::FORMAT_GT => '>',
        self::FORMAT_GTE => '>=',
        self::FORMAT_LT => '<',
        self::FORMAT_LTE => '<='
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (! isset($this->connection)) {
            $this->setConnection(config('approval_process.database_connection'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('approval_process.approval_config_table_name'));
        }
    }

    protected $fillable = [
         'merchant_id', //int(11) NOT NULL DEFAULT '0', //COMMENT '商户id',
          'status', //tinyint(1) NOT NULL DEFAULT '1', //COMMENT '审批流控制状态：1开启 2关闭 0删除',
          'type', //int(11) NOT NULL DEFAULT '1', //COMMENT '状态：1采购审批流 2质检审批流',
         'use_condition',
          'create_uuid', //varchar(50) NOT NULL DEFAULT '', //COMMENT '创建人uuid',
          'create_name', //varchar(50) NOT NULL DEFAULT '', //COMMENT '创建人name',
          'create_time', //datetime NOT NULL DEFAULT '0000-00-00 00:00:00', //COMMENT '创建时间',
          'update_time', //datetime NOT NULL DEFAULT '0000-00-00 00:00:00', //COMMENT '更新时间',
    ];


}
