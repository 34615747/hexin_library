<?php

namespace Hexin\Library\Lib\Feishu;

use GuzzleHttp\Client;
use Hexin\Library\Helpers\ApiException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * 飞书 OA 审批客户端
 * 文档: https://open.feishu.cn/document/server-docs/approval-v4/
 *
 * 使用示例:
 * $client = new FeishuOAClient([
 *     'api_host' => config('feishu.api_host'),
 *     'token_provider' => function () {
 *         $res = rpcRequest('hexin_site_feishu_get_tenant_access_token', []);
 *         return $res['token'] ?? null;
 *     },
 * ]);
 *
 * $result = $client->submitApproval([
 *     'approval_code' => 'xxx',
 *     'user_id'       => 'feishu_user_id',
 *     'form'          => $formComponents, // 数组或 JSON 字符串
 * ]);
 */
class FeishuOAClient
{
    /** @var string */
    protected $apiHost;

    /** @var string */
    protected $appId;

    /** @var string */
    protected $appSecret;

    /** @var callable|null */
    protected $tokenProvider;

    /** @var string */
    protected $tokenCacheKey;

    /** @var int */
    protected $tokenCacheExpire;

    /** @var string */
    protected $encryptKey;

    /** @var string */
    protected $verificationToken;

    public function __construct(array $config = [])
    {
        $this->apiHost = $config['api_host'] ?? 'https://open.feishu.cn';
        $this->appId = $config['app_id'] ?? '';
        $this->appSecret = $config['app_secret'] ?? '';
        $this->tokenProvider = $config['token_provider'] ?? null;
        $this->tokenCacheKey = $config['token_cache_key'] ?? 'feishu_tenant_access_token';
        $this->tokenCacheExpire = (int) ($config['token_cache_expire'] ?? 7000);
        $this->encryptKey = $config['encrypt_key'] ?? '';
        $this->verificationToken = $config['verification_token'] ?? '';
    }

    /**
     * 从 Laravel 配置创建实例
     *
     * @param string $configKey 配置键名，默认 feishu
     * @param callable|null $tokenProvider 自定义 token 获取方式，不传则尝试 app_id/app_secret 直连
     */
    public static function fromConfig($configKey = 'feishu', callable $tokenProvider = null)
    {
        $config = config($configKey, []);

        return new self([
            'api_host'            => $config['api_host'] ?? 'https://open.feishu.cn',
            'app_id'              => $config['app_id'] ?? '',
            'app_secret'          => $config['app_secret'] ?? '',
            'token_provider'      => $tokenProvider,
            'token_cache_key'     => $config['token_cache_key'] ?? 'feishu_tenant_access_token',
            'token_cache_expire'  => $config['token_cache_expire'] ?? 7000,
            'encrypt_key'         => $config['encrypt_key'] ?? '',
            'verification_token'  => $config['verification_token'] ?? '',
        ]);
    }

