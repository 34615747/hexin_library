<?php
namespace Hexin\Library\Model;

class ApprovalProcessUserModel extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (! isset($this->connection)) {
            $this->setConnection(config('approval_process.database_connection'));
        }

        if (! isset($this->table)) {
            $this->setTable(config('approval_process.approval_process_user_table_name'));
        }
    }

    protected $fillable = [
          'merchant_id', //int(11) NOT NULL DEFAULT '0', //COMMENT '商户id',
          'approval_process_id', //int(11) NOT NULL DEFAULT '0', //COMMENT '审批节点id',
          'approval_uuid', //varchar(50) NOT NULL DEFAULT '', //COMMENT '审批人uuid',
          'approval_name', //varchar(50) NOT NULL DEFAULT '', //COMMENT '审批人name',
          'status', //tinyint(1) NOT NULL DEFAULT '1', //COMMENT '状态：1开启 2关闭 0删除',
    ];


}
