<?php

namespace Hexin\Library\Lib\DingTalk;


use GuzzleHttp\Client;
use Hexin\Library\Helpers\CommonHelper;
use Illuminate\Support\Facades\Log;

class DingDingMessage
{
    const MSG_TYPE_TEXT = 'text';
    const MSG_TYPE_MARKDOWN = 'markdown';
    const MSG_TYPE_LINK = 'link';

    private $webhook;
    private $msg_type;
    private $keyword;

    public function __construct($webhook, $msg_type = self::MSG_TYPE_TEXT, $secret = '')
    {
        $sign = $secret ? $this->sign($secret) : '';
        $this->webhook = $webhook.$sign;
        $this->msg_type = $msg_type;
        if (CommonHelper::isProduction()) {
            $this->keyword = '【生产环境】';
        } else {
            $this->keyword = '【测试/预生产环境】';
        }

    }

    public function text($data)
    {
        $keyword = $this->keyword;
        if($data['keyword']??''){
            $keyword = $data['keyword'];
        }
        return [
            'msgtype' => 'text',
            'text'    => ['content' => !empty($data['content']) ? sprintf("%s\n%s", $keyword, $data['content']) : ''],
            'at'      => ['atMobiles' => $data['at'] ?? []],
        ];
    }

    public function markdown($data)
    {
        return [
            'msgtype'  => 'markdown',
            'markdown' => [
                'title' => $data['title'] ?? '',
                'text'  => $data['content'] ?? '',
            ],
            'at'       => ['atMobiles' => $data['at'] ?? []],
        ];
    }

    public function link($data)
    {
        return [
            'msgtype' => 'link',
            'link'    => [
                'title'      => $data['title'] ?? '',
                'text'       => $data['content'] ?? '',
                'messageUrl' => $data['message_url'] ?? '',
                'picUrl'     => $data['pic_url'] ?? '',
            ],
        ];
    }

    public function getParams($data)
    {
        switch ($this->msg_type) {
            case self::MSG_TYPE_TEXT:
                $params = $this->text($data);
                break;
            case self::MSG_TYPE_MARKDOWN:
                $params = $this->markdown($data);
                break;
            case self::MSG_TYPE_LINK:
                $params = $this->link($data);
                break;
            default:
                $params = $this->text($data);
                break;
        }
        return $params;
    }

    public function send($params)
    {
        $params = $this->getParams($params);
        try {

            $client = new Client();
            $res = $client->request('POST', $this->webhook, [
                'json'   => $params,
                'verify' => false,
            ]);

            $result = json_decode($res->getBody()->getContents(), true);

            if ($result['errcode'] != 0) {
                Log::debug(
                    '钉钉推送失败！',
                    [
                        'webhook' => $this->webhook,
                        'data'    => $params,
                        'message' => sprintf('错误码：%s，错误信息：%s', $result['errcode'], $result['errmsg'] ?? '未知错误'),
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::debug(
                '钉钉推送异常！',
                [
                    'webhook' => $this->webhook,
                    'data'    => $params,
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    public function sign($secret)
    {
        //将时间戳 timestamp 和密钥 secret 当做签名字符串，使用HmacSHA256算法计算签名，然后进行Base64 encode，最后再把签名参数再进行urlEncode，得到最终的签名（需要使用UTF-8字符集）。
        $timestamp = time() * 1000;
        $sign = hash_hmac('sha256', $timestamp . "\n" . $secret, $secret, true);
        $sign = base64_encode($sign);
        $sign = urlencode($sign);
        return "&timestamp=$timestamp&sign=$sign";
    }
}
