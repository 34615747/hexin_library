<?php
namespace Hexin\Library\Helpers;
use Carbon\CarbonTimeZone;
use Illuminate\Support\Carbon;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 19:45
 */
class DateHelper
{
    /**
     * 格式化时间
     * @param null $time
     * @param string $format
     * @return false|string
     */
    public static function formateIso8601Date($time = null, $format = 'Y-m-d\TH:i:s.000\Z')
    {
        $time = empty($time) ? time() : $time;
        return gmdate($format, $time);
    }

    /**
     * Cdiscount时间格式化
     * @param $time
     * @return false|string
     */
    public static function formateIso8601DateCdiscount($time)
    {
        return self::formateIso8601Date($time, 'Y-m-d\TH:i:s\Z');
    }

    /**
     * Wish时间格式化
     * @param $time
     * @return false|string
     */
    public static function formateIso8601DateWish($time)
    {
        return self::formateIso8601Date($time, 'Y-m-d\TH:i:s\Z');
    }

    /**
     * Vova时间格式化
     * @param $time
     * @return false|string
     */
    public static function formateIso8601DateVova($time)
    {
        return self::formateIso8601Date($time, 'Y-m-d\ H:i:s');
    }

    /**
     * Hxcart时间格式化
     * @param $time
     * @return false|string
     */
    public static function formateIso8601DateHxcart($time)
    {
        return self::formateIso8601Date($time, 'Y-m-d H:i:s');
    }

    /**
     * Amazon时间格式化
     * @param $time
     * @return false|string
     */
    public static function formateIso8601DateAmazon($time)
    {
        return self::formateIso8601Date($time, 'Y-m-d\TH:i:s\Z');
    }
    /**
     * Shopify时间格式化
     * @param $time
     * @return false|string
     */
    public static function formateIso8601DateShopify($time)
    {
        return self::formateIso8601Date($time, 'Y-m-d\TH:i:s\Z');
    }
    /**
     * Ebay时间格式化
     * @param $time
     * @return false|string
     */
    public static function formateIso8601DateEbay($time)
    {
        return self::formateIso8601Date($time, 'Y-m-d\TH:i:s\Z');
    }
    /**
     * Daraz时间格式化
     * @param $time
     * @return false|string
     */
    public static function formateIso8601DateDaraz($time)
    {
        return self::formateIso8601Date($time, 'Y-m-d\TH:i:s.000\Z');
    }
    /**
     * Walmart时间格式化
     * @param $time
     * @return false|string
     */
    public static function formateIso8601DateWalmart($time)
    {
        return self::formateIso8601Date($time, 'Y-m-d\TH:i:s\Z');
    }
    /**
     * Joom时间格式化
     * @param $time
     * @return false|string
     */
    public static function formateIso8601DateJoom($time)
    {
        return self::formateIso8601Date($time, 'Y-m-d\TH:i:s\Z');
    }
    /**
     * 按天分隔时间段
     * @param $begin_time
     * @param int $day
     * @param string $end_time
     * @return array
     */
    public static function getScopeTimeByDay($begin_time, $day = 14,$end_time = '')
    {
        if(!$end_time){
            $end_time = time()-5*60;
        }
        $s = $day*86400;
        $diff_time   = $end_time - $begin_time;
        $yu = $diff_time%$s;
        $n = floor($diff_time/$s);
        if($n){
            $start_time = $begin_time;
            $end_time = 0;
            for($i=0;$i<$n;$i++){
                $end_time = ($start_time + $s);
                if($i==$n-1){
                    $end_time += $yu;
                }
                $array[] = [
                    'start_time'=>$start_time,
                    'end_time'=>$end_time,
                ];
                $start_time = $end_time+1;
            }
        }else{
            $array[] = [
                'start_time'=>$begin_time,
                'end_time'=>$end_time,
            ];
        }
        return $array;
    }

    /**
     * 几天之前的时间
     */
    public static function dayBefore($day = 80)
    {
        return strtotime("-{$day} days");
    }


    /**
     * 格式化日期
     * @param $timestamp
     * @return false|string
     */
    public static function formatDate($timestamp)
    {
        if(!$timestamp){
            return '';
        }
        return date('Y-m-d H:i:s',$timestamp);
    }

