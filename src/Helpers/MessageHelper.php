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
        $config = $config[$level];
        $res = true;
        switch ($msg_class){
            case 'dingding':
                $dingTalk = new DingDingMessage($config['webhook'],DingDingMessage::MSG_TYPE_TEXT,$config['secret']);
                $dingTalk->send([
                    'content' => $content,
                    'at'      => $config['mobiles'],
                    'keyword'=>$config['keyword']??''
                ]);
                break;
            case 'mail':
                $mail = new \Hexin\Library\Helpers\MailHelper($config['relay_host'],  $config['user'], $config['pass'], $config['smtp_port'], $config['auth'], $config['is_ssl']);
                $res = $mail->sendmail($params['to'], $params['from'], $params['subject'], $content);
                break;
        }
        return $res;
    }

	/**
	 * 发送markdown消息
	 * @param $title 标题
	 * @param $content 内容
	 * @param $msg_class 消息类型
	 * @param $level
	 * @return true|void
	 */
	public static function sendMarkdown($title, $content, $msg_class, $level = 'error')
	{
		$config = config('message_notice.' . $msg_class, []);
		if (!$config) {
			return;
		}
		$config = $config[$level];
		$res = true;
		switch ($msg_class) {
			case 'dingding':
				$dingTalk = new DingDingMessage($config['webhook'], DingDingMessage::MSG_TYPE_MARKDOWN, $config['secret']);
				$dingTalk->send([
					'content' => $content,
					'title'   => $title,
					'at'      => $config['mobiles']
				]);
				break;
		}
		return $res;
	}

}