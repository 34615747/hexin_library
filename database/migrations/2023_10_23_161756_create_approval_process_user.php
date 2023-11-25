<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalProcessUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_process_user', function (Blueprint $table) {
            //   `id` int NOT NULL AUTO_INCREMENT COMMENT '主键',
            //  `merchant_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
            //  `approval_process_id` int NOT NULL DEFAULT '0' COMMENT '审批节点id',
            //  `approval_uuid` varchar(50) NOT NULL DEFAULT '' COMMENT '审批人uuid',
            //  `approval_name` varchar(50) NOT NULL DEFAULT '' COMMENT '审批人name',
            //  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态：1开启 2关闭 0删除',
            //  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
            //  `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
            $table->increments('id');
            $table->integer('merchant_id')->default(0)->comment('商户id');
            $table->integer('approval_process_id')->default(0)->comment('审批节点id');
            $table->string('approval_uuid', 50)->default('')->comment('审批人uuid');
            $table->string('approval_name', 50)->default('')->comment('审批人name');
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
        Schema::dropIfExists('approval_process_user');
    }
}
