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
    protected $signature = 'command:job_message_fail_retry';

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
        $query = $this->getFailQuery();
        $this->traitRunCommand($query);
    }

    abstract function getJobMessage() :Model;

    protected function getFailQuery() :Builder
    {
        $query = $this->getJobMessage()->newQuery();
        $query->where(function (Builder $subQuery){
            $subQuery->whereIn('status', [
                JobMessageModel::STATUS_FAIL,
            ]);
            $subQuery->orWhere(function (Builder $subSubQuery) {
                $subSubQuery->where("status", JobMessageModel::STATUS_ING);
                $subSubQuery->where("start_time", "<", date("Y-m-d H:i:s", time() - 3600));//超过1小时的进行中 重新跑 可能线程被杀了或者资源耗尽了
            });
        });
        return $query;
    }
}