<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;

/**
 * 通关宝
 * @package Yansongda\Pay\Gateways\Mobaopay
 */
class ThirdPartyPayment
{
    /**
     * 通关宝订单支付单推送
     * 商户通过本接口向收银台发起通关宝订单的支付单推送，收银台处理请求成功后返回成功或失败
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function thirdPartyPaymentSubmit($payload): Collection
    {
        $payload['apiName'] = 'THIRD_PARTY_PAYMENT_SUBMIT';
        $payload['apiVersion'] = '1.0.0.0';
        $payload['customerIP'] = Support::getIp();
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'customerIP' => $payload['customerIP'],
            'merchNo' => $payload['merchNo'],
            'merchOrderNo' => $payload['merchOrderNo'],
            'orderNo' => $payload['orderNo'],
            'customsType' => $payload['customsType'],
            'payAmt' => $payload['payAmt'],
            'payerIdNumber' => $payload['payerIdNumber'],
            'payerName' => $payload['payerName'],
            'telephone' => $payload['telephone'],
            'payTime' => $payload['payTime'],
            'ebpName' => $payload['ebpName'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('ThirdPartyPayment::thirdPartyPaymentSubmit', $payload);

        return Support::requestApi($payload);
    }

    /**
     * 通关宝额度查询
     * 商户通过本接口向收银台发起通关额度查询，收银台处理请求成功后返回成功或失败
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function thirdPartyPaymentQuery($payload): Collection
    {
        $payload['apiName'] = 'THIRD_PARTY_PAYMENT_QUERY';
        $payload['apiVersion'] = '1.0.0.0';
        $payload['customerIP'] = Support::getIp();
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'customerIP' => $payload['customerIP'],
            'merchNo' => $payload['merchNo'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('ThirdPartyPayment::thirdPartyPaymentQuery', $payload);

        return Support::requestApi($payload);
    }
}
