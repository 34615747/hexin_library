<?php

namespace Hexin\Library\Model;

class ApprovalLevelModel extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (! isset($this->connection)) {
            $this->setConnection(config('approval_process.database_connection'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('approval_process.approval_level_table_name'));
        }
    }
    protected $fillable = [
         'merchant_id', //int(11) NOT NULL DEFAULT '0', //COMMENT '商户id',
          'status', //tinyint(1) NOT NULL DEFAULT '1', //COMMENT '状态：1开启 2关闭 0删除',
          'approval_condition', //int(10) NOT NULL DEFAULT '1', //COMMENT '审批条件 1应付金额 2实付金额 3差价 4运费 5异常件数 6异常金额',
          'formula', //varchar(20) NOT NULL DEFAULT '', //COMMENT '公式',
          'min', //decimal(10,2) NOT NULL COMMENT 'min',
          'max', //decimal(10,2) NOT NULL COMMENT 'max',
          'type', //int(11) NOT NULL DEFAULT '1', //COMMENT '状态：1采购审批流 2质检审批流',
          'create_uuid', //varchar(50) NOT NULL DEFAULT '', //COMMENT '创建人uuid',
          'create_name', //varchar(50) NOT NULL DEFAULT '', //COMMENT '创建人name',
          'update_uuid', //varchar(50) NOT NULL DEFAULT '', //COMMENT '更新人uuid',
          'update_name', //varchar(50) NOT NULL DEFAULT '', //COMMENT '更新人name',
    ];

    public function approval_process()
    {
        return $this->hasMany(ApprovalProcessModel::class, 'approval_level_id', 'id');
    }
}