    /**
     * 获取租户访问令牌 tenant_access_token
     * 文档: https://open.feishu.cn/document/server-docs/authentication-management/access-token/tenant_access_token_internal
     *
     * @return string
     * @throws ApiException
     */
    public function getTenantAccessToken()
    {
        if (is_callable($this->tokenProvider)) {
            $token = call_user_func($this->tokenProvider);
            if (empty($token)) {
                throw new ApiException([0, '获取飞书 tenant_access_token 失败']);
            }
            return $token;
        }

        $cachedToken = Cache::get($this->tokenCacheKey);
        if (!empty($cachedToken)) {
            return $cachedToken;
        }

        if (empty($this->appId) || empty($this->appSecret)) {
            throw new ApiException([0, '请配置 token_provider 或 app_id/app_secret']);
        }

        try {
            $client = new Client();
            $response = $client->post("{$this->apiHost}/open-apis/auth/v3/tenant_access_token/internal", [
                'headers' => ['Content-Type' => 'application/json'],
                'json'    => [
                    'app_id'     => $this->appId,
                    'app_secret' => $this->appSecret,
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            if (($result['code'] ?? -1) !== 0 || empty($result['tenant_access_token'])) {
                throw new ApiException([0, $result['msg'] ?? '获取飞书 tenant_access_token 失败']);
            }

            Cache::put($this->tokenCacheKey, $result['tenant_access_token'], $this->tokenCacheExpire);

            return $result['tenant_access_token'];
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException([0, $e->getMessage()]);
        }
    }

    /**
     * 提交 OA 审批
     *
     * @param array $params
     *   - approval_code: string 审批定义 Code（必填）
     *   - user_id: string 发起人用户 ID（必填）
     *   - form: array|string 表单组件值，数组会自动 JSON 编码（必填）
     *   - uuid: string 审批实例 UUID（选填）
     * @return array|null
     * @throws ApiException
     */
    public function submitApproval(array $params)
    {
        if (empty($params['approval_code'])) {
            throw new ApiException([0, '审批定义 approval_code 不能为空']);
        }
        if (empty($params['user_id'])) {
            throw new ApiException([0, '发起人 user_id 不能为空']);
        }
        if (!isset($params['form']) || $params['form'] === '' || $params['form'] === []) {
            throw new ApiException([0, '审批表单 form 不能为空']);
        }

        if (is_array($params['form'])) {
            $params['form'] = json_encode($params['form'], JSON_UNESCAPED_UNICODE);
        }

        return $this->createApprovalInstance($params);
    }

    /**
     * 获取指定审批定义
     * 文档: https://open.feishu.cn/document/server-docs/approval-v4/approval/get
     *
     * @param string $approvalCode
     * @param string $locale
     * @return array|null
     * @throws ApiException
     */
    public function getApprovalDefinition($approvalCode, $locale = 'zh-CN')
    {
        $result = $this->request('GET', "/open-apis/approval/v4/approvals/{$approvalCode}", [
            'query' => ['locale' => $locale],
        ]);

        if ($result === null) {
            Log::error('飞书获取审批定义失败', [
                'approval_code' => $approvalCode,
                'locale'        => $locale,
            ]);
        }

        return $result;
    }

    /**
     * 创建审批实例
     * 文档: https://open.feishu.cn/document/server-docs/approval-v4/instance/create
     *
     * @param array $params
     * @return array|null
     * @throws ApiException
     */
    public function createApprovalInstance(array $params)
    {
        try {
            $result = $this->requestRaw('POST', '/open-apis/approval/v4/instances', [
                'json' => $params,
            ]);

            if (($result['code'] ?? -1) === 0) {
                return $result['data'] ?? [];
            }

            Log::error('飞书创建审批实例失败', [
                'params'   => $params,
                'response' => $result,
            ]);

            return null;
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';
            $result = json_decode($responseBody, true);
            $message = $result['msg'] ?? $e->getMessage();

            Log::error('飞书创建审批实例异常', [
                'params'   => $params,
                'response' => $result,
                'error'    => $e->getMessage(),
            ]);

            throw new ApiException([0, $message]);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException([0, $e->getMessage()]);
        }
    }

    /**
     * 获取单个审批实例详情
     * 文档: https://open.feishu.cn/document/server-docs/approval-v4/instance/get
     *
     * @param string $instanceCode
     * @return array|null
     * @throws ApiException
     */
    public function getApprovalDetails($instanceCode)
    {
        $result = $this->request('GET', "/open-apis/approval/v4/instances/{$instanceCode}");

        if ($result === null) {
            Log::error('飞书获取审批实例详情失败', [
                'instance_code' => $instanceCode,
            ]);
        }

        return $result;
    }

    /**
     * 上传审批附件
     * 文档: https://open.feishu.cn/document/server-docs/approval-v4/file/upload-files
     *
     * @param string $filePath
     * @param string $fileName
     * @return string|null 文件 ID
     * @throws ApiException
     */
    public function uploadFile($filePath, $fileName)
    {
        $token = $this->getTenantAccessToken();

        try {
            $client = new Client();
            $response = $client->post("{$this->apiHost}/open-apis/approval/v4/files/upload", [
                'headers'   => ['Authorization' => "Bearer {$token}"],
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => file_get_contents($filePath),
                        'filename' => $fileName,
                    ],
                    [
                        'name'     => 'name',
                        'contents' => $fileName,
                    ],
                    [
                        'name'     => 'type',
                        'contents' => 'attachment',
                    ],
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (($result['code'] ?? -1) === 0 && !empty($result['data']['urls_detail'][0]['code'])) {
                return $result['data']['urls_detail'][0]['code'];
            }

            throw new ApiException([0, '飞书上传文件失败']);
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException([0, $e->getMessage()]);
        }
    }

    /**
     * 创建审批实例评论
     * 文档: https://open.feishu.cn/document/server-docs/approval-v4/instance-comment/create
     *
     * @param string $instanceCode
     * @param string $content
     * @param array $fileIds
     * @return bool
     * @throws ApiException
     */
    public function addComment($instanceCode, $content, array $fileIds = [])
    {
        $params = ['content' => $content];

        if (!empty($fileIds)) {
            $params['files'] = array_map(function ($fileId) {
                return ['file_id' => $fileId];
            }, $fileIds);
        }

        $result = $this->requestRaw('POST', "/open-apis/approval/v4/instances/{$instanceCode}/comments", [
            'json' => $params,
        ]);

        if (($result['code'] ?? -1) === 0) {
            return true;
        }

        Log::error('飞书添加审批评论失败', [
            'instance_code' => $instanceCode,
            'content'       => $content,
            'file_ids'      => $fileIds,
            'response'      => $result,
        ]);

        return false;
    }

    /**
     * 批量获取审批实例 ID
     * 文档: https://open.feishu.cn/document/server-docs/approval-v4/instance/list
     *
     * @param array $params
     * @return array|null
     * @throws ApiException
     */
    public function listApprovalInstances(array $params)
    {
        $result = $this->request('GET', '/open-apis/approval/v4/instances', [
            'query' => $params,
        ]);

        if ($result === null) {
            Log::error('飞书批量获取审批实例失败', ['params' => $params]);
        }

        return $result;
    }

    /**
     * 查询实例列表（高级搜索）
     * 文档: https://open.feishu.cn/document/server-docs/approval-v4/approval-search/query-2
     *
     * @param array $params
     * @return array|null
     * @throws ApiException
     */
    public function searchApprovalInstances(array $params)
    {
        $result = $this->request('POST', '/open-apis/approval/v4/instances/query', [
            'json' => $params,
        ]);

        if ($result === null) {
            Log::error('飞书查询实例列表失败', ['params' => $params]);
        }

        return $result;
    }

    /**
     * 撤回审批实例
     * 文档: https://open.feishu.cn/document/server-docs/approval-v4/instance/cancel
     *
     * @param string $approvalCode
     * @param string $instanceCode
     * @param string $userId
     * @param string $userIdType
     * @return array|null
     * @throws ApiException
     */
    public function cancelInstance($approvalCode, $instanceCode, $userId, $userIdType = 'user_id')
    {
        try {
            $result = $this->requestRaw(
                'POST',
                '/open-apis/approval/v4/instances/cancel?user_id_type=' . $userIdType,
                [
                    'json' => [
                        'approval_code' => $approvalCode,
                        'instance_code' => $instanceCode,
                        'user_id'       => $userId,
                    ],
                ]
            );

            if (($result['code'] ?? -1) === 0) {
                Log::info('飞书撤回审批实例成功', [
                    'approval_code' => $approvalCode,
                    'instance_code' => $instanceCode,
                    'user_id'       => $userId,
                ]);
                return $result;
            }

            throw new ApiException([0, '飞书撤回审批实例失败' . ($result['msg'] ?? '')]);
        } catch (ApiException $e) {
            Log::error('飞书撤回审批实例异常', [
                'approval_code' => $approvalCode,
                'instance_code' => $instanceCode,
                'user_id'       => $userId,
                'error'         => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 获取单个用户信息
     * 文档: https://open.feishu.cn/document/server-docs/contact-v3/user/get
     *
     * @param string $userId
     * @param string $userIdType
     * @param string $departmentIdType
     * @return array|null
     * @throws ApiException
     */
    public function getUserInfo($userId, $userIdType = 'user_id', $departmentIdType = 'open_department_id')
    {
        return $this->request('GET', "/open-apis/contact/v3/users/{$userId}", [
            'query' => [
                'user_id_type'       => $userIdType,
                'department_id_type'   => $departmentIdType,
            ],
        ], true);
    }

    /**
     * 获取用户名称（带缓存）
     *
     * @param string $userId
     * @param string $userIdType
     * @return string|null
     */
    public function getUserName($userId, $userIdType = 'user_id')
    {
        $cacheKey = "feishu:user:name:{$userIdType}:{$userId}";
        $userName = Cache::get($cacheKey);
        if ($userName !== null) {
            return $userName;
        }

        try {
            $userInfo = $this->getUserInfo($userId, $userIdType);
            if ($userInfo && isset($userInfo['user']['name'])) {
                $userName = $userInfo['user']['name'];
                Cache::put($cacheKey, $userName, 1800);
                return $userName;
            }
        } catch (\Exception $e) {
            Log::error('飞书获取用户名称异常', [
                'user_id'      => $userId,
                'user_id_type' => $userIdType,
                'error'        => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * 构建简单审批表单组件值
     *
     * @param array $formData [widget_id => value]
     * @return array
     */
    public function buildFormComponentValues(array $formData)
    {
        $componentValues = [];

        foreach ($formData as $id => $value) {
            $componentValues[] = [
                'id'    => $id,
                'value' => is_array($value) ? json_encode($value) : (string) $value,
            ];
        }

        return $componentValues;
    }

    /**
     * 验证事件订阅签名
     *
     * @param array $eventData
     * @return bool
     */
    public function verifyEventSignature(array $eventData)
    {
        if (empty($this->verificationToken) || empty($eventData['token'])) {
            return false;
        }

        return $eventData['token'] === $this->verificationToken;
    }

    /**
     * 解密事件数据
     *
     * @param string $encryptedData
     * @return array|null
     * @throws ApiException
     */
    public function decryptEventData($encryptedData)
    {
        if (empty($this->encryptKey)) {
            return null;
        }

        try {
            $key = base64_decode($this->encryptKey);
            $decrypted = openssl_decrypt(
                base64_decode($encryptedData),
                'AES-256-CBC',
                $key,
                OPENSSL_RAW_DATA,
                substr($key, 0, 16)
            );

            if ($decrypted === false) {
                Log::error('飞书事件数据解密失败');
                return null;
            }

            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            throw new ApiException([0, $e->getMessage()]);
        }
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param bool $throwOnError 失败时是否抛异常
     * @return array|null
     * @throws ApiException
     */
    protected function request($method, $uri, array $options = [], $throwOnError = false)
    {
        $result = $this->requestRaw($method, $uri, $options);

        if (($result['code'] ?? -1) === 0) {
            return $result['data'] ?? [];
        }

        if ($throwOnError) {
            throw new ApiException([0, $result['msg'] ?? '飞书接口请求失败']);
        }

        return null;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array
     * @throws ApiException
     */
    protected function requestRaw($method, $uri, array $options = [])
    {
        $token = $this->getTenantAccessToken();

        $options['headers'] = array_merge($options['headers'] ?? [], [
            'Authorization' => "Bearer {$token}",
        ]);

        if (isset($options['json'])) {
            $options['headers']['Content-Type'] = 'application/json';
        }

        try {
            $client = new Client();
            $response = $client->request($method, $this->apiHost . $uri, $options);

            return json_decode($response->getBody()->getContents(), true) ?: [];
        } catch (ApiException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new ApiException([0, $e->getMessage()]);
        }
    }
}
