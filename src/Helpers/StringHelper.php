<?php
namespace Hexin\Library\Helpers;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 19:45
 */
class StringHelper
{
    /**
     * 提取数字
     * @param $str
     * @return string
     */
    public static function getNum($str)
    {
        if(!preg_match('/[0-9]+/', $str,$arr)){
            return '';
        }
        return $arr[0];
    }

    /**
     * 字符串循环连接
     * $add + $add + $add
     * User: lir 2020/4/17 10:03
     * @param $str
     * @param string $symbol
     * @return string
     */
    public static function forJoin($str,$add,$symbol='+')
    {
        return $str=='' ? $add : $str.$symbol.$add;
    }

    /**
     * 高精度相比较
     * $left==$right 返回 0 | $left<$right 返回 -1 | $left>$right 返回 1
     * @param $left
     * @param $right
     * @param int $scale
     * @return int
     */
    public static function bccomp($left,$right,$scale=3){
        return bccomp($left,$right, $scale);
    }

    /**
     * 获取网络图片的格式
     * @param $imgurl
     * @return string
     */
    public static function imgExtension($imgurl)
    {
        $str = strrchr($imgurl,'.');
        if($str){
            return substr($str,1);
        }
        return '';
    }

    /**
     * 字符串替换
     * 'CN￥136.87' 转136.87
     * User: lir 2020/5/5 15:06
     * @param $str
     * @return mixed
     */
    public static function CNstrReplace($str)
    {
        $regexp = '/(\d+)\.(\d+)/is';
        preg_match_all($regexp,$str,$arr);
        return $arr[0][0]??'';
    }

    /**
     * 判断字符串是否全是中文和空格
     * @param $str
     * @return bool
     */
    public static function isAllChinese($str){
        if(preg_match('/^[\x7f-\xff+\s]+$/', $str)){
            return true;//全是中文
        }else{
            return false;//不全是中文
        }
    }

    /**
     * 判断字符串是否含有中文
     * @param $str
     * @return bool
     */
    public static function hasChinese($str){
        if(preg_match('/[\x7f-\xff]/', $str)){
            return true; //含有中文
        }else{
            return false;//没有中文;
        }
    }

    /**
     * 四舍五入
     * @param $num
     * @param int $scale
     * @return float
     */
    public static function round($num,$scale=2)
    {
        return round($num,$scale);
    }

    /**
     * 截取指定字符之前的字符串
     * User: lir 2020/9/7 19:47
     * @param $str
     * @param $flag
     * @return bool|string
     */
    public static function shearBefore($str,$flag)
    {
        $strlen = strlen($str);
        $tp = strpos($str,$flag);
        if($tp!==false){
            $str = substr($str,-$strlen,$tp);
        }
        return $str;
    }

    /**
     * 截取指定两个字符之间的字符串
     * User: lir 2020/11/23 14:06
     * @param $begin
     * @param $end
     * @param $str
     * @return string
     */
    public static function cut($begin,$end,$str)
    {
        $b = mb_strpos($str,$begin) + mb_strlen($begin);
        $e = mb_strpos($str,$end) - $b;
        return mb_substr($str,$b,$e);
    }

    /**
     * 空格/回车/换行替换
     * User: lir 2020/12/9 15:37
     * @param $str
     * @return mixed
     */
    public static function trimStr($str,$replace='')
    {
        $str = trim($str);
        $before=[/*" ",*/"\t","\n","\r"];
        $after=[$replace,$replace,$replace,$replace];
        $str = str_replace($before,$after,$str);
        $str = str_replace(chr(194).chr(160), $replace, $str);
        return preg_replace('/\xC2\xA0/is', $replace, $str);
    }

    /**
     * 过滤redis key
     * User: lir 2021/11/9 11:23
     * @param $method
     * @param $arr
     * @return int|mixed|string
     */
    public static function filterRedisKey($method,$arr)
    {
        $str = $arr;
        if(is_array($arr)){
            $str = json_encode($arr);
        }
        $key = $method.':'.md5($str);
        $key = str_replace('\\','_',$key);
        return $key;
    }

    /**
     * 获取网络图片url的文件名，123.jpg
     * User: lir 2022/7/13 11:07
     * @param $img_url
     * @return bool|string
     */
    public static function getImageUrlName($img_url)
    {
        if(!$img_url){
            return '';
        }
        $start = strrpos($img_url,'/')+1;
        $file_name = substr($img_url,$start);
        return $file_name;
    }


    /**
     * 金额补2个0
     * User: lir 2022/10/14 12:06
     * @param $str
     * @return bool
     */
    public static function sprintf2($num)
    {
        return  sprintf("%01.2f",$num);
    }
    /**
     * 判断字符串中是否有中文
     * User: lir 2022/10/14 12:06
     * @param $str
     * @return bool
     */
    public static function isCNCharacters($str)
    {
        if (preg_match("/[\x7f-\xff]/",$str)){
            return true;
        }
        return false;
    }

    /**
     * 获得base64图片
     * @param $url
     * @return string
     * @throws \Exception
     */
    public static function getBase64($url)
    {
        if (!$url){
            return '';
        }
        if (!preg_match("/^(http:\/\/|https:\/\/).*$/",$url)) {
            return '';
        }
        $base64Code = '';
        try {
            $base64Code = base64_encode(file_get_contents($url));
        }catch (\Throwable $exception){
            throw new \Exception('无法获取图片信息:'.$url);
        }

        if (!$base64Code){
            return '';
        }
        $extension = pathinfo($url,PATHINFO_EXTENSION);
        return "data:image/{$extension};base64," . $base64Code;
    }

    /**
     * 是否含有日期 Y-m-d
     * @return bool
     */
    public static function isContainsDate($string)
    {
        $pattern = '/\b\d{4}-\d{2}-\d{2}\b/';
        if (preg_match($pattern, $string)) {
            return true;
        }
        return false;
    }

    /**
     * 获取金额的小数位数
     * @param $number
     * @return int
     */
    public static function getDecimalPlaces($number)
    {
        $number = (string)$number; // 确保是字符串
        $dotPos = strpos($number, '.');
        if ($dotPos === false) {
            return 0; // 没有小数点
        }
        return strlen($number) - $dotPos - 1;
    }

}