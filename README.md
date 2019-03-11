# credit
芝麻信用，2019，可用，简单

## 初始化
```php
<?php
$aliConfig = [
     'appId' => '支付宝appId',
     'alipayrsaPublicKey' => '支付宝公钥（一整行字符串），详情请查看支付宝生成公钥的文档',

     'rsaPrivateKey' => '支付宝私钥（一整行字符串），详情请查看支付宝生成私钥的文档',
 ];
$orderId = time() . '身份证号';
$result = Ali::init($aliConfig)
->startInit('场景', $orderId, '身份证号', '真实姓名');


```