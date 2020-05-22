<?php

namespace Nookery\HuaweiCloud;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Nookery\HuaweiCloud\Exceptions\HuaweiCloudException;

class HuaweiCloud
{
    // 查询客户列表API的路径
    const RESOURCE_PATH_OF_CUSTOMERS = '/v2/partners/sub-customers/query';
    // 获取Token API的路径
    const RESOURCE_PATH_OF_TOKEN = '/v3/auth/tokens';

    public function debug()
    {
        dd($this->customers());
    }

    /**
     * 获取用户列表
     *
     * @return array
     * @throws HuaweiCloudException
     */
    public function customers()
    {
        $response = Http::withHeaders(['X-AUTH-TOKEN' => $this->getToken()])
            ->post($this->makeUrl(self::RESOURCE_PATH_OF_CUSTOMERS), [
                'account_name' => null
            ]);

        return $response->json();
    }

    /**
     * 获取Token
     *
     * @return string
     * @throws HuaweiCloudException
     */
    private function getToken()
    {
        return Cache::get('huawei_cloud_token') ?: $this->getTokenFromServer();
    }

    /**
     * 从服务器获取新的Token，应在前Token失效时使用
     *
     * @return string
     * @throws HuaweiCloudException
     */
    private function getTokenFromServer()
    {
        $response = Http::post($this->makeUrl(self::RESOURCE_PATH_OF_TOKEN), [
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
        ]);

        if ($response->successful()) {
            return $response->header('X-Subject-Token');
        } else {
            throw new HuaweiCloudException($response->body());
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
