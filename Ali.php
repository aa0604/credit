<?php
/**
 * Created by PhpStorm.
 * User: xing.chen
 * Date: 2019/3/11
 * Time: 17:14
 */

namespace xing\credit;

use xing\payment\sdk\aliPay\aop\AopClient;

class Ali
{

    private $config;

    private $params;

    public $AopClient;

    public $request;


    /**
     * 初始化
     * @param $config
     * @return Ali
     */
    public static function init($config)
    {
        $class = new self();
        $class->config = $config;
        defined('AOP_SDK_WORK_DIR') ?: define("AOP_SDK_WORK_DIR", $config['logDir'] ?? sys_get_temp_dir() . '/');

//        初始化支付宝和配置参数
//        $class->AopClient = $class->getAopClient();
//        返回本类自身
        return $class;
    }
    /**
     * @return AopClient
     */
    private function getAopClient()
    {
        $config = & $this->config;
        $aopClient = new AopClient();
        $aopClient->appId = $config['appId'];
        $aopClient->rsaPrivateKey = $config['rsaPrivateKey'];  // 请填写开发者私钥去头去尾去回车，一行字符串
        $aopClient->alipayrsaPublicKey = $config['alipayrsaPublicKey']; // 请填写支付宝公钥，一行字符串
        $aopClient->signType = $config['signType'] ?? 'RSA2';  // 签名方式
        $aopClient->format = $config['format'] ?? 'JSON';
        $aopClient->charset = $config['charset'] ?? 'utf-8';

        return $aopClient;
    }

    /**
     * 初始化
     * @param $cardNumber
     * @param string $name
     * @param string $transactionId
     * @return bool
     * @throws \Exception
     */
    public function startInit($cardNumber, $name = '', $transactionId = '')
    {

        $set = PaymentSetMap::$set['aliPay'];
        $linkedMerchantId = $set['linkedMerchantId'] ?? '';
        $aop = $this->getAopClient();
        $aop = new \xing\payment\sdk\aliPay\aop\AopClient ();
//        $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
//        $aop->appId = $set['appId'];
//        $aop->rsaPrivateKey = $set['rsaPrivateKey'];
//        $aop->alipayrsaPublicKey=$set['alipayrsaPublicKey'];
        $aop->apiVersion = '1.0';
//        $aop->signType = 'RSA2';
//        $aop->postCharset='UTF-8';
//        $aop->format='json';
        $request = new \xing\payment\sdk\aliPay\aop\request\ZhimaCustomerCertificationInitializeRequest ();
        $request->setBizContent("{" .
            "\"transaction_id\":\"{$transactionId}\"," .
            "\"product_code\":\"w1010100000000002978\"," .
            "\"biz_code\":\"FACE\"," .
            "\"identity_param\":\"{\\\"identity_type\\\":\\\"CERT_INFO\\\",\\\"cert_type\\\":\\\"IDENTITY_CARD\\\",\\\"cert_name\\\":\\\"{$cardNumber}\\\",\\\"cert_no\\\":\\\"{$cardNumber}\\\"}\"," .
            "\"merchant_config\":\"{}\"," .
            "\"ext_biz_param\":\"{}\"," .
            "\"linked_merchant_id\":\"{$linkedMerchantId}\"," .
            "\"face_contrast_picture\":\"xydasf==\"" .
            "  }");
        $this->request = $result = $aop->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000){
            return $result;
        } else {
            throw new \Exception('访问失败:code=' . $resultCode );
        }
    }

    public function getResult()
    {
        return $this->request;
    }
}