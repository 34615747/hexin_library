<?php
namespace Hexin\Library\Helpers;
use Illuminate\Support\Facades\Cache;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 19:45
 */
class YarHelper
{

    /**
     * yar类
     * @return string
     */
    public static function yarClassName()
    {
        return '\Reprover\LaravelYar\Yar';
    }

    /**
     * 请求
     * @param $params
     * @param array $config YarHelper::yarAddExpertConf();
     */
    public static function call($params,$config = [])
    {
        //载入config配置
        $method = self::rpcConfig($config);
        $yar_name = self::yarClassName();

        $yarClient = new $yar_name($method);
        $res = $yarClient->call($params);
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
            'composer_storage_ExpertListServices_updateExpert' => [
                'module' => 'storage',
                'service' => 'ExpertListServices',
                'method' => 'updateExpert',
                'connect_timeout' => 60000,
                'read_timeout' => 60000,
            ]
        ];
    }

    /**
     * yar获取员工浏览权限配置
     * @return array[]
     */
    public static function yarGetViewAuthConf()
    {
        return [
            'composer_hexin_site_ViewAuthServices_getViewAuth' => [
                'module' => 'hexin_site',
                'service' => 'ViewAuthServices',
                'method' => 'getViewAuth',
                'connect_timeout' => 60000,
                'read_timeout' => 60000,
            ]
        ];
    }
}