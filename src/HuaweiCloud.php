<?php

namespace HuaweiCloud;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use HuaweiCloud\Contracts\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use HuaweiCloud\Exceptions\HuaweiCloudException;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\BinaryOp\LogicalAnd;

class HuaweiCloud
{
    // 获取Token API的路径
    const RESOURCE_PATH_OF_TOKEN = '/v3/auth/tokens';

    // 创建客户API的路径
    const RESOURCE_PATH_OF_CREATE_USER = '/v2/partners/sub-customers';

    // 查询客户列表API的路径
    const RESOURCE_PATH_OF_CUSTOMERS = '/v2/partners/sub-customers/query';

    // 给用户配置折扣的API的路径
    const RESOURCE_PATH_OF_SET_DISCOUNT = '/v2/partners/discounts';

    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    /**
     * 创建用户
     *
     * @param string $accountId 伙伴销售平台的用户唯一标识，该标识的具体值由伙伴分配
     * @param string $userName 客户的华为云账号名
     * @param string $cooperationType 模式，1是推荐模式，0是垫付模式
     * @return mixed
     * @throws GuzzleException
     * @throws HuaweiCloudException
     */
    public function createCustomer($accountId = '', $userName = '', $cooperationType = '0')
    {
        try {
            $response = $this->httpClient->request('POST', $this->makeUrl(self::RESOURCE_PATH_OF_CREATE_USER),
                [
                    'form_params' => [
                        'xaccount_id' => $accountId,
                        'xaccount_type' => config('huawei.xaccount_type'),
                        'domain_name' => $userName,
                        'cooperation_type' => $cooperationType, // 1代表推荐模式
                    ],
                    'headers' => [
                        'X-AUTH-TOKEN' => $this->getToken()
                    ]
                ]
            );
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
        }

        return $this->formatResponse($response);
    }

    /**
     * 获取用户列表
     *
     * @return mixed
     * @throws GuzzleException
     * @throws HuaweiCloudException
     */
    public function customers()
    {
        try {
            $response = $this->httpClient->request('POST', $this->makeUrl(self::RESOURCE_PATH_OF_CUSTOMERS),
                [
                    'form_params' => [
                        'account_name' => null
                    ],
                    'headers' => [
                        'X-AUTH-TOKEN' => $this->getToken()
                    ]
                ]
            );
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
        }

        return $this->formatResponse($response);
    }

    /**
     * 给用户设置折扣
     *
     * @param string $customerId 客户ID
     * @param int $discount 折扣
     * @param \Carbon\Carbon|null $expiresAt $expiresAt 失效时间，默认2年后，或传递一个Canbon实例
     * @return mixed
     * @throws GuzzleException
     * @throws HuaweiCloudException
     */
    public function setDiscount($customerId = '', $discount = 1, \Carbon\Carbon $expiresAt = null)
    {
        // 默认有效期一年
        $expiresAt = $expiresAt ?: Carbon::now()->addYear(1);

        try {
            $response = $this->httpClient->request('POST', $this->makeUrl(self::RESOURCE_PATH_OF_SET_DISCOUNT),
                [
                    'json' => [
                        'sub_customer_discounts' => [
                            [
                                'customer_id' => $customerId,
                                'discount' => "$discount", // 折扣率，最高精确到4位小数。折扣范围：0.8~1
                                "effective_time" => Carbon::now()->toIso8601ZuluString(),
                                "expire_time" => $expiresAt->toIso8601ZuluString()
                            ]
                        ],
                    ],
                    'headers' => [
                        'X-AUTH-TOKEN' => $this->getToken()
                    ],
                    'debug' => false,
                ]
            );
        } catch (ClientException $exception) {
            $response = $exception->getResponse();
        }

        return $this->formatResponse($response);
    }

    /**
     * 从HTTP响应中提取关键信息
     *
     * @param null $response
     * @return mixed
     */
    private function formatResponse(\GuzzleHttp\Psr7\Response $response = null)
    {
        if (optional(json_decode($response->getBody()))->error_code) {
            if ('APIGW.0307' === optional(json_decode($response->getBody()))->error_code) {
                // 此错误码表示Token失效（即使Token在有效期内，也可能失效）,清除缓存
                $this->destroyTokenInCache();
            }

            return (new Response())->setStatusCode($response->getStatusCode())
                ->setRequestId(optional(json_decode($response->getBody()))->request_id)
                ->setErrorCode(optional(json_decode($response->getBody()))->error_code)
                ->setErrorMessage(optional(json_decode($response->getBody()))->error_msg);
        } else {
            return (new Response())->setStatusCode($response->getStatusCode())
                ->setRequestId(optional(json_decode($response->getBody()))->request_id)
                ->setResult(collect(json_decode($response->getBody())));
        }
    }

    /**
     * 获取Token
     *
     * @return mixed
     * @throws GuzzleException
     * @throws HuaweiCloudException
     */
    public function getToken()
    {
        return Cache::get('huawei_cloud_token') ?: $this->getTokenFromServer();
    }

    /**
     * 从服务器获取新的Token，应在前Token失效时使用
     *
     * @return mixed
     * @throws GuzzleException
     * @throws HuaweiCloudException
     */
    private function getTokenFromServer()
    {
        try {
            Log::info('从华为云获取token');
            // Log::info($response->getBody());

            $response = $this->httpClient->request('POST', $this->makeUrl(self::RESOURCE_PATH_OF_TOKEN),
                [
                    'json' => [
                        'auth' => [
                            "identity" => [
                                "methods" => ["password"],
                                "password" => [
                                    "user" => [
                                        "name" => config('huawei.auth.name'),
                                        "password" => config('huawei.auth.password'),
                                        "domain" =>  [
                                            "name" => config('huawei.auth.name')
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);

            $this->storeTokenToCache($response);
            return $response->getHeader('X-Subject-Token');
        } catch (ClientException $exception) {
            $response = $exception->getResponse();

            throw new HuaweiCloudException($response->getBody());
        }
    }

    /**
     * 将token信息存入缓存中
     *
     * @param null $response
     */
    private function storeTokenToCache($response = null)
    {
        $body = json_decode($response->getBody());
        $expiresAt = Carbon::parse($body->token->expires_at)->setTimezone(config('app.timezone'));

        $result = Cache::put('huawei_cloud_token', $response->getHeader('X-Subject-Token'), $expiresAt->subMinutes(10));

        // Log::info('将华为云Token存入缓存的返回：'.$result);
        // Log::info('从缓存获取华为云Token：');
        // Log::info(Cache::get('huawei_cloud_token'));
    }

    /**
     * 清除Token缓存
     *
     */
    private function destroyTokenInCache()
    {
        $result = Cache::forget('huawei_cloud_token');
    }

    /**
     * @param string $resourcePath
     * @return string
     */
    private function makeUrl($resourcePath = '')
    {
        return config('huawei.scheme').'://'.config('huawei.endpoint').$resourcePath;
    }
}
