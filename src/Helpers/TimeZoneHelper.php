<?php

namespace Hexin\Library\Helpers;

use Carbon\CarbonTimeZone;
use Illuminate\Support\Carbon;

/**
 * 时区配置文件
 * Class TimeZone
 * @package App\Http\Repositories\Common\Config
 */
class TimeZoneHelper
{
    /** @var array ad时区 */
    public static $adTimeAreaSet = [
        'US'=>'America/Los_Angeles',
        'CA'=>'America/Los_Angeles',
        'MX'=>'America/Los_Angeles',
        'BR'=>'America/Sao_Paulo',
        'GB'=>'Europe/London',//UK
        'DE'=>'	Europe/Paris',
        'FR'=>'	Europe/Paris',
        'ES'=>'	Europe/Paris',
        'IT'=>'	Europe/Paris',
        'NL'=>'	Europe/Paris',
        'SE'=>'Europe/Stockholm',
        'JP'=>'Asia/Tokyo',
        'AU'=>'Australia/Sydney',
        'AE'=>'Asia/Dubai',
        'SG'=>'Asia/Singapore',
    ];

    /** @var array sp时区 */
    public static $spTimeAreaSet = [
        'CA'=>'America/Los_Angeles',
        'US'=>'America/Los_Angeles',
        'MX'=>'CST',
        'BR'=>'America/Sao_Paulo',

        'ES'=>'Europe/Madrid',
        'GB'=>'Europe/London',
        'FR'=>'Europe/Paris',
        'NL'=>'Europe/Amsterdam',
        'DE'=>'Europe/Berlin',
        'IT'=>'Europe/Rome',
        'SE'=>'Europe/Stockholm',
        'TR'=>'Etc/GMT-3',
        'AE'=>'Asia/Dubai',
        'IN'=>'Asia/Kolkata',
        'PL'=>'Europe/Warsaw',
        'SA'=>'Asia/Riyadh',
        'BE'=>'Europe/Brussels',
        'CZ'=>'Europe/Prague',
        'SK'=>'Europe/Bratislava',

        'SG'=>'Asia/Singapore',
        'AU'=>'Australia/Sydney',
        'JP'=>'Asia/Tokyo',
    ];


    /**
     * 对应时区转北京时间
     * User: lir 2021/12/1 16:48
     * @param $date
     * @param string $country_code
     * @return float|int|string
     */
    public static function siteDateToBeijingStamp($date,$country_code,$type = 'ad')
    {
        $zone = '';
        if($type == 'ad'){
            $zone = self::$adTimeAreaSet[$country_code]??'';
        }
        if($type == 'sp'){
            $zone = self::$spTimeAreaSet[$country_code]??'';
        }
        if(!$zone){
            throw new \Exception('时区未配置');
        }
        $tz = CarbonTimeZone::create($zone);
        $date = Carbon::parse($date, $tz);
        $res = $date->timestamp;
        return $res;
    }

    /**
     * 北京时间转对应时区
     * User: lir 2021/12/1 16:48
     * @param $date
     * @param string $zone
     * @return float|int|string
     */
    public static function beiJingDateToSiteStamp($beijingdate,$country_code,$type = 'ad')
    {
        $beijingstamp = strtotime($beijingdate);
        $siteStamp = self::siteDateToBeijingStamp($beijingdate,$country_code,$type);
        $res = $beijingstamp - ($siteStamp - $beijingstamp);
        $siteDate = date('Y-m-d H:i:s',$res);
        return strtotime($siteDate);
    }

    public static function getSpTimeZoneText($country_code)
    {
        return self::$spTimeAreaSet[$country_code]??'';
    }

    /**
     * utc时间转对应时区
     * User: lir 2021/12/1 16:48
     * @param $date
     * @param string $zone
     * @return float|int|string
     */
    public static function utcDateToSiteStamp($utcdate,$country_code,$type = 'ad')
    {
        return self::beiJingDateToSiteStamp(date('Y-m-d H:i:s',strtotime($utcdate)),$country_code,$type);
    }
}