<?php

namespace Hexin\Library\Model;

use Hexin\Library\Helpers\CommonHelper;

class ExportJobModel extends Model
{
    protected $table = 'hexin_erp_logistics_export';

    /**
     * 是否是模型自己
     * 是则save，否则rpc
     * @var bool
     */
    public $is_self_model = false;

    protected $fillable = [
        'merchant_id',
        'file_name',
        'download_addreee',
        'conditions',
        'remark',
        'start_time',
        'end_time',
        'status',
        'handling_status',
        'create_uuid',
        'create_name',
        'type',
        'template_type',
        'class_name',//执行的类名
        'method',//方法
        'dir_name',//导出的目录名，结构app/exports/dir_name/2023-01-01
    ];

    /**
     * 状态
     */
    const STATUS_OPEN = 1;
    const STATUS_STOP = 2;
    const STATUS_DEL = 0;
    const STATUS = [
        self::STATUS_OPEN => '启用',
        self::STATUS_STOP => '禁用',
        self::STATUS_DEL => '删除',
    ];

    /**
     * 类型
     */
    const TYPE_EXAMPLE = 1;
    const TYPE = [
        self::TYPE_EXAMPLE => '示例模块',
    ];

    /**
     * 处理状态
     */
    const HANDLING_STATUS_WAIT = 10;
    const HANDLING_STATUS_ING = 15;
    const HANDLING_STATUS_SUCCESS = 20;
    const HANDLING_STATUS_FAIL = 30;
    const HANDLING_STATUS = [
        self::HANDLING_STATUS_WAIT => '待处理',
        self::HANDLING_STATUS_ING => '处理中',
        self::HANDLING_STATUS_SUCCESS => '已处理',
        self::HANDLING_STATUS_FAIL => '失败',
    ];

    public function viewHandlingStatusText()
    {
        return static::HANDLING_STATUS[$this->handling_status] ?? '';
    }

    public function viewTypeText()
    {
        return static::TYPE[$this->type] ?? '';
    }

    public function viewStatusText()
    {
        return static::STATUS[$this->status] ?? '';
    }

    /**
     * yar类
     * @return string
     */
    public static function yarClassName()
    {
        return '\Reprover\LaravelYar\Yar';
    }

    /**
     * 创建导出任务
     * @param $type
     * @param $file_name
     * @param $condition
     * @param $extra
     * @return array
     * @throws \Exception
     */
    public function createExportJob($type, $file_name, $condition = null, $extra = [])
    {
        if (empty($type) || empty($file_name)) {
            throw new \Exception('参数错误');
        }
        $merchant_id = $extra['merchant_id'] ?? 2;
        $template_type = $extra['template_type'] ?? '';
        $class_name = $extra['class_name'] ?? '';
        $method = $extra['method'] ?? '';
        $dir_name = $extra['dir_name'] ?? '';
        try {
            $params = [
                'merchant_id' => $merchant_id,
                'file_name' => $file_name,
                'download_addreee' => '',
                'conditions' => !is_null($condition) ? json_encode($condition) : '',
                'type' => $type,
                'template_type' => $template_type,
                'handling_status' => self::HANDLING_STATUS_WAIT,
                'class_name' => $class_name,
                'method' => $method,
                'dir_name' => $dir_name,
                'create_uuid' => CommonHelper::getUserInfo()['member_uuid'] ?? '',
                'create_name' => CommonHelper::getUserInfo()['member_name'] ?? '',
            ];
            if ($this->is_self_model) {
                $this->fill($params)->save();
                return $this->id;
            }
            //载入config配置
            $method = self::rpcConfig(self::yarAddExpertConf());
            $yar_name = self::yarClassName();
            $yarClient = new $yar_name($method);
            $res = $yarClient->call($params);

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return [];
    }

    /**
     * 执行成功更新导出记录
     * @return mixed
     * @throws \Exception
     */
    public function successToUpdateStorageExport()
    {
        try {
            if ($this->is_self_model) {
                $this->save();
                return [];
            }
            //载入config配置
            $method = self::rpcConfig(self::yarUpdateExpertConf());
            $yar_name = self::yarClassName();
            $yarClient = new $yar_name($method);
            $res = $yarClient->call($this->toArray());

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return [];
    }

    /**
     * 执行失败更新导出记录
     * @return mixed
     * @throws \Exception
     */
    public function failToUpdateStorageExport()
    {
        try {
            if ($this->is_self_model) {
                $this->save();
                return [];
            }
            //载入config配置
            $method = self::rpcConfig(self::yarUpdateExpertConf());
            $yar_name = self::yarClassName();
            $yarClient = new $yar_name($method);
            $res = $yarClient->call($this->toArray());

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return [];
    }

    /**
     * 设置yar配置
     * @param $conf
     * @return int|string
     */
    public static function rpcConfig($conf)
    {
        $method = array_keys($conf)[0];
        $value = array_values($conf)[0];
        app('config')->set([('yar-map.' . $method) => $value]);
        return $method;
    }

    /**
     * yar创建导出配置
     * @return array[]
     */
    public static function yarAddExpertConf()
    {
        return [
            'composer_storage_expert_list_services_create_export' => [
                'module' => 'storage',
                'service' => 'ExpertListServices',
                'method' => 'addExpert',
                'connect_timeout' => 60000,
                'read_timeout' => 60000,
            ]
        ];
    }

    /**
     * yar更新导出配置
     * @return array[]
     */
    public static function yarUpdateExpertConf()
    {
        return [
            'composer_storage_expert_list_services_update_export' => [
                'module' => 'storage',
                'service' => 'ExpertListServices',
                'method' => 'updateExpert',
                'connect_timeout' => 60000,
                'read_timeout' => 60000,
            ]
        ];
    }
}