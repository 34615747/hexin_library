<?php
namespace Hexin\Library\Helpers;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 19:45
 */
class RequestHelper
{
    /**
     * curl请求
     * @param $url 请求地址
     * @param $params post参数
     * @param bool $is_post 是否post
     * @param int $time_out 超时时间
     * @param $header Header头
     * @return mixed
     */
    public static function curlRequest($url, $params, $is_post = false, $time_out = 10, $header)
    {
        $str_cookie = isset($ext_params['str_cookie']) ? $ext_params['str_cookie'] : '';
        $ch = curl_init();//初始化curl
        $method = $is_post ? "POST" : "GET";
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置是否返回response header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        //当需要通过curl_getinfo来获取发出请求的header信息时，该选项需要设置为true
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $time_out);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $time_out);
        curl_setopt($ch, CURLOPT_POST, $is_post);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        if (1 == strpos('$'.$url, "https://"))
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        if ($is_post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        if ($str_cookie) {
            curl_setopt($ch, CURLOPT_COOKIE, $str_cookie);
        }
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $response = curl_exec($ch);
//        $headerInfo = curl_getinfo($ch); //获得header信息
        curl_close($ch);
        $data = json_decode($response,true);
        return $data;
    }

}