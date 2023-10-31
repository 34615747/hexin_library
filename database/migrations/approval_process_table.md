## 审批流相关表

CREATE TABLE `hp_approval_config` (
`id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
`merchant_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '审批流控制状态：1开启 2关闭 0删除',
`use_condition` tinyint NOT NULL DEFAULT '1' COMMENT '是否开启条件控制1开启 2关闭',
`type` int NOT NULL DEFAULT '1' COMMENT '类型 1=采购审批流 2=质检审批流 3=供应商对账审批 4=折扣单审批流',
`create_uuid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建人uuid',
`create_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建人name',
`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
`update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
`update_uuid` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新人uuid',
`update_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '更新人name',
PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=DYNAMIC COMMENT='审批流配置表';


CREATE TABLE `hp_approval_level` (
`id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
`merchant_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1开启 2关闭 0删除',
`approval_condition` int NOT NULL DEFAULT '1' COMMENT '审批条件 1应付金额 2实付金额 3差价 4运费 5异常件数 6异常金额 7对账结算金额 8对账计算件数 9不限制',
`formula` varchar(20) NOT NULL DEFAULT '' COMMENT '公式',
`min` decimal(10,2) NOT NULL COMMENT 'min',
`max` decimal(10,2) NOT NULL COMMENT 'max',
`type` int NOT NULL DEFAULT '1' COMMENT '状态：1采购审批流 2质检审批流',
`create_uuid` varchar(50) NOT NULL DEFAULT '' COMMENT '创建人uuid',
`create_name` varchar(50) NOT NULL DEFAULT '' COMMENT '创建人name',
`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
`update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
`update_uuid` varchar(50) NOT NULL DEFAULT '' COMMENT '更新人uuid',
`update_name` varchar(50) NOT NULL DEFAULT '' COMMENT '更新人name',
PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='审批泳道(等级)表';


CREATE TABLE `hp_approval_process` (
`id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
`merchant_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
`approval_level_id` int NOT NULL COMMENT '审批泳道id',
`approval_title` varchar(200) NOT NULL DEFAULT '' COMMENT '审批名称',
`approval_type` int NOT NULL DEFAULT '1' COMMENT '审批类型 1人工审核 2自动通过 3自动拒绝',
`approved_by` int NOT NULL DEFAULT '1' COMMENT '审批人 1指定成员 2直属上级 3多人审核',
`approval_way` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审批方式 1或签 2会签',
`sort` int NOT NULL DEFAULT '1' COMMENT '排序',
`create_uuid` varchar(50) NOT NULL DEFAULT '' COMMENT '创建人uuid',
`create_name` varchar(50) NOT NULL DEFAULT '' COMMENT '创建人name',
`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1开启 2关闭 0删除',
`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
`update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='审批节点设置';


CREATE TABLE `hp_approval_process_user` (
`id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
`merchant_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
`approval_process_id` int NOT NULL DEFAULT '0' COMMENT '审批节点id',
`approval_uuid` varchar(50) NOT NULL DEFAULT '' COMMENT '审批人uuid',
`approval_name` varchar(50) NOT NULL DEFAULT '' COMMENT '审批人name',
`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1开启 2关闭 0删除',
`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
`update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='审批人';


## 采购单审批流相关表
CREATE TABLE `hp_order_approval` (
`id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
`merchant_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
`order_code` varchar(32) NOT NULL DEFAULT '' COMMENT '订单编号',
`approval_process_id` int NOT NULL COMMENT '审批节点id',
`approval_title` varchar(200) NOT NULL DEFAULT '' COMMENT '审批名称',
`approval_type` int NOT NULL DEFAULT '1' COMMENT '审批类型 1人工审核 2自动通过 3自动拒绝',
`approved_by` int NOT NULL DEFAULT '1' COMMENT '审批人 1指定成员 2直属上级 3多人审核',
`approval_way` tinyint(1) NOT NULL DEFAULT '1' COMMENT '审批方式 1或签 2会签',
`approval_status` int NOT NULL DEFAULT '1' COMMENT '审批状态 1待审批 2已同意 3已拒绝',
`sort` int NOT NULL DEFAULT '1' COMMENT '排序',
`create_uuid` varchar(50) NOT NULL DEFAULT '' COMMENT '创建人uuid',
`create_name` varchar(50) NOT NULL DEFAULT '' COMMENT '创建人name',
`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1开启 2关闭 0删除',
`approval_time` datetime DEFAULT NULL COMMENT '审批时间',
`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
`update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='采购审批表';

CREATE TABLE `hp_order_approval_user` (
`id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
`merchant_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
`order_code` varchar(32) NOT NULL DEFAULT '' COMMENT '订单编号',
`approval_process_id` int NOT NULL COMMENT '审批节点id',
`order_approval_id` int NOT NULL COMMENT '采购审批表id',
`approval_uuid` varchar(50) NOT NULL DEFAULT '' COMMENT '审批人uuid',
`approval_name` varchar(50) NOT NULL DEFAULT '' COMMENT '审批人name',
`approval_status` int NOT NULL DEFAULT '1' COMMENT '审批状态 1待审批 2已同意 3已拒绝',
`status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1开启 2关闭 0删除',
`approval_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审批时间',
`approval_opinion` varchar(255) NOT NULL DEFAULT '' COMMENT '审批意见',
`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
`update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC COMMENT='采购审批人表';