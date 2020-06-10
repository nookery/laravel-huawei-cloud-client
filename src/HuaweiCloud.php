<?php

namespace HuaweiCloud;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use HuaweiCloud\Exceptions\HuaweiCloudException;

class HuaweiCloud
{
    // 获取Token API的路径
    const RESOURCE_PATH_OF_TOKEN = '/v3/auth/tokens';

    // 创建客户API的路径
    const RESOURCE_PATH_OF_CREATE_USER = '/v2/partners/sub-customers';

    // 查询客户列表API的路径
    const RESOURCE_PATH_OF_CUSTOMERS = '/v2/partners/sub-customers/query';

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

        return json_decode((string) $response->getBody());
    }

    /**
     * 获取用户列表
     *
     * @return mixed
     * @throws HuaweiCloudException
     * @throws GuzzleException
     */
    public function customers()
    {
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

        return json_decode((string) $response->getBody());
    }

    /**
     * 获取Token
     *
     * @return mixed
     * @throws GuzzleException
     * @throws HuaweiCloudException
     */
    private function getToken()
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

        if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
            return $response->getHeader('X-Subject-Token');
        } else {
            throw new HuaweiCloudException($response->getBody());
        }
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
