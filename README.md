# credit
由于芝麻信用于2019年停止服务，支付宝官方建议使用支付宝的认证产品来代替芝麻信用认证。所以本插件已将停止，并将全部源码替换为支付宝认证服务。

方法和原来的一样，也就是说你只需要更新，不需要改你原来代码。

注：本插件依赖本人的另一个插件（支付插件），因为这个支付插件的支付宝官方sdk被我大量修改为能够使用的了。。。你也可以用我这个支付插件，支持微信，支付宝，paypal，首信易等支付
# 认证流程
1、程序发起初始化

2、获取到唯一流水号

3、用户跳转到H5页面认证

4、用户完成认证后返回h5页面

5、h5页面向服务器发起认证轮询，完成则做相关动作

## 跳转到h5认证页面url
说明：

程序会自动初始化认证

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

$returnUrl = 'http://xxx.com/h5/auth/return';
$auth = getInstance();
$bizNo = $auth->getBizNo('场景',  '身份证号', '真实姓名', $orderId, $returnUrl);
$url = $auth->goH5Url($bizNo, $returnUrl);
echo $url;
```

## 认证查询
```php
<?php

try {
    
$auth = getInstance();
// 成功返回真
$result = $auth->isPassed('bizNo');
print_r($auth->getResult()); // 输出结果
} catch (\Exception $e) {
    exit('认证失败：' .$e->getMessage() . ' 错误代码:' . $e->getCode());
}
```