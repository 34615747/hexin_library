<?php
namespace Hexin\Library\Network\Soa;

class SoaService
{
    public $error;

    public function hasError()
    {
        return !empty($this->error);
    }

    public function flushError()
    {
        $this->error = null;
    }

    public function getErrorMsg()
    {
        return $this->error['msg'];
    }

    public function getErrorCode()
    {
        return $this->error['code'];
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($msg = '缺少必要的参数', $code = 1000)
    {
        $this->error = [
            'msg'  => $msg,
            'code' => $code
        ];

        return $this->error;
    }

}

