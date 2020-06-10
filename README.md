# Huawei Cloud Client for Laravel

<p align="left">
<a href="https://travis-ci.org/nookery/laravel-huawei-cloud-client"><img src="https://travis-ci.org/nookery/laravel-huawei-cloud-client.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/nookery/laravel-huawei-cloud-client"><img src="https://poser.pugx.org/nookery/laravel-huawei-cloud-client/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/nookery/laravel-huawei-cloud-client"><img src="https://poser.pugx.org/nookery/laravel-huawei-cloud-client/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/nookery/laravel-huawei-cloud-client"><img src="https://poser.pugx.org/nookery/laravel-huawei-cloud-client/license.svg" alt="License"></a>
</p>

## 简介

适用于Laravel的华为云伙伴API的客户端。  

本组件是对 [华为云伙伴API](https://support.huaweicloud.com/api-bpconsole/zh-cn_topic_0075200705.html) 的封装，请在已阅读华为云相关文档的前提下使用。

## 版本与兼容

 Laravel  | 本软件
:---------|:----------
 5.8.x    | 1.x.x

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

## 其他说明

本软件使用到了Laravel的缓存（`Illuminate\Support\Facades\Cache`）来存储华为云的Token，缓存驱动请不要配置成`array`，驱动是`array`时仅对单次请求有效。

## License

This software is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
