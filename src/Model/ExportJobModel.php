<?php

namespace Hexin\Library\Model;

use Hexin\Library\Helpers\CommonHelper;
use Hexin\Library\Helpers\YarHelper;

class ExportJobModel extends Model
{
    protected $table = 'hexin_erp_logistics_export';


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

    public static function viewTypeText($type)
    {
        return static::TYPE[$type] ?? '';
    }

    public function viewStatusText()
    {
        return static::STATUS[$this->status] ?? '';
    }

    /**
     * 获得导出记录
     * @return mixed
     * @throws \Exception
     */
    public static function getStorageExport($params,$ExportJobModelClassName='')
    {
        try {
            if ($ExportJobModelClassName) {
                return $ExportJobModelClassName::selectRaw($params['field'] ?? '*')
                    ->whereRaw($params['where'])
                    ->get()
                    ->toArray();
            }
            $res = YarHelper::call($params,YarHelper::yarGetExpertListsConf());

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $res;
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
    public static function createExportJob($type, $file_name, $condition = null, $extra = [],$ExportJobModelClassName='')
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
                'create_uuid' => CommonHelper::getUUid(),
                'create_name' => CommonHelper::getUserName(),
            ];
            if ($ExportJobModelClassName) {
                $model = new self();
                $model->fill($params);
                $model->save();
                return $model->toArray();
            }
            $res = YarHelper::call($params,YarHelper::yarAddExpertConf());

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $res;
    }

    /**
     * 执行成功更新导出记录
     * @return mixed
     * @throws \Exception
     */
    public static function successToUpdateStorageExport($ExportJob,$ExportJobModelClassName='')
    {
        try {
            if ($ExportJobModelClassName) {
                $model = $ExportJobModelClassName::findOrFail($ExportJob['id']);
                $model->fill($ExportJob);
                $model->save();
                return [];
            }
            $res = YarHelper::call($ExportJob,YarHelper::yarUpdateExpertConf());

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
    public static function failToUpdateStorageExport($ExportJob,$ExportJobModelClassName='')
    {
        try {
            if ($ExportJobModelClassName) {
                $model = $ExportJobModelClassName::findOrFail($ExportJob['id']);
                $model->fill($ExportJob);
                $model->save();
                return [];
            }
            $res = YarHelper::call($ExportJob,YarHelper::yarUpdateExpertConf());

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return [];
    }

}