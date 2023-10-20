<?php
namespace Hexin\Library\Network\Soa;

class SoaClient
{
    private static $clientList = array();

    public static function config()
    {
        return [
            'aliexpress_system' => '',
            'alibaba_system' => '',
            'hx.jst.cc'=>'',
        ];
    }
    /**
     * 获取相关yar客户端
     *
     * @param $server       string      要调用的SOA服务端名称
     * @param $service      string      要调用的SOA服务
     * @param $retryNum     int         重试次数
     *
     * @return object      相关yar客户端
     *
     * config配置需要增加相关地址
     *   'soa_client' => array(
     *        'erp_system' => 'http://192.168.66.188:81/yar/soa/"
     *   )
     *
     *  调用方法为
     *  $soa    = SoaClient::getSoa('erp_system', 'Order');
     *  $result = $soa->getOrderList($data);
     *
     */
    public static function getSoa($server, $service, $retryNum = 1)
    {
        if (isset(self::$clientList[$server][$service]) && self::$clientList[$server][$service] != null) {
            $returnObject           = self::$clientList[$server][$service];
            $returnObject->retryNum = $retryNum;

            return $returnObject;
        }

        //soa访问的地址
        $config = config('soaClient');
        if(!isset($config)){
            $config = self::config();
        }
        $baseUrl = $config[$server];


        //实例化对象
        self::$clientList[$server][$service] = new Yar($baseUrl, $service, $retryNum);

        return self::$clientList[$server][$service];
    }



}

