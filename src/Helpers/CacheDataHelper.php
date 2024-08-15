<?php

namespace Hexin\Library\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheDataHelper
{
    public static $ttl = 120;

    protected static $storeKey = 'hexin_library:hexin_site:store:';
    protected static $memberKey = 'hexin_library:hexin_site:member:';
    protected static $countryKey = 'hexin_library:hexin_site:country:';
    protected static $brandKey = 'hexin_library:hexin_site:brand:';
    protected static $companyKey = 'hexin_library:hexin_site:company:';

    /**
     * 获取店铺简单数据
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public static function getStore(array $where, string $field = 'id,name,code')
    {
        if (!empty($where['merchant_id'])) {
            $where['user_id'] = $where['merchant_id'];
            unset($where['merchant_id']);
        }
        ksort($where);
        $field = explode(',', $field);
        ksort($field);
        $field = implode(',', $field);
        $params = ['field' => $field, 'where' => $where];
        $key = self::$storeKey . md5(json_encode($params));
        $result = Cache::store('redis_common')->remember($key, self::$ttl, function () use ($params) {
            return YarHelper::call($params, YarHelper::yarGetStore());
        });
        return $result;
    }

    /**
     * 获取用户简单数据
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public static function getMember(array $where, string $field = 'uuid,name')
    {
        if (!empty($where['merchant_id'])) {
            $where['user_id'] = $where['merchant_id'];
            unset($where['merchant_id']);
        }
        ksort($where);
        $field = explode(',', $field);
        ksort($field);
        $field = implode(',', $field);
        $params = ['field' => $field, 'where' => $where];
        $key = self::$memberKey . md5(json_encode($params));
        $result = Cache::store('redis_common')->remember($key, self::$ttl, function () use ($params) {
            return YarHelper::call($params, YarHelper::yarGetMember());
        });
        return $result;
    }

    /**
     * 获取用户简单数据
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public static function getCountry(array $where, string $field = 'iso_code_2,name')
    {
        if (!empty($where['merchant_id'])) {
            $where['user_id'] = $where['merchant_id'];
            unset($where['merchant_id']);
        }
        ksort($where);
        $field = explode(',', $field);
        ksort($field);
        $field = implode(',', $field);
        $params = ['field' => $field, 'where' => $where];
        $key = self::$countryKey . md5(json_encode($params));
        $result = Cache::store('redis_common')->remember($key, self::$ttl, function () use ($params) {
            return YarHelper::call($params, YarHelper::yarGetCountry());
        });
        return $result;
    }

    /**
     * 获取用户简单数据
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public static function getBrand(array $where, string $field = 'id,name')
    {
        if (!empty($where['merchant_id'])) {
            $where['user_id'] = $where['merchant_id'];
            unset($where['merchant_id']);
        }
        ksort($where);
        $field = explode(',', $field);
        ksort($field);
        $field = implode(',', $field);
        $params = ['field' => $field, 'where' => $where];
        $key = self::$brandKey . md5(json_encode($params));
        $result = Cache::store('redis_common')->remember($key, self::$ttl, function () use ($params) {
            return YarHelper::call($params, YarHelper::yarGetBrand());
        });
        return $result;
    }

    /**
     * 获取用户简单数据
     * @param array $where
     * @param string $field
     * @return mixed
     */
    public static function getCompany(array $where, string $field = 'id,name')
    {
        if (!empty($where['merchant_id'])) {
            $where['user_id'] = $where['merchant_id'];
            unset($where['merchant_id']);
        }
        ksort($where);
        $field = explode(',', $field);
        ksort($field);
        $field = implode(',', $field);
        $params = ['field' => $field, 'where' => $where];
        $key = self::$companyKey . md5(json_encode($params));
        $result = Cache::store('redis_common')->remember($key, self::$ttl, function () use ($params) {
            return YarHelper::call($params, YarHelper::yarGetCompany());
        });
        return $result;
    }
}