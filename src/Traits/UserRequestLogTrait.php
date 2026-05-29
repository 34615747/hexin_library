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
    public function setUserRequestLog($route,$module = '')
    {
        if(in_array($route,$this->blak_list)) {
        return ;
        }
        $data = [
            'route' => $route,
            'uuid' => config('userInfo')['uuid'] ?? '',
            'uname' => config('userInfo')['member_name'] ?? '',
            'module' => $module ? $module : '',
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
        ];
        DB::connection('DB_OLAP')->table('user_request_log')->insert($data);

    }

}