<?php

namespace Hexin\Library\Cache\Redis;

use Illuminate\Support\Facades\Cache;

/**
 * 锁
 * Trait ExchangeRate
 * @package App\Http\Libs\Traits
 */
class Lock
{
    /**
     * 锁
     * @var array
     */
    public static $locks = [];

    /**
     * 获得公共原子锁
     * User: lir 2020/9/4 11:23
     * @param $key
     * @param int $ttl
     * @return bool
     */
    public function getCommonAtomicLock($key,$ttl=60)
    {
        $lock = Cache::store('redis_common')->lock($key, $ttl);
        //未获得锁,且本地锁不存在
        if (!$lock->get() && !isset(self::$locks[$key])) {
            return false;
        }
        $this->addLock($key,$lock);
        return true;
    }

    /**
     * 释放指定锁
     * User: lir 2020/9/4 14:02
     * @param $key
     */
    public static function releaseLock($key)
    {
        if(isset(self::$locks[$key])){
            self::$locks[$key]->release();
            unset(self::$locks[$key]);
        }
    }

    /**
     * 释放所有锁
     * User: lir 2020/9/4 14:01
     */
    public static function releaseAllLock()
    {
        if(self::$locks){
            foreach (Lock::$locks as $key=>$handle){
                $handle->release();
            }
            self::$locks = [];
        }
    }

    /**
     * 添加锁
     * User: lir 2020/9/22 1:30
     * @param $key
     * @param $lock
     */
    public function addLock($key,$lock)
    {
        //本地锁不存在则添加
        if(!isset(self::$locks[$key])){
            self::$locks[$key] = $lock;
        }
    }

    /**
     * 获得本地锁
     * User: lir 2020/9/4 11:23
     * @param $key
     * @param int $ttl
     * @return bool
     */
    public function getLocalLock($key,$ttl=600)
    {
        $lock = Cache::lock($key, $ttl);
        //未获得锁,且本地锁不存在
        if (!$lock->get() && !isset(self::$locks[$key])) {
            return false;
        }
        $this->addLock($key,$lock);
        return true;
    }

}