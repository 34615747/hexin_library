<?php

namespace Hexin\Library\Command;

use Hexin\Library\Model\JobMessageModel;
use Hexin\Library\Traits\DeleteJobMessageCommand;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractJobMessageDelete extends Command
{
    use DeleteJobMessageCommand;
    /**
     * php artisan command:job_message_delete
     * @var string
     */
    protected $signature = 'command:job_message_delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除job_message的历史记录';

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
        $query = $this->getToDeleteQuery();
        $this->traitRunCommand($query);
    }

    abstract function getJobMessage() :Model;

    protected function getToDeleteQuery() :Builder
    {
        $query = $this->getJobMessage()->newQuery();
        $query->where("delete_time", "<=", date("Y-m-d H:i:s"));
        $query->where("delete_time", ">", "0000-00-00 00:00:00");
        $query->whereIn("status", [JobMessageModel::STATUS_ED,JobMessageModel::STATUS_FAIL,JobMessageModel::STATUS_STOP]);
        $query->select(['id']);
        $query->orderBy("id", "ASC");
        return $query;
    }
}