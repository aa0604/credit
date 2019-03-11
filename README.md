# credit
芝麻信用，2019，可用，简单

## 跳转到h5认证页面url
说明：
1、程序会自动初始化认证
2、自动跳转到认证页面，以下代码需要HTML支持，在不支持HTML，document的app里将无法运行
```php
<?php
function getInstance()
{
    $dir = dirname(dirname(__DIR__));
    $set = [
        'appId' => '支付宝appId',
        'privateKeyFile' => $dir . '支付宝私钥.pem',
        'publicKeyFile' => $dir . '支付宝公钥.pem',
        ];
    return Ali::init($set);
}

$orderId = time() . '身份证号';

$auth = getInstance();
$bizNo = $auth->getBizNo('场景',  '身份证号', '真实姓名', $orderId);

$url = $auth->goH5Url($bizNo, 'http://xxx.com/h5/auth/return');
echo $url;
```

## 认证查询
```php
<?php

try {
    
$auth = getInstance();
// 成功返回真
$result = $auth->isFinish('bizNo');
print_r($auth->getResult()); // 输出结果
} catch (\Exception $e) {
    exit('认证失败：' .$e->getMessage() . ' 错误代码:' . $e->getCode());
}
```