    /**
     * 获得太平洋时间
     * User: lir 2020/2/28 11:41
     * @param $timestamp
     * @return float|int
     */
    public static function getPacificTime($timestamp)
    {
        return $timestamp-16.5*3600;
    }

    /**
     * 获得太平洋时间并格式化
     */
    public static function getFormatPacificDate($timestamp)
    {
        if(is_string($timestamp)){
            return $timestamp;
        }
        return date('Y-m-d H:i:s',self::getPacificTime($timestamp));
    }

    /**
     * 结束时间，2019-12-12=》2019-12-12 23:59:59
     * @param $end_time
     * @return false|int
     */
    public static function endTime($end_time)
    {
        return strtotime(date('Y-m-d',strtotime($end_time)))+86400-1;
    }

    /**
     * 是否在31天内
     * @param $time
     * @return bool
     */
    public static function in30Day($time)
    {
        if($time > self::dayBefore(31)){
            return true;
        }
        return false;
    }

    /**
     * 结束时间
     * @param $date
     * @return false|string
     */
    public static function dateEnd($date)
    {
        return date('Y-m-d H:i:s',strtotime($date)+86400-1);
    }

    /**
     * 结束时间带T
     * @param $date
     * @return false|string
     */
    public static function dateEndT($date)
    {
        return date('Y-m-d\TH:i:s',strtotime($date)+86400-1);
    }

    /**
     * 结束时间带TZ
     * @param $date
     * @return false|string
     */
    public static function dateEndTZ($date)
    {
        return date('Y-m-d\TH:i:s.000\Z',strtotime($date)+86400-1);
    }

    /**
     * 时间带T
     * @param $date
     * @return false|string
     */
    public static function gmdateT($date)
    {
        return date('Y-m-d\TH:i:s',strtotime($date));
    }

    /**
     * 时间带TZ
     * @param $date
     * @return false|string
     */
    public static function gmdateTZ($date)
    {
        return date('Y-m-d\TH:i:s.000\Z',strtotime($date));
    }

    /**
     * 现在是否在指定时间段内
     * @return bool
     */
    public static function inDateG()
    {
        if(date('G')>=0 && date('G')<6){
            return true;
        }
        throw new \Exception('只能在0点到6点之间执行');
    }

    /**
     * 获得年月
     * User: lir 2020/4/14 14:07
     * @param $date
     * @return array
     */
    public static function getMD($date)
    {
        $date = date('Y-m',strtotime($date));
        return explode('-',$date);
    }

    /**
     * 和现在的差额天数
     * @param $complete_time
     * @return float|int
     */
    public static function poorNowDay($complete_time)
    {
        $day = 0;
        $poor = $complete_time - time();
        if($poor > 0){
            $day = ceil($poor/86400);
        }
        return $day;
    }

    /**
     * 剩余时间
     * User: lir 2020/5/23 13:27
     * @param $time_stamp
     * @return array
     */
    public static function restOfTime($time_stamp)
    {
        $data = [];
        $day = intval($time_stamp/86400);//天
        $data['day'] = $day;
        $hour = intval((($time_stamp/86400)-$day)*24);//小时
        $data['hour'] = $hour;
        $minute = intval( (((($time_stamp/86400)-$day)*24)-$hour)*60 );//分钟
        $data['minute'] = $minute;
        $second = intval(((((((($time_stamp/86400)-$day)*24)-$hour)*60)-$minute)*60));//秒
        $data['second'] = $second;
        return $data;
    }

    /**
     * 获得毫秒时间
     * User: lir 2020/7/15 11:23
     * @return float
     */
    public static function getMillisecond()
    {
        list($microsecond , $time) = explode(' ', microtime()); //' '中间是一个空格
        return (float)sprintf('%.0f',(floatval($microsecond)+floatval($time))*1000);
    }

    /**
     * 获取客户列表日期
     * @param $timestamp
     * @return string
     */
    public static function getSenderListDate($timestamp)
    {
        $year = date("Y");
        $nowYear = date("Y",$timestamp);
        if($year != $nowYear){
            return date("Y-m-d",$timestamp);
        }
        return date("m-d",$timestamp);
    }

