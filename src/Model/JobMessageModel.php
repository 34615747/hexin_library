<?php
namespace Hexin\Library\Model;

use Hexin\Library\Jobs\ExampleJob;

/**
 * Class JobMessageModel
 * @package App\Models\Mongodb
 */
class JobMessageModel extends Model
{
    public $table = 'job_message';

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
        'params_md5',
        'delete_time',
        'extend_params1',
        'extend_params2',
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
        return static::$businessTypeLabel[$this->business_type]??'';
    }
    public function viewStatus()
    {
        return static::$statusLabel[$this->status]??'';
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
//            self::BUSINESS_TYPE_EXAMPLE => ['max_fail_count'=>3,'retry_msgs'=>['*']],//*代表，任何错误消息都重试，否则指定内容['curl','500']
        ];
    }

    /**
     * 获取最大的失败次数
     * User: lir 2022/2/21 12:07
     * @return int|mixed
     */
    public function getMaxFailCount()
    {
        $max_fail_count = static::MAX_FAIL_COUNT;
        $jobDetail = static::jobDetail()[$this->business_type]??[];
        if($jobDetail){
            $max_fail_count = $jobDetail['max_fail_count']??static::MAX_FAIL_COUNT;
        }
        return (int)$max_fail_count;
    }

    /**
     * 获取重试消息
     * User: lir 2022/2/21 12:07
     * @return int|mixed
     */
    public function getRetryMsgs()
    {
        $jobDetail = static::jobDetail()[$this->business_type]??[];
        $retry_msgs = $jobDetail['retry_msgs']??[];
        return $retry_msgs;
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
    public static function insertMsg($merchant_id,$business_type,array $data,$is_now=2,$command_run_time='',$platform_id=0,$extend_params1='',$extend_params2='')
    {
        if(!static::isCanUse($business_type,$merchant_id)){
            return;
        }
        $JobMessageModel = new static();
        $JobMessageModel->merchant_id = $merchant_id;
        $JobMessageModel->platform_id = $platform_id;
        $JobMessageModel->business_type = $business_type;
        $JobMessageModel->business_type_name = $JobMessageModel->viewBusinessType();

        $JobMessageModel->params_md5 = md5($JobMessageModel->merchant_id.'#'.$business_type.'#'.md5(json_encode($data)));

        if(static::isFilterRepeat($business_type)){
            //队列中存在相同参数的队列
            if($existsModel = self::where('params_md5',$JobMessageModel->params_md5)->whereIn('status',[self::STATUS_WAIT,self::STATUS_JOB])->first()){
                return $existsModel;
            }
        }

        $JobMessageModel->params = json_encode($data,JSON_UNESCAPED_UNICODE);
        $JobMessageModel->is_now = $is_now;
        $JobMessageModel->command_run_time = $command_run_time == '' ? date('Y-m-d H:i:s') : $command_run_time;
        $JobMessageModel->fail_count = 0;
        $JobMessageModel->is_retry = 2;
        $JobMessageModel->extend_params1 = $extend_params1;
        $JobMessageModel->extend_params2 = $extend_params2;
        $JobMessageModel->wait();

        return $JobMessageModel;
    }

    /**
     * 队列批量入库
     * @param $merchant_id 商户id
     * @param int $business_type 队列类型
     * @param array $data 参数
     * @param int $is_now 是否同步执行
     * @param string $command_run_time 定时任务的运行开始时间（延迟队列可用）
     * @param int $platform_id 平台id
     * @return JobMessageModel|void
     */
    public static function insertBatchMsg($merchant_id,$business_type,array $data,$is_now=2,$command_run_time='',$platform_id=0,$extend_params1='',$extend_params2='')
    {

        if(!static::isCanUse($business_type,$merchant_id)){
            return;
        }
        $p = [];
        foreach ($data as $item){
            $p[] = [
                'merchant_id'        => $merchant_id,
                'platform_id'        => $platform_id,
                'business_type'      => $business_type,
                'business_type_name' => static::$businessTypeLabel[$business_type]??'',
                'params_md5'         => md5($merchant_id.'#'.$business_type.'#'.md5(json_encode($item))),
                'params'             => json_encode($item,JSON_UNESCAPED_UNICODE),
                'is_now'             => $is_now,
                'command_run_time'   => $command_run_time == '' ? date('Y-m-d H:i:s') : $command_run_time,
                'fail_count'         => 0,
                'is_retry'           => 1,
                'status'             => self::STATUS_WAIT,
                'status_name'        => static::$statusLabel[self::STATUS_WAIT]??'',
                'create_time'        => date('Y-m-d H:i:s'),
                'extend_params1'=>$extend_params1,
                'extend_params2'=>$extend_params2,
            ];
        }
        return static::insert($p);
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
     * 是否过滤重复
     * User: lir 2022/10/9 14:09
     * @param $business_type
     * @param $user_id
     * @return bool
     * @throws \App\Exceptions\ApiException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public static function isFilterRepeat($business_type)
    {
        return false;
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
        if(is_array($remark)){
            $remark = json_encode($remark,JSON_UNESCAPED_UNICODE);
        }
        $this->remark = $remark??'';
        $this->status = self::STATUS_ED;
        $this->status_name = $this->viewStatus();
        $this->end_time = date('Y-m-d H:i:s');
        $this->delete_time = self::setSuccDeleteTime();
        $this->save();
    }

    /**
     * 设置成功的删除时间
     * 默认成功之后，会在30天后删除
     * @param int $daysToAdd 新增的天数
     * @return false|string
     */
    public static function setSuccDeleteTime($daysToAdd=30)
    {
        return date('Y-m-d H:i:s',strtotime("+{$daysToAdd} days"));
    }

    /**
     * 设置失败的删除时间
     * 默认失败之后，会在60天后删除
     * @param int $daysToAdd 新增的天数
     * @return false|string
     */
    public static function setFailDeleteTime($daysToAdd=60)
    {
        return date('Y-m-d H:i:s',strtotime("+{$daysToAdd} days"));
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
        //是否停止判断
        foreach ($this->filterErrorMsg() as $msg){
            if(strpos($remark['msg'],$msg) !== false){
                $this->fail_count = $this->getMaxFailCount()+1;
                $this->status = self::STATUS_STOP;
                $this->status_name = $this->viewStatus();
                break;
            }
        }
        //是否重试判断
        $retry_msgs = $this->getRetryMsgs();
        foreach ($retry_msgs as $retry_msg){
            if((strpos($remark['msg'],$retry_msg) !== false) || ($retry_msg == '*')){
                $this->is_retry = 1;
                $this->command_run_time = date('Y-m-d H:i:s',time());
                break;
            }
        }

        if(!$this->isRetry()){
            $this->is_retry = 2;
        }
        $this->delete_time = self::setFailDeleteTime();
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
