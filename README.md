# yangchao/php-jwt
## 安装

```php
composer require yangchao/php-jwt
```

## 配置
```php
$config = [
    'iss' => 'yangchao/jwt', // 令牌签发者
    'signer' => \yangchao\jwt\Config::ALGO_HS256,//加密类型
    'nbf' => 5,// 某个时间点后才能访问，单位秒。（如：5 表示当前时间5秒后TOKEN才能使用）
    'expires_at' => 5, //过期时间，单位：秒
    'refresh_disable' => false,//是否禁用刷新令牌
    'refresh_ttl' => 604800,//刷新令牌过期时间，单位：秒
    'leeway' => 60, // 容错时间差，单位：秒
    'is_single_device' => true, // 是否开启单设备登录
    'device_verify' => 'ua', // 单设备验证方式，可选值：ua(User-Agent)、ip(客户端IP)、ip_ua(IP+UA)
    'secret_key' => '', //HS256 密钥
    'refresh_secret_key' => '',//HS256 刷新密钥
    'public_key' => '', //RS256 RSA公钥
    'private_key' => '',//RS256 RSA私钥
    'refresh_public_key' => '',//RS256 刷新RSA公钥
    'refresh_private_key' => '',//RS256 刷新RSA私钥
    'black_list'=>[ //黑名单配置
        'redis_host' => '120.78.131.19',//黑名单储存 redis主机
        'redis_password' => 'CZdLYnWfbqKxv7Tp',// redis密码
        'redis_port' => 6379,// redis端口
        'storage_server'=> XXX::class// 储存服务器类型
    ]
];
```
### 配置说明
* refresh_disable 为 true 时，刷新禁用，refresh_ttl、refresh_secret_key、refresh_public_key、refresh_private_key可以不设置。
* is_single_device 为 false 时，不启用单设备登录，device_verify 可以不设置。
* signer 配置加密类型，为HS256 时，secret_key 必填，为RS256 时，public_key、private_key 必填。
* black_list 黑名单需要储存服务，这里给出了两个方案，一个使用redis储存需要配置你的redis_host、redis_password、redis_port；另一种为自定义储存类。 自定义储存类需要实现 \yangchao\jwt\StorageInterface 接口，具体实现参考 \yangchao\jwt\Storage\RedisStorage 类。
## 使用说明
```php
$jwt = new \yangchao\jwt\JWTAuth($config);
//创建token
$token = $jwt->createToken(['a'=>'b']);
//验证token
$claims = $jwt->verifyToken($token)
//刷新Token 当refresh_disable为false时（不禁用刷新），此处token传值为刷新token
$claims = $jwt->refreshToken($token);
//获取token过期时间
$expiresAt = $jwt->getExpireTime();
//获取刷新token过期时间
$refreshExpiresAt = $jwt->getRefreshTtlTime();
```
## 异常说明
* \yangchao\jwt\Exception\JWTException 所有抛出异常
* \yangchao\jwt\Exception\JWTExpiredException token过期异常
* \yangchao\jwt\Exception\JWTServerException 服务器异常(内部处理错误)
* \yangchao\jwt\Exception\JWTConfigException 传入配置错误
* \yangchao\jwt\Exception\JWTVerifyException token验证异常