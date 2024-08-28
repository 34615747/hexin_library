<?php
namespace Hexin\Library\Helpers;
// 自定义异常类
class ApiException extends \Exception
{
    public function __construct($error)
    {
        $code = $error[0] ?? 0;
        $message = $error[1] ?? 'error';
        parent::__construct($message, $code);
    }
}