    /**
     * 24小时差额，<0超过24小时
     * @param $timestamp
     * @return bool|int
     */
    public static function poor24($timestamp)
    {
        $t = $timestamp+86400-time();
        return $t;
    }

    /**
     * 毫秒转日期
     * @param int $msectime 带毫秒时间戳
     * @param bool $get_ms 是否带毫秒
     * @return mixed
     */
    public static function getMsecToMescdate($msectime, $get_ms = true)
    {
        $msectime = $msectime * 0.001;
        if(strstr($msectime,'.')){
            sprintf("%01.3f",$msectime);
            list($usec, $sec) = explode(".",$msectime);
            $sec = str_pad($sec,3,"0",STR_PAD_RIGHT);
        }else{
            $usec = $msectime;
            $sec = "000";
        }
        if($get_ms){
            $date = date("Y-m-d H:i:s.x", $usec);
        }else{
            $date = date("Y-m-d H:i:s", $usec);
        }
        return $mescdate = str_replace('x', $sec, $date);
    }

    /**
     * 获取指定日期所在月的第一天和最后一天
     * User: lir 2020/12/14 13:32
     * @param $date
     * @return array
     */
    public static function getTheMonth($date)
    {
        if(!is_int($date)){
            $date = strtotime($date);
        }
        $firstday = date("Y-m-01",$date);
        $lastday = date("Y-m-d",strtotime("$firstday +1 month -1 day"));
        return [$firstday,$lastday];
    }

    /**
     * 获取指定日期所在年的第一天和最后一天
     * @param $date
     * @return array
     */
    public static function getTheYear($date)
    {
        if(!is_int($date)){
            $date = strtotime($date);
        }
        $firstday = date("Y-01-01",$date);
        $lastday = date("Y-m-d",strtotime("$firstday +1 year -1 day"));
        return [$firstday, $lastday];
    }

    /**
     * 获取指定日期上个月的第一天和最后一天
     * User: lir 2020/12/14 13:32
     * @param $date
     * @return array
     */
    public static function getPurMonth($date)
    {
        $time=strtotime($date);
        $firstday=date('Y-m-01',strtotime(date('Y',$time).'-'.(date('m',$time)-1).'-01'));
        $lastday=date('Y-m-d',strtotime("$firstday +1 month -1 day"));
        return [$firstday,$lastday];
    }

    /**
     * 日期转时间戳，带时区
     * @param $date
     * @param $timezone
     * @param int $change 调整数
     * @param string $unit 调整单位
     * @return int
     * @throws \Exception
     */
    public static function getDateToTimestamp($date,$timezone,$change=0,$unit="")
    {
        $tz = CarbonTimeZone::create($timezone);
        $time = Carbon::parse($date, $tz);
        if (!empty($change) && !empty($unit)){
            $time->add($change,$unit);
        }
        return $time->timestamp;
    }

    /**
     * 时间戳转日期，带时区
     * @param $timestamp
     * @param $timezone
     * @return string
     */
    public static function getTimestampToDate($timestamp,$timezone)
    {
        return Carbon::createFromTimestamp($timestamp,$timezone)->format("Y-m-d H:i:s");
    }

    /**
     * 上周的第一天（星期一）
     * User: lir 2020/12/24 16:16
     * @return false|int
     */
    public static function getBeginLastWeek()
    {
        return mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
    }

