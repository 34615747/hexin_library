<?php

namespace Hexin\Library\Jobs;

use Hexin\Library\Model\JobMessageModel;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;


class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $is_cli;
    public $job_message_id;
    public $JobMessageModel;
    public $beginTransaction = false;
    public $tries = 5;

    /**
     * 任务可以执行的秒数 (超时时间)。1小时
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($job_message_id)
    {
        $this->job_message_id = $job_message_id;
        $this->is_cli = self::isCli();
    }

    /**
     * JobMessageModel名
     * @return string
     */
    public static function getJobMessageModelName()
    {
        return JobMessageModel::class;
    }

    /**
     * 执行之前
     */
    public function handleBefore()
    {
        //todo 初始化静态变量等
    }

    /**
     * 执行队列
     *
     * @return void
     */
    public function handle()
    {
        try{
            $this->handleBefore();
            /**@var $JobMessageModel \Hexin\Library\Model\JobMessageModel*/
            $JobMessageModelName = static::getJobMessageModelName();
            $JobMessageModel = $JobMessageModelName::find($this->job_message_id);
            if(!$JobMessageModel){
                throw new \Exception('任务不存在');
            }
            $this->JobMessageModel = $JobMessageModel;
            if($JobMessageModel->status != $JobMessageModelName::STATUS_JOB){
                echo '请选择队列中的队列:'.$JobMessageModel->_id.PHP_EOL;
                return;
            }
//            if($JobMessageModel->is_retry == 2){ 
//                echo '该任务不允许重试，忽略处理:'.$JobMessageModel->_id.PHP_EOL;
//                return;
//            }
//            if($JobMessageModel->status == $JobMessageModelName::STATUS_ED){
//                echo '该任务已完成，忽略处理:'.$JobMessageModel->_id.PHP_EOL;
//                return;
//            }
            $JobMessageModel->ing();
            if($this->is_cli){
                echo '开始:'.$JobMessageModel->_id.PHP_EOL;
            }

            $res = $this->runBody();

            $JobMessageModel->succ($res);
            if($this->is_cli){
                echo '已处理'.PHP_EOL;
            }
        }catch (\Exception $e){
            $this->rollBackDB();
            $this->failAfter($e);
            if(isset($JobMessageModel)){
                $JobMessageModel->fail($e);
            }
            if($this->is_cli){
                $this->failed($e);
            }
            if($JobMessageModel->is_now == 1){
                throw new \Exception($e->getMessage());
            }
        }
    }

    /**
     * 失败之后处理
     * @param $e
     */
    public function failAfter($e)
    {
        //todo 释放锁，记录日志等
    }

    /**
     * 执行内容
     * User: lir 2021/10/25 16:42
     */
    public function runBody()
    {
        //todo 子类编写
        return '';
    }


    /**
     * 执行失败的任务。
     * @param \Exception $exception
     */
    public function failed(\Exception $e)
    {
        if($this->is_cli){
            echo 'file:'.$e->getFile().',line:'.$e->getLine().',msg:'.$e->getMessage().PHP_EOL;

            $msg = $e->getMessage();
            if(strpos($msg,'Connection refused') !== false){
                //连接被拒绝，需重启
                exit();
            }
        }
    }

    /**
     * 开启事务
     * User: lir 2021/12/9 11:52
     */
    public function beginTransactionDB()
    {
        DB::beginTransaction();
        $this->beginTransaction = true;
    }

    /**
     * 提交事务
     * User: lir 2021/12/9 11:52
     */
    public function commitDB()
    {
        if($this->beginTransaction){
            DB::commit();
            $this->beginTransaction = false;
        }
    }

    /**
     * 回滚事务
     * User: lir 2021/12/9 11:51
     */
    public function rollBackDB()
    {
        if($this->beginTransaction){
            DB::rollBack();
            $this->beginTransaction = false;
        }
    }

    /**
     * 是否Cli模式
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function isCli()
    {
        if (preg_match("/cli/i", php_sapi_name())) {
            return true;
        }
        return false;
    }

}
