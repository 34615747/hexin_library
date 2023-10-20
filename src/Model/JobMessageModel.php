<?php
namespace Hexin\Library\Model;

use Illuminate\Database\Eloquent\Model;
use Hexin\Library\Jobs\ExampleJob;

/**
 * Class JobMessageModel
 * @package App\Models\Mongodb
 */
class JobMessageModel extends Model
{
    public $table = 'job_message';

    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';

    /**
     *
     CREATE TABLE `order_job_message` (
    `id` int NOT NULL AUTO_INCREMENT,
    `merchant_id` int NOT NULL DEFAULT '0' COMMENT '商户id',
    `platform_id` int NOT NULL DEFAULT '0' COMMENT '平台id',
    `business_type` smallint NOT NULL DEFAULT '0' COMMENT '类型',
    `business_type_name` varchar(64) NOT NULL DEFAULT '' COMMENT '类型名称',
    `params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '参数',
    `is_now` tinyint(1) NOT NULL DEFAULT '2' COMMENT '是否马上执行队列,1是2否',
    `fail_count` int NOT NULL DEFAULT '0' COMMENT '失败次数',
    `is_retry` tinyint(1) NOT NULL DEFAULT '2' COMMENT '重跑次数',
    `command_run_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '定时任务的运行开始时间（延迟队列可用）',
    `status` smallint NOT NULL DEFAULT '2' COMMENT '状态',
    `status_name` varchar(64) NOT NULL DEFAULT '' COMMENT '状态名称',
    `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
    `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '开始时间',
    `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '结束时间',
    `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
    PRIMARY KEY (`id`),
    KEY `business_type` (`business_type`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=15662 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
     */
    protected $fillable = [
        'id',
        'merchant_id',
        'platform_id',
        'business_type',
        'business_type_name',
        'params',
        'is_now',
        'fail_count',
        'is_retry',
        'command_run_time',
        'status',
        'status_name',
        'update_time',
        'create_time',
        'start_time',
        'end_time',
        'remark',
    ];

    /**
     * 最大失败次数
     */
    public const MAX_FAIL_COUNT = 3;

    /**
     * 队列名称
     */
    const JOB_NAME_EXAMPLE = 'example';

    /**
     * 队列列表
     * todo 重写
     */
    const BUSINESS_TYPE_EXAMPLE = 1;
    public static $businessTypeLabel = [
        self::BUSINESS_TYPE_EXAMPLE => '示例',
    ];
    /**
     * 状态
     */
    const STATUS_ED = 1;
    const STATUS_FAIL = 2;
    const STATUS_ING = 3;
    const STATUS_JOB = 4;
    const STATUS_WAIT = 5;
    const STATUS_STOP = 6;
    public static $statusLabel = [
        self::STATUS_ED => '已完成',
        self::STATUS_FAIL => '失败',
        self::STATUS_ING => '进行中',
        self::STATUS_JOB => '队列中',
        self::STATUS_WAIT => '待处理',
        self::STATUS_STOP => '停止',
    ];

    //-----------------------------------------------------
    // Sub-Category activityQuery
    //-----------------------------------------------------



    //-----------------------------------------------------
    // Sub-Category view
    //-----------------------------------------------------

    public function viewBusinessType()
    {
        return self::$businessTypeLabel[$this->business_type]??'';
    }
    public function viewStatus()
    {
        return self::$statusLabel[$this->status]??'';
    }

    //-----------------------------------------------------
    // Sub-Category methods
    //-----------------------------------------------------


    /**
     * 队列详情
     * todo 重写
     * User: lir 2022/2/21 11:53
     * @return array
     */
    public static function jobDetail()
    {
        return [
            self::BUSINESS_TYPE_EXAMPLE => ['max_fail_count'=>3],
        ];
    }

    /**
     * 获取最大的失败次数
     * User: lir 2022/2/21 12:07
     * @return int|mixed
     */
    public function getMaxFailCount()
    {
        $max_fail_count = self::MAX_FAIL_COUNT;
        $jobDetail = self::jobDetail()[$this->business_type]??[];
        if($jobDetail){
            $max_fail_count = $jobDetail['max_fail_count']??self::MAX_FAIL_COUNT;
        }
        return (int)$max_fail_count;
    }

