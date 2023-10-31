<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalConfig extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_config', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id')->default(0)->comment('商户id');
            $table->tinyInteger('status')->default(1)->comment('审批流控制状态 1=开启 2=关闭 0=删除');
            $table->tinyInteger('use_condition')->default(2)->comment('是否开启条件控制 1=开启 2=关闭');
            $table->integer('type')->default(1)->comment('类型 1=采购审批流 2=质检审批流');
            $table->string('create_uuid', 50)->default('')->comment('创建人uuid');
            $table->string('create_name', 50)->default('')->comment('创建人name');
            $table->dateTime('create_time')->comment('创建时间');
            $table->dateTime('update_time')->comment('更新时间');
            $table->string('update_uuid', 50)->default('')->comment('更新人uuid');
            $table->string('update_name', 50)->default('')->comment('更新人name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_config');
    }
}
