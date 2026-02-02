<?php

namespace Hexin\Library\Lib\Feishu;

use GuzzleHttp\Client;
use Hexin\Library\Helpers\CommonHelper;
use Illuminate\Support\Facades\Log;

/**
 * 飞书自定义机器人消息通知
 * 文档: https://open.feishu.cn/document/client-docs/bot-v3/add-custom-bot
 */
class FeishuMessage
{
    const MSG_TYPE_TEXT = 'text';
    const MSG_TYPE_POST = 'post';       // 富文本，类似钉钉 markdown
    const MSG_TYPE_INTERACTIVE = 'interactive';  // 消息卡片，类似钉钉 link

    private $webhook;
    private $msg_type;
    private $secret;
    private $keyword;

    public function __construct($webhook, $msg_type = self::MSG_TYPE_TEXT, $secret = '')
    {
        $this->webhook = $webhook;
        $this->msg_type = $msg_type;
        $this->secret = $secret;
        if (CommonHelper::isProduction()) {
            $this->keyword = '【生产环境】';
        } else {
            $this->keyword = '【测试/预生产环境】';
        }
    }

    /**
     * 文本消息
     */
    public function text($data)
    {
        $content = !empty($data['content']) ? sprintf("%s\n%s", $this->keyword, $data['content']) : '';
        return [
            'msg_type' => 'text',
            'content'  => ['text' => $content],
        ];
    }

    /**
     * 富文本消息 (post)，类似钉钉 markdown
     */
    public function post($data)
    {
        $content = $data['content'] ?? '';
        $lines = array_filter(explode("\n", $content));
        if (empty($lines)) {
            $lines = [''];
        }
        $contentArr = array_map(function ($line) {
            return [['tag' => 'text', 'text' => $line]];
        }, $lines);

        return [
            'msg_type' => 'post',
            'content'  => [
                'post' => [
                    'zh_cn' => [
                        'title'   => $data['title'] ?? '',
                        'content' => $contentArr,
                    ],
                ],
            ],
        ];
    }

    /**
     * 消息卡片，类似钉钉 link
     */
    public function interactive($data)
    {
        $content = $data['content'] ?? '';
        $messageUrl = $data['message_url'] ?? '';

        $elements = [
            [
                'tag'  => 'div',
                'text' => [
                    'tag'     => 'lark_md',
                    'content' => $content,
                ],
            ],
        ];

        if (!empty($messageUrl)) {
            $elements[] = [
                'tag'     => 'action',
                'actions' => [
                    [
                        'tag'  => 'button',
                        'text' => ['tag' => 'plain_text', 'content' => '查看详情'],
                        'url'  => $messageUrl,
                        'type' => 'primary',
                    ],
                ],
            ];
        }

        return [
            'msg_type' => 'interactive',
            'card'     => [
                'header'   => [
                    'title'    => ['tag' => 'plain_text', 'content' => $data['title'] ?? '', 'emoji' => true],
                    'template' => 'blue',
                ],
                'elements' => $elements,
            ],
        ];
    }

    public function getParams($data)
    {
        switch ($this->msg_type) {
            case self::MSG_TYPE_TEXT:
                $params = $this->text($data);
                break;
            case self::MSG_TYPE_POST:
                $params = $this->post($data);
                break;
            case self::MSG_TYPE_INTERACTIVE:
                $params = $this->interactive($data);
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

        if ($this->secret) {
            $timestamp = (string) time();
            $params['timestamp'] = $timestamp;
            $params['sign'] = $this->sign($timestamp, $this->secret);
        }

        try {
            $client = new Client();
            $res = $client->request('POST', $this->webhook, [
                'json'   => $params,
                'verify' => false,
            ]);

            $result = json_decode($res->getBody()->getContents(), true);
            $code = $result['code'] ?? $result['StatusCode'] ?? 0;

            if ($code != 0) {
                Log::debug(
                    '飞书推送失败！',
                    [
                        'webhook' => $this->webhook,
                        'data'   => $params,
                        'message' => sprintf('错误码：%s，错误信息：%s', $code, $result['msg'] ?? $result['StatusMessage'] ?? '未知错误'),
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::debug(
                '飞书推送异常！',
                [
                    'webhook' => $this->webhook,
                    'data'   => $params,
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * 飞书签名：HMAC-SHA256(timestamp + "\n" + secret, secret) 后 Base64 编码
     * 与钉钉不同，飞书将 timestamp 和 sign 放入请求体，不拼接到 URL
     */
    public function sign($timestamp, $secret)
    {
        $stringToSign = $timestamp . "\n" . $secret;
        return base64_encode(hash_hmac('sha256', $stringToSign, $secret, true));
    }
}
