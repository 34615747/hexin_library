<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalProcess extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_process', function (Blueprint $table) {
            //   `id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
            //  `merchant_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
            //  `approval_level_id` int NOT NULL COMMENT '审批泳道id',
            //  `approval_title` varchar(200) NOT NULL DEFAULT '' COMMENT '审批名称',
            //  `approval_type` int NOT NULL DEFAULT '1' COMMENT '审批类型 1人工审核 2自动通过 3自动拒绝',
            //  `approved_by` int NOT NULL DEFAULT '1' COMMENT '审批人 1指定成员 2直属上级 3多人审核',
            //  `approval_way` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审批方式 1或签 2会签',
            //  `sort` int NOT NULL DEFAULT '1' COMMENT '排序',
            //  `create_uuid` varchar(50) NOT NULL DEFAULT '' COMMENT '创建人uuid',
            //  `create_name` varchar(50) NOT NULL DEFAULT '' COMMENT '创建人name',
            //  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1开启 2关闭 0删除',
            //  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
            //  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
            $table->increments('id');
            $table->integer('merchant_id')->default(0)->comment('商户id');
            $table->integer('approval_level_id')->default(0)->comment('审批泳道id');
            $table->string('approval_title', 200)->default('')->comment('审批名称');
            $table->integer('approval_type')->default(1)->comment('审批类型 1人工审核 2自动通过 3自动拒绝');
            $table->integer('approved_by')->default(1)->comment('审批人 1指定成员 2直属上级 3多人审核');
            $table->tinyInteger('approval_way')->default(0)->comment('审批方式 1或签 2会签');
            $table->integer('sort')->default(1)->comment('排序');
            $table->string('create_uuid', 50)->default('')->comment('创建人uuid');
            $table->string('create_name', 50)->default('')->comment('创建人name');
            $table->tinyInteger('status')->default(1)->comment('状态：1开启 2关闭 0删除');
            $table->dateTime('create_time')->comment('创建时间');
            $table->dateTime('update_time')->comment('更新时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_process');
    }
}
