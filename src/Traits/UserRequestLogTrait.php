<?php

namespace Hexin\Library\Traits;

use App\Http\Repositories\BaseRepository;
use Hexin\Library\Model\JobMessageModel;
use Hexin\Library\Model\UserRequestLogModel;
use Illuminate\Support\Facades\DB;

trait UserRequestLogTrait
{

    /**
     *  黑名单的路由不记录
     * @var array
     */
public  $blak_list = [];

public  function setBlackList(array $black_list):void
{
        $this->blak_list = $black_list;
}

    /**
     * Notes:  记录用户的请求记录
     * Author: RockLau
     * DateTime: 2026/5/28 17:31
     * @param $route
     * @param $module
     */
    public function setUserRequestLog($route,$method,$module = '')
    {
        if(in_array($route,$this->blak_list)) {
        return ;
        }
        $route_menu = [];
        $route_menu_file =  file_get_contents(env('STAT_URL').'/route_menu.json');
            if(!empty($route_menu_file)) {
                $route_menu = json_decode($route_menu_file,true);
            }
        $menu_arr = $route_menu[$route.'-'.$method] ?? [];

        $data = [
            'route' => $route,
            'method' => $method,
            'uuid' => config('userInfo')['uuid'] ?? '',
            'uname' => config('userInfo')['member_name'] ?? '',
            'module' => $module ? $module : ($menu_arr ? $menu_arr['module'] : ''),
            'action' => $menu_arr ? $menu_arr['action'] : $route,
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
        ];
        DB::connection('DB_OLAP')->table('user_request_log')->insert($data);

    }

}