<?php

namespace Hexin\Library\Command;

use Hexin\Library\Model\JobMessageModel;
use Hexin\Library\Traits\FailJobMessageCommand;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractJobMessageFailRetry extends Command
{
    use FailJobMessageCommand;

    /**
     * php artisan command:job_message_fail_retry
     * @var string
     */
    protected $signature = 'command:job_message_fail_retry {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'job_message队列重试机制';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(!static::failRetryBusinessType() && !static::ingRetryBusinessType()){
            echo '暂无重试任务'.PHP_EOL;
            return;
        }
        $query = $this->getFailQuery();
        $this->traitRunCommand($query);
    }

    abstract function getJobMessage() :Model;

    /**
     * 失败重试类型
     */
    public static function failRetryBusinessType()
    {
        return [];
    }

    /**
     * 进行中重试类型
     */
    public static function ingRetryBusinessType()
    {
        return [];
    }

    /**
     * 进行中的超时时间
     * @return int
     */
    public static function ingTimeOut()
    {
        return 3600;
    }

    protected function getFailQuery() :Builder
    {
        $id = $this->argument('id');
        $query = $this->getJobMessage()->newQuery();
        if ($id) {
            $query = $query->where('id', $id);
        }
        $query->where(function (Builder $subQuery){
            if(static::failRetryBusinessType()){
                $subQuery->where(function (Builder $subSubQuery){
                    $subSubQuery->whereIn('business_type',static::failRetryBusinessType());
                    $subSubQuery->where('status', JobMessageModel::STATUS_FAIL);
                    $subSubQuery->where('is_retry',\Hexin\Library\Model\Model::TRUE);
                });
            }
            if(static::ingRetryBusinessType()){
                $subQuery->orWhere(function (Builder $subSubQuery) {
                    $subSubQuery->whereIn('business_type',static::ingRetryBusinessType());
                    $subSubQuery->where("status", JobMessageModel::STATUS_ING);
                    $subSubQuery->where("start_time", "<", date("Y-m-d H:i:s", time() - static::ingTimeOut()));//超过n小时的进行中 重新跑 可能线程被杀了或者资源耗尽了
                });
            }
        });
        return $query;
    }
}