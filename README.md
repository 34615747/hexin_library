## laravel工具包

1. Jobmessage 队列管理工具
2. redis锁
3. 导出csv类
4. 一些helper类型：数组、日期、请求、字符串

安装：composer require hexin/library

## 一、Jobmessage 队列管理工具
**①.杜绝事务回滚，但队列又执行的bug
②.担心事务没提交，设置队列的延迟时间过长的问题
③.执行记录留痕**

继承JobMessageModel，重写部分方法，比如insertJob等
###1、创建表
```sql
     CREATE TABLE `job_message` (
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
     ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
```
###2、插入队列
```php
<?php
namespace App\Test;

use Hexin\Library\Model\JobMessageModel;

class Test

    public function insertJob()
    {
        JobMessageModel::insertMsg(
                    2,
                    JobMessageModel::BUSINESS_TYPE_EXAMPLE,
                    $data
                );
    }
}
```



###3、处理队列，定时任务每秒执行
```php
<?php
namespace App\Console\Commands;

use Hexin\Library\Model\JobMessageModel;
use Hexin\Library\Traits\JobMessageCommand;
use Illuminate\Console\Command;

class JobMessage extends Command
{
    use JobMessageCommand;
    /**
     * php artisan command:job_message
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:job_message';

    /**
     * @var string
     */
    protected $description = '队列任务消费';


    /**
     * Create a new command instance.
     */
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
        $JobMessageModel = new JobMessageModel();
        $query = $JobMessageModel->newQuery();
        //待处理的任务
        $query->whereIn('status', [
            JobMessageModel::STATUS_WAIT
        ]);
        //指定任务类型
        $query->whereIn('business_type',
            [
                JobMessageModel::BUSINESS_TYPE_EXAMPLE
            ]
        );
        $this->traitRunCommand($query);
    }
    public function traitGetRunStep()
    {
        return 200;
    }
}
```


###4、处理失败的队列，定时任务，每秒执行
```php

<?php
namespace App\Console\Commands;

use Hexin\Library\Model\JobMessageModel;
use Hexin\Library\Traits\FailJobMessageCommand;
use Illuminate\Console\Command;

class FailJobMessage extends Command
{
    use  FailJobMessageCommand;
    /**
     * php artisan command:fail_job_message
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fail_job_message {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '失败的队列消息重跑';


    /**
     * Create a new command instance.
     *
     * @return void
     */
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
    	//处理失败，可重试的任务
        $Query = JobMessageModel::where([
            ['status',JobMessageModel::STATUS_FAIL],
            ['is_retry',JobMessageModel::IS_YES]
        ]);

        $id = $this->argument('id');

        if (!empty($id)) {
            $Query = $Query->where('_id', $id);
        }
        $this->traitRunCommand($Query);
        echo '完成';
    }

    public function traitGetRunStep()
    {
        return 100;
    }

}
```


## 二、redis锁
```php
<?php
namespace App\Test;

use Hexin\Library\Cache\Redis\Lock;

class Test

    public function lock()
    {
        $RedisKey = 'lock:123';
        $Lock = new Lock();
        //获取锁
        if(!$Lock->getLocalLock($RedisKey,300)){
            throw new ApiException([0,'正在执行，请稍后']);
        }
        try{
            DB::beginTransaction();
          
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            //释放锁
            Lock::releaseLock($RedisKey);
            throw new \Exception($e->getMessage());
        }
        //释放锁
        Lock::releaseLock($RedisKey);
     
    }
}
```
## 三、导出csv类
```php
<?php
namespace App\Test;

use Hexin\Library\Helpers\ExportCSVHelper;

class Test

    public function csv()
    {
        $header = [
            '姓名',
            '年龄',
        ];
       $filename = '学生的年龄';
       $path = '/app/exports/' . date('Y-m-d');
       $ExportCSVHelper = new ExportCSVHelper($path,$filename,$header);
       $data = [
            '张三','12岁'
        ];
       $ExportCSVHelper->fwrite($data);
       $ExportCSVHelper->fclose();
    }
}
```

## 四 审批流
```php
#### 审批配置 approval_process.php
<?php

return [
    // 审批配置表
    'approval_config_table_name' => 'hp_approval_config',
    // 审批泳道(等级)表
    'approval_level_table_name' => 'hp_approval_level',
    // 审批节点设置表
    'approval_process_table_name' => 'hp_approval_process',
    // 审批人表
    'approval_process_user_table_name' => 'hp_approval_process_user',

    // 数据库连接名称，不填写则使用默认配置
    'database_connection' => 'mysql',
];


<?php
namespace App\Http\Repositories\Base;

use App\Exceptions\ApiException;
use App\Http\Repositories\BaseRepository;
use App\Http\Repositories\Purchase\OrderApprovalRepository;
use App\Http\Repositories\Purchase\OrderRepository;
use App\Models\Approval\ApprovalConfigModel;
use App\Models\Approval\ApprovalLevelModel;
use App\Models\Approval\ApprovalProcessModel;
use App\Models\Approval\ApprovalProcessUserModel;
use App\Models\Discount\DiscountAccountStatementModel;
use App\Models\Purchase\Order;
use Illuminate\Support\Facades\Cache;
// 审批流设置
class ApprovalConfigRepository extends BaseRepository
{
    use \Hexin\Library\Traits\ApprovalConfigRepository;

    const TYPE_PURCHASE = 1;
    const TYPE_QC = 2;
    const TYPE_DISCOUNT = 4;
    const TYPE_SUPPLIER_RECONCILIATION = 3;

    public $type = [
        self::TYPE_PURCHASE => '采购审批流',
        self::TYPE_QC => '质检审批流',
        // 供应商对账审批流
        self::TYPE_SUPPLIER_RECONCILIATION => '供应商对账审批流',
        self::TYPE_DISCOUNT => '折扣单流水审批流',
    ];
    
    // 重写 应用至所有待审批
    public function applyToPending($data)
    {
        // 应用至所有待审批 todo
        $order_list = (new Order())->where('status', OrderRepository::STATUS_SUBMIT)->get();

        if (!$order_list->isEmpty()) {
            foreach ($order_list as $item) {
                $item->orderApproval()->delete();
                try {
                    // 创建订单审批流
                    OrderApprovalRepository::createOrderApproval($item);
                } catch (\Exception $e) {
                    throw new ApiException([ApiException::DEFAULT_ERROR_CODE, $e->getMessage()]);
                }
            }
        }
    }
}

```