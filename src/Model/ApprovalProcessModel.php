<?php

namespace Hexin\Library\Model;

class ApprovalProcessModel extends Model
{
    const MANUAL_CHECK = 1;
    const AUTO_BYPASS = 2;
    const AUTO_REJECTED = 3;
    // 审批类型
    protected $approval_type = [
        self::MANUAL_CHECK => '人工审核',
        self::AUTO_BYPASS => '自动通过',
        self::AUTO_REJECTED => '自动拒绝'
    ];
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (! isset($this->connection)) {
            $this->setConnection(config('approval_process.database_connection'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('approval_process.approval_process_table_name'));
        }
    }

    protected $fillable = [
          'merchant_id', //int(11) NOT NULL DEFAULT '0', //COMMENT '商户id',
          'approval_level_id', //int(11) NOT NULL COMMENT '审批泳道id',
          'approval_title', //varchar(200) NOT NULL DEFAULT '', //COMMENT '审批名称',
          'approval_type', //int(10) NOT NULL DEFAULT '1', //COMMENT '审批类型 1人工审核 2自动通过 3自动拒绝',
          'approved_by', //int(10) NOT NULL DEFAULT '1', //COMMENT '审批人 1指定成员 2直属上级 3多人审核',
          'approval_way', //tinyint(1) NOT NULL DEFAULT '1', //COMMENT '审批方式 1或签 2会签',
          'sort', //int(11) NOT NULL DEFAULT '1', //COMMENT '排序',
          'create_uuid', //varchar(50) NOT NULL DEFAULT '', //COMMENT '创建人uuid',
          'create_name', //varchar(50) NOT NULL DEFAULT '', //COMMENT '创建人name',
          'status', //tinyint(1) NOT NULL DEFAULT '1', //COMMENT '状态：1开启 2关闭 0删除',
    ];

    public function approval_process_user()
    {
        return $this->hasMany(ApprovalProcessUserModel::class, 'approval_process_id', 'id');
    }
}
