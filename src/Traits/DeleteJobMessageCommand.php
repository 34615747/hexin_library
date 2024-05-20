<?php

namespace Hexin\Library\Traits;

use Hexin\Library\Model\JobMessageModel;
use Illuminate\Database\Eloquent\Model;

trait DeleteJobMessageCommand
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

    public function traitHandleJob(Model $JobMessageModel)
    {
        //一条条删 不要批量删除 如果数据量大，批量删除是一个大事务
      $JobMessageModel->delete();
    }

}