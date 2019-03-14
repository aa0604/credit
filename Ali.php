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

    public $gatewayUrl = "https://zmopenapi.zmxy.com.cn/openapi.do";

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
        $aopClient->format = $config['format'] ?? 'json';
        $aopClient->charset = $config['charset'] ?? 'utf-8';
        $aopClient->apiVersion = '1.0';

        return $aopClient;
    }

    /**
     * 初始化
     * @param $bizCode
     * @param $cardNumber
     * @param string $name
     * @param string $transactionId
     * @return bool|mixed|\SimpleXMLElement
     * @throws \Exception
     */
    public function getBizNo($bizCode, $cardNumber, $name = '', $transactionId = '')
    {

        $linkedMerchantId = $this->config['linkedMerchantId'] ?? '';
        $aop = $this->getAopClient();
        $request = new \xing\payment\sdk\aliPay\aop\request\ZhimaCustomerCertificationInitializeRequest ();
        $request->setBizContent("{" .
            "\"transaction_id\":\"{$transactionId}\"," .
            "\"product_code\":\"w1010100000000002978\"," .
            "\"biz_code\":\"{$bizCode}\"," .
            "\"identity_param\":\"{\\\"identity_type\\\":\\\"CERT_INFO\\\",\\\"cert_type\\\":\\\"IDENTITY_CARD\\\",\\\"cert_name\\\":\\\"{$name}\\\",\\\"cert_no\\\":\\\"{$cardNumber}\\\"}\"," .
            "\"merchant_config\":\"{}\"," .
            "\"ext_biz_param\":\"{}\"," .
            "\"linked_merchant_id\":\"{$linkedMerchantId}\"," .
            "\"face_contrast_picture\":\"xydasf==\"" .
            "  }");
        $this->request = $result = $aop->execute ( $request);

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000){
            return $result->$responseNode->biz_no;
        } else {
            throw new \Exception('访问失败:code=' . $resultCode );
        }
    }

    /**
     * 获取H5 认证url
     * @param $bizNo
     * @param $returnUrl
     */
    public function goH5Url($bizNo, $returnUrl)
    {
        $aop = $this->getAopClient();
        $request = new \xing\payment\sdk\aliPay\aop\request\ZhimaCustomerCertificationCertifyRequest ();
        $request->setReturnUrl($returnUrl);
        $request->setBizContent("{" .
            "\"biz_no\":\"{$bizNo}\"" .
            "  }");
        $result = $aop->pageExecute ( $request, 'GET');
        if (is_string($result)) return $result;

        return $this->checkResult($request);
    }

    public function isFinish($bizNo)
    {
        $aop = $this->getAopClient();
        $request = new \xing\payment\sdk\aliPay\aop\request\ZhimaCustomerCertificationQueryRequest ();
        $request->setBizContent("{" .
            "\"biz_no\":\"{$bizNo}\"" .
            "  }");
        $this->request = $result = $aop->execute ( $request);
        return $this->checkResult($request);
    }

    /**
     * 检查结果，如果失败则抛出错误
     * @param \xing\payment\sdk\aliPay\aop\request\ZhimaCustomerCertificationQueryRequest|\xing\payment\sdk\aliPay\aop\request\ZhimaCustomerCertificationCertifyRequest $request
     * @return bool
     * @throws \Exception
     */
    private function checkResult($request)
    {
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $result = isset($result->$responseNode) ? $result->$responseNode : null;
        if (empty($result)) throw new \Exception('访问失败');
        if ($result->code != 10000 || !$result->passed) throw new \Exception($result->failed_reason ?? $result->sub_msg, $result->code);
        return true;
    }

    public function getResult()
    {
        return $this->request;
    }
}