## laravel工具包

1. Jobmessage 队列管理工具
2. redis锁
3. 一些helper类型：数组、日期、请求、字符串、导出csv类
4. 审批流
5. 导出xls类
6. 文件导入任务
7. 导出任务

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
            //业务代码
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

## 四、审批流
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

## 五、导出xls类
```php
<?php
namespace App\Test;

use Hexin\Library\Helpers\ExportXLSHelper;

class Test

    /**
     *  逐行写入数据 导出
     */
    public function testWriteData()
    {
         $fieldArr = ['id' => 'ID', 'name' => '名称']; // 表头
         $file_name = '文件名';

        $list = [
            [
                'id' => 1,
                'name' => '名称'
            ],
            [
                'id' => 2,
                'name' => '名称2'
            ]
        ];

        $file_name = $file_name . '.xlsx';

        $XLSWriteExcelExport = (new ExportXLSHelper($fieldArr, $file_name));

        foreach ($list as $v) {
            $XLSWriteExcelExport->writeData($v);
        }

        $filePath = $XLSWriteExcelExport->store();
        return response()->download($filePath, $file_name, $headers = ['Content-Type' => 'application/vnd.ms-excel;charset=utf-8']);
    }

    /**
     *  一次性写入数据 导出
     */
    public function testWriteDataList()
    {
        $fieldArr = ['id' => 'ID', 'name' => '名称']; // 表头
        $file_name = '文件名';

        $list = [
            [
                'id' => 1,
                'name' => '名称'
            ],
            [
                'id' => 2,
                'name' => '名称2'
            ]
        ];

        $file_name = $file_name . '.xlsx';

        $XLSWriteExcelExport = (new ExportXLSHelper($fieldArr, $file_name));

        $XLSWriteExcelExport->writeDataList($list);

        $filePath = $XLSWriteExcelExport->store();
        return response()->download($filePath, $file_name, $headers = ['Content-Type' => 'application/vnd.ms-excel;charset=utf-8']);
    }
}
```
## 六、文件导入

###1、创建表
```sql
CREATE TABLE `file_import_task` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `handle_status` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '1 待处理 20校验数据中 21数据校验失败 22数据校验通过 30等待数据导入，31 数据导入中 32 部分数据导入 33数据导入完成 34导入失败 40 业务处理中  41部分完成 42已完成',
    `business_type` int NOT NULL COMMENT '业务类型',
    `original_file_name` varchar(100) NOT NULL COMMENT '原文件名',
    `fail_msg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '错误原因',
    `merchant_id` int unsigned NOT NULL COMMENT '商户id',
    `admin_uuid` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '操作人uuid',
    `admin_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '操作人',
    `update_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '数据更新时间',
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '数据添加时间',
    `save_file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '保存文件完整路径',
    `save_file_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '保存的文件名',
    `read_total_rows` int NOT NULL COMMENT '代码获取到的行数 不一定是真正有效的行数 ',
    `file_size` int unsigned NOT NULL COMMENT '文件大小 bytes',
    `import_start_time` int unsigned NOT NULL DEFAULT '0' COMMENT '导入开始时间',
    `import_end_time` int unsigned NOT NULL DEFAULT '0' COMMENT '导入结束时间',
    `server_local_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '' COMMENT '服务器本地路径',
    `related_condition1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '关联条件1 根据各自业务去存',
    `related_condition2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '关联条件2 根据各自业务去存',
    `related_condition3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT '关联条件3 根据各自业务去存',
    `remark` varchar(255) NOT NULL COMMENT '备注',
    `success_num` int NOT NULL COMMENT '成功数量',
    `fail_num` int NOT NULL COMMENT '失败数量',
    `total_nums` int NOT NULL COMMENT '总数量',
    `data_start_time` int NOT NULL COMMENT '数据开始时间',
    `data_end_time` int NOT NULL COMMENT '数据结束时间',
    PRIMARY KEY (`id`) USING BTREE,
    KEY `handle_status` (`business_type`,`handle_status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='文件导入任务';
```
## 七、导出任务

###1、配置yar-services.php,新增'ExpertListServices'
```php
return [
    'storage'   => [
        'path' => env('STORAGE_URL', '127.0.0.1').'/yar/', 'services' => [
            'ExpertListServices' => 'ExpertListServices',
        ]
    ],
];
?php>
```
###2、创建导出任务
```php
<?php

namespace App\Console\Commands\Export;

use Hexin\Library\Model\ExportJobModel;
use Illuminate\Console\Command;
use Hexin\Library\Traits\ExportDataTrait;
use App\Libs\Helpers\ExportCSVHelper;

class ExportTask extends Command
{
    /**
    * 创建导出任务
    * @throws \Exception
     */
    public function export()
    {
        //create export
        ExportJobModel::createExportJob(
            $type,//类型，仓储的type
            $filename,//导出文件名
            $params,//条件
            [
                'template_type' => 'exportExample',//模板类型
                'class_name' => __CLASS__,//导出的类,一般是当然类
                'method' => 'exportExampleData',//导出的方法
                'dir_name' => 'olap',//导出存储的目录，app/exports/dir_name/2024-01-01
            ]
        );
    }
    
    /**
    * 导出数据
    * @throws \Exception
     */
    public final exportExampleData($params, $path, $file_name)
    {
        $header = [
            'id',
            '名称',
        ];
        $exportCSVHelper = new ExportCSVHelper($path, $filename, $header);
        $query = (new Model())->getQuery($params);
        $query->chunkById(10000, function ($chunk) use ($exportCSVHelper) {
            foreach ($chunk as $item) {
                $row = [];
                foreach ($item as $field => $val) {
                    $row[] = $value;
                }
                $exportCSVHelper->fputCsv($row);
            }
        });
        $exportCSVHelper->fclose();
    }
}
?php>
```
###3、创建定时任务,每秒执行
```php
<?php

namespace App\Console\Commands\Export;

use Hexin\Library\Model\ExportJobModel;
use Illuminate\Console\Command;
use Hexin\Library\Traits\ExportDataTrait;

class ExportTask extends Command
{
    use ExportDataTrait;

    /**
     * php artisan command:export_task
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:export_task {template_type?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导出任务，每分钟执行一次';

    /**
    * 模板类型 
    * @var string[] 
     */
    public static $template_type = [
        'exportExample',
    ];

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
        $template_type = $this->argument('template_type');
        if (!$template_type) {
            $template_type = array_map(function ($item) {
                return "'{$item}'";
            }, self::$template_type);
            $template_type = implode(',', $template_type);
        } else {
            $template_type = "'{$template_type}'";
        }
        $params = [
            'where' => "template_type in ({$template_type}) and handling_status=" . ExportJobModel::HANDLING_STATUS_WAIT,
        ];
        $ErpExportTasks = ExportJobModel::getStorageExport($params); //默认rpc获取任务，可传第二个参数，从指定模型读取
        if (!$ErpExportTasks) {
            $this->info('没有导出任务');
            return;
        }
        $this->traitRunCommand($ErpExportTasks);//默认rpc获取任务，可传第二个参数，从指定模型读取和导出

        return true;
    }
}
?php>
```