<?php

namespace Hexin\Library\Traits;

use Hexin\Library\Model\JobMessageModel;

trait JobMessageCommand
{
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function traitRunCommand($query)
    {
        $this->traitEchoStart();
        echo '总数量:'.$query->count().PHP_EOL;
        $query->chunkById($this->traitGetRunStep(), function ($JobMessageModels) {
            /** @var JobMessageModel $JobMessageModel */
            foreach ($JobMessageModels as $JobMessageModel) {
                if (!$this->traitHandleBefore($JobMessageModel)){
                    continue;
                }
                try {
                    $this->traitHandleJob($JobMessageModel);
                }catch (\Exception $e){
                    $this->traitEchoFail($e,$JobMessageModel);
                    continue;
                }
                $this->traitEchoSucc($JobMessageModel);
            }
        });
        $this->traitEchoEnd();
    }

    public function traitGetRunStep()
    {
        return 1000;
    }

    public function traitEchoFail($e,$JobMessageModel)
    {
        echo '失败'.date('Y-m-d H:i:s').$JobMessageModel->viewBusinessType() . ':' .$JobMessageModel->id.':'.$e->getMessage(). PHP_EOL;
    }

    public function traitEchoSucc($JobMessageModel)
    {
//        echo '成功'.date('Y-m-d H:i:s').$JobMessageModel->viewBusinessType() . ':' .$JobMessageModel->id. PHP_EOL;
    }
    
    public function traitEchoStart()
    {
        echo '开始'.date('Y-m-d H:i:s'). PHP_EOL;
    }

    public function traitEchoEnd()
    {
        echo '结束'.date('Y-m-d H:i:s'). PHP_EOL;
    }

    public function traitHandleJob($JobMessageModel)
    {
        $JobMessageModel->status = JobMessageModel::STATUS_JOB;
        $JobMessageModel->status_name = $JobMessageModel->viewStatus();
        $JobMessageModel->save();
        $JobMessageModel->insertJob($JobMessageModel);
    }

    public function traitHandleBefore(JobMessageModel $JobMessageModel): bool
    {
        return true;
    }


}