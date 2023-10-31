<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApprovalLevel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approval_level', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('merchant_id')->default(0)->comment('商户id');
            $table->tinyInteger('status')->default(1)->comment('状态：1开启 2关闭 0删除');
            $table->tinyInteger('approval_condition')->default(1)->comment('审批条件 1应付金额 2实付金额 3差价 4运费 5异常件数 6异常金额 7对账结算金额 8对账计算件数 9不限制');
            $table->string('formula', 20)->default('')->comment('公式');
            $table->decimal('min', 10, 2)->comment('min');
            $table->decimal('max', 10, 2)->comment('max');
            $table->integer('type')->default(1)->comment('类型：1=采购审批流 2=质检审批流');
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
        Schema::dropIfExists('approval_level');
    }
}
