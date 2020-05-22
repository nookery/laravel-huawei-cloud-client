# Huawei Cloud Client for Laravel

## 简介

适用于Laravel的华为云伙伴API的客户端。

## 安装
```
composer require nookery/laravel-huawei-cloud-client  

php artisan vendor:publish --provider="Nookery\HuaweiCloud\Provider"    
```

最后在`config/huawei.php`文件中配置您的账号和密码。

## 使用

- 查询当前账号下的客户

```php
    \Nookery\HuaweiCloud\Facades\HuaweiCloud::customers();
```

- 创建客户账号

```php
    \Nookery\HuaweiCloud\Facades\HuaweiCloud::createCustomer();
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