    /**
     * 是否重试
     * User: lir 2022/2/21 12:08
     * @return bool
     */
    public function isRetry()
    {
        if($this->is_now == 1){
            return false;
        }
        if($this->fail_count>$this->getMaxFailCount()){
            return false;
        }
        return true;
    }

    /**
     * 忽略的错误消息-匹配到的话，队列状态会变为停止
     * todo 重写
     * User: lir 2022/3/16 16:59
     * @return array
     */
    public function filterErrorMsg()
    {
        return [
            '不跑了，直接停吧',
        ];
    }

    /**
     * 等待中
     */
    public function wait()
    {
        $this->status = self::STATUS_WAIT;
        $this->status_name = $this->viewStatus();
        if($this->remark){
            $this->remark = '';
        }
        if($this->start_time){
            $this->start_time = '';
        }
        if($this->end_time){
            $this->end_time = '';
        }
        $this->save();
    }

    /**
     * 队列入库
     * @param $merchant_id 商户id
     * @param $business_type 队列类型
     * @param array $data 参数
     * @param int $is_now 是否同步执行
     * @param string $command_run_time 定时任务的运行开始时间（延迟队列可用）
     * @param int $platform_id 平台id
     * @return JobMessageModel|void
     */
    public static function insertMsg($merchant_id,$business_type,array $data,$is_now=2,$command_run_time='',$platform_id=0)
    {
        if(!self::isCanUse($business_type,$merchant_id)){
            return;
        }
        $JobMessageModel = new self();
        $JobMessageModel->merchant_id = $merchant_id;
        $JobMessageModel->platform_id = $platform_id;
        $JobMessageModel->business_type = $business_type;
        $JobMessageModel->business_type_name = $JobMessageModel->viewBusinessType();
        $JobMessageModel->params = json_encode($data,JSON_UNESCAPED_UNICODE);
        $JobMessageModel->is_now = $is_now;
        $JobMessageModel->command_run_time = $command_run_time == '' ? time() : $command_run_time;
        $JobMessageModel->fail_count = 0;
        $JobMessageModel->is_retry = 1;
        $JobMessageModel->wait();

        return $JobMessageModel;
    }

    /**
     * 是否可以使用
     * User: lir 2022/10/9 14:09
     * @param $business_type
     * @param $user_id
     * @return bool
     * @throws \App\Exceptions\ApiException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function isCanUse($business_type,$merchant_id)
    {
        return true;
    }



    /**
     * 进行中
     */
    public function ing()
    {
        $this->status = self::STATUS_ING;
        $this->status_name = $this->viewStatus();
        $this->start_time = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * 成功记录
     */
    public function succ($remark='')
    {
        $this->remark = $remark;
        $this->status = self::STATUS_ED;
        $this->status_name = $this->viewStatus();
        $this->end_time = date('Y-m-d H:i:s');
        $this->save();
    }

    /**
     * 失败记录
     * @param \Exception $e
     */
    public function fail(\Exception $e)
    {
        $remark = [
            'file'=>$e->getFile(),
            'line'=>$e->getLine(),
            'msg'=>$e->getMessage(),
        ];
        $this->status = self::STATUS_FAIL;
        $this->status_name = $this->viewStatus();
        $this->end_time = date('Y-m-d H:i:s');
        $this->remark = json_encode($remark,JSON_UNESCAPED_UNICODE);
        $this->fail_count = ($this->fail_count??0)+1;
        foreach ($this->filterErrorMsg() as $msg){
            if(strpos($remark['msg'],$msg) !== false){
                $this->fail_count = $this->getMaxFailCount()+1;
                $this->status = self::STATUS_STOP;
                $this->status_name = $this->viewStatus();
                break;
            }
        }
        if(!$this->isRetry()){
            $this->is_retry = 2;
        }
        $this->save();
    }

    /**
     * 插入队列
     * //todo 重写
     */
    public function insertJob()
    {
        switch ($this->business_type){
            case self::BUSINESS_TYPE_EXAMPLE:
                if($this->is_now == 2){
                    ExampleJob::dispatch($this->id)->onQueue(self::JOB_NAME_EXAMPLE);
                }else{
                    ExampleJob::dispatchNow($this->id);
                }
                break;
        }
    }
}
