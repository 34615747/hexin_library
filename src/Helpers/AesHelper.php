<?php
namespace Hexin\Library\Helpers;

class AesHelper
{
    /**
     * 解密字符串
     * @param $key 密钥须是16位
     * @param $str 内容
     * @return false|string
     */
    public  function decode($key,$str)
    {
        return openssl_decrypt(base64_decode($str),"AES-128-ECB",$key,OPENSSL_RAW_DATA);
    }

    /**
     * 加密字符串
     * @param $key 密钥须是16位
     * @param $str 内容
     * @return string
     */
    public  function encode($key,$str)
    {
        return base64_encode(openssl_encrypt($str,"AES-128-ECB",$key,OPENSSL_RAW_DATA));
    }

}
