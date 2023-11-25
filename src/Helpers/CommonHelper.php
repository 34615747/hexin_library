<?php
namespace Hexin\Library\Helpers;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 19:45
 */
class CommonHelper
{
    /**
     * 是否Cli模式
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function isCli()
    {
        if (preg_match("/cli/i", php_sapi_name())) {
            return true;
        }
        return false;
    }

    /**
     * 是否生产环境
     * User: lir 2020/9/2 16:02
     * @return bool
     */
    public static function isProduction()
    {
        if(env('APP_ENV') != 'production'){
            return false;
        }
        return true;
    }

    /**
     * 获得用户信息
     * User: lir 2020/4/22 11:06
     * @return string
     */
    public static function getUserInfo()
    {
        return app('config')->get('userInfo');
    }
}