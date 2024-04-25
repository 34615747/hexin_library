<?php
namespace Hexin\Library\Helpers;
use Hexin\Library\Lib\DingTalk\DingDingMessage;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/22
 * Time: 19:45
 */
class MessageHelper
{
    /**
     * 发送消息
     * User: lir 2020/12/25 11:19
     * @return array
     */
    public static function sendText($content,$msg_class,$level='error',$params=[])
    {
        $config = config('message_notice.'.$msg_class,[]);
        if(!$config){
            return;
        }
        switch ($msg_class){
            case 'dingding':
                $config = $config[$level];
                $dingTalk = new DingDingMessage($config['webhook'],DingDingMessage::MSG_TYPE_TEXT,$config['secret']);
                $dingTalk->send([
                    'content' => $content,
                    'at'      => $config['mobiles']
                ]);
            break;
        }
    }



}