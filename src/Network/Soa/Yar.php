<?php
namespace Hexin\Library\Network\Soa;

/**
 * Class Yar(作为客户端)
 */
class Yar extends SoaService
{
    /**
     * @var object yar对象
     */
    private $yarClient;

    /**
     * @var string 服务名称
     */
    private $service;

    /**
     * @var string url地址
     */
    private $url;

    /**
     * @var array  重试次数
     */
    public $retryNum;

    /**
     * 构造函数
     *
     * @param $baseUrl      string  基本的url
     * @param $service      string  调用的服务
     * @param $retryNum     int     重试次数
     */
    public function __construct($baseUrl, $service, $retryNum)
    {
        $this->service = $service;

        if (!is_int($retryNum) || $retryNum < 0) {
            $retryNum = 3;
        } elseif ($retryNum > 10) {
            $retryNum = 10;
        }
        $this->retryNum  = $retryNum;
        $this->url       = $baseUrl . 'getSoaInstance';
        $this->yarClient = new \Yar_Client($this->url);
        $this->yarClient->SetOpt(YAR_OPT_CONNECT_TIMEOUT, 30000);
        $this->yarClient->SetOpt(YAR_OPT_TIMEOUT, 0);
    }

    /**
     * 魔术方法，用于远程方法调用
     *
     * @param $method string 调用的方法名
     * @param $params array  调用的参数
     *
     * @return mixed 远程方法返回的值
     */
    public function __call($method, $params)
    {
        $result      = '';
        $this->error = null;
        $retry_sum = 3;//重试次数
        for ($i = 1; $i <= $this->retryNum; $i++) {
            try {
                $result      = $this->yarClient->doYarMethod($this->service, $method, $params);
                $isException = false;
            } catch (\Exception $e) {
                if(strrpos($e->getMessage(),'Timeout was reached') !== false && $this->retryNum<=$retry_sum){
                    $this->retryNum++;
                    sleep(1);
                    continue;
                }
                $isException = true;
                if ($i < $this->retryNum) {
                    continue;
                } else {
                    throw new \Exception('soa调用异常,'.$e->getMessage(), $e->getCode());
//                    E('soa调用异常,异常描述：'.$e->getMessage());
                }
            }

            if (!$isException) {
                break;
            }
        }

        //假如出现错误则进行内部记录
        if (is_array($result) && isset($result['code']) && $result['code'] > 0) {
            $this->error = array(
                'code' => $result['code'],
                'msg'  => $result['msg'],
            );
        }

        return $result;
    }


}

