# Huawei Cloud Client for Laravel

## 简介

适用于Laravel的华为云伙伴API的客户端。

## 安装
```
composer require nookery/laravel-huawei-cloud-client  

php artisan vendor:publish --provider="\HuaweiCloud\Provider"    
```

最后在`config/huawei.php`文件中配置您的账号和密码。

## 使用

- 输出格式
    以下function的输出都是这个实例：`HuaweiCloud\Contracts\Response`
    
- 查询当前账号下的客户

```php
    \HuaweiCloud\Facades\HuaweiCloud::customers();
```

- 创建客户账号

```php
    /**
     * 创建用户
     *
     * @param string $accountId 伙伴销售平台的用户唯一标识，该标识的具体值由伙伴分配
     * @param string $userName 客户的华为云账号名
     * @param string $cooperationType 模式，1是推荐模式，0是垫付模式，默认是垫付模式
     * @return mixed
     * @throws GuzzleException
     * @throws HuaweiCloudException
     */
    \HuaweiCloud\Facades\HuaweiCloud::createCustomer($accountId = '', $userName = '', $cooperationType = '0');
```

- 为用户设置折扣

```php
    /**
     * 给用户设置折扣
     *
     * @param string $customerId 客户ID
     * @param int $discount 折扣
     * @param \Carbon\Carbon $expiresAt 失效时间，默认2年后，或传递一个Canbon实例
     * @return mixed
     * @throws GuzzleException
     * @throws HuaweiCloudException
     */
    \HuaweiCloud\Facades\HuaweiCloud::setDiscount($customerId = '', $discount = 1, \Carbon\Carbon $expiresAt = null);
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