    /**
     * 上周的最后一天（星期日）
     * User: lir 2020/12/24 16:16
     * @return false|int
     */
    public static function getEndLastweek()
    {
        return mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));

    }

    /**
     * 验证传入时间是否是今天
     * @param $timestamp
     * @return bool
     */
    public static function checkTimeIsToday($timestamp){
        $today = date('Y-m-d');
        if($timestamp < strtotime($today) || $timestamp > self::endTime($today)){
            return false;
        }
        return true;
    }

    /**
     * 获取上个月的年月
     * User: lir 2020/12/14 13:32
     * @param $date
     * @return array
     */
    public static function getPurYm()
    {
        $time=time();
        $ym=date('Y-m',strtotime(date('Y',$time).'-'.(date('m',$time)-1).'-01'));
        return $ym;
    }

    /**
     * 毫秒转秒
     * User: lir 2022/4/8 11:45
     */
    public static function msTos($ms)
    {
        if(!$ms){
            return 0;
        }
        return bcdiv($ms, 1000, 0);
    }

    /**
     * 是否是时间戳
     * User: lir 2022/7/20 18:02
     * @param $timestamp
     * @return bool
     */
    public static function isTimestamp($timestamp)
    {
        if(strtotime(date('Y-m-d H:i:s',$timestamp)) === $timestamp){
            return true;
        }
        return false;
    }

    /**
     * 日期范围
     * User: lir 2021/11/23 15:02
     * @param $time_type
     * @return array
     */
    public static function dateScope($time_type)
    {
        switch ($time_type){
            //今天
            case 'today':
                $start_time = strtotime(date('Y-m-d'));
                $end_time = time();
                break;
            //昨天
            case 'yesterday':
                $start_time = strtotime(date("Y-m-d",strtotime("-1 day")));
                $end_time = strtotime(date('Y-m-d'))-1;
                break;
            //最近7天
            case 'day_7':
                $start_time = strtotime(date("Y-m-d",strtotime("-7 day")));
                $end_time = time();
                break;
            //本周
            case 'weeks':
                $w=date('w');
                $start_time = strtotime(date('Y-m-d')."-".($w ? $w - 1 : 6).' days');
                $end_time = time();
                break;
            //上周
            case 'last_weeks':
                $start_time=mktime(0,0,0,date('m'),date('d')-date('w')+1-7,date('Y'));
                $end_time=mktime(23,59,59,date('m'),date('d')-date('w')+7-7,date('Y'));
                break;
            //最近30天
            case 'day_30':
                $start_time = strtotime(date("Y-m-d",strtotime("-30 day")));
                $end_time = time();
                break;
            //本月
            case 'month':
                $start_time = strtotime(date("Y-m-01"));
                $end_time = time();
                break;
            //最近1个月
            case 'month_1':
                $start_time = strtotime(date("Y-m-d",strtotime("-1 months")));
                $end_time = time();
                break;
            //最近2个月
            case 'month_2':
                $start_time = strtotime(date("Y-m-d",strtotime("-2 months")));
                $end_time = time();
                break;
            //最近3个月
            case 'month_3':
                $start_time = strtotime(date("Y-m-d",strtotime("-3 months")));
                $end_time = time();
                break;
            //最近6个月
            case 'month_6':
                $start_time = strtotime(date("Y-m-d",strtotime("-6 months")));
                $end_time = time();
                break;
            //最近9个月
            case 'month_9':
                $start_time = strtotime(date("Y-m-d",strtotime("-9 months")));
                $end_time = time();
                break;
            //上个月
            case 'last_month':
                $start_time = strtotime(date("Y-m-1 00:00:00",strtotime("last months")));
                $end_time = strtotime(date("Y-m"))-1;
                break;
            //本年至今
            case 'year':
                $start_time = strtotime(date("Y-01-01"));
                $end_time = time();
                break;
            //最近1年
            case 'year_1':
                $start_time = strtotime(date("Y-m-d",strtotime("-1 years")));
                $end_time = time();
                break;
            default:
                throw new \Exception('格式错误');
        }
        return [$start_time,$end_time];
    }

    /**
     * 判断是否是文本格式的日期
     * @param $date
     * @return bool
     */
    public static function isTextDate($date)
    {
        if(empty($date)) return false;
        return (strpos($date,"-") ||  strpos($date,"/"));
    }

    /** 导入时间错乱问题
     * @param $dateTime
     * @return false|string
     */
    public static function getImportDateTime($dateTime)
    {
        if (is_numeric($dateTime)) {
            $dates_timestamp = intval((($dateTime - 25569) * 3600 * 24)) - 8 * 3600;
            return date('Y-m-d H:i:s', $dates_timestamp);
        }
        return $dateTime;
    }
}
