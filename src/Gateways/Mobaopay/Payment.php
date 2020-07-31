<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;

/**
 * 海关支付单
 * @package Yansongda\Pay\Gateways\Mobaopay
 */
class Payment
{
    /**
     * 海关数据更改支付人信息
     * 商户通过本接口向收银台发起报送海关数据更改支付人信息，
     * 收银台处理请求成功后返回成功或失败
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function payVoucherOrderSubmit($payload): Collection
    {
        $payload['apiName'] = 'PAY_VOUCHER_ORDER_SUBMIT';
        $payload['apiVersion'] = '1.0.0.0';
        $payload['customerIP'] = Support::getIp();
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'customerIP' => $payload['customerIP'],
            'merchNo' => $payload['merchNo'],
            'merchOrderNo' => $payload['merchOrderNo'],
            'payerIdNumber' => $payload['payerIdNumber'],
            'payerName' => $payload['payerName'],
            'telephone' => $payload['telephone'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('Payment::payVoucherOrderSubmit', $payload);

        return Support::requestApi($payload);
    }

    /**
     * 海关平台支付单推送
     * 商户通过本接口向收银台发起支付单推送，收银台处理请求成功后返回成功或失败
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function paymentPushSend($payload): Collection
    {
        $payload['apiName'] = 'PAYMENT_PUSH_SEND';
        $payload['apiVersion'] = '1.0.0.0';
        $payload['customerIP'] = Support::getIp();
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'customerIP' => $payload['customerIP'],
            'merchNo' => $payload['merchNo'],
            'merchOrderNo' => $payload['merchOrderNo'],
            'customsType' => $payload['customsType'],
            'payerIdNumber' => $payload['payerIdNumber'],
            'payerName' => $payload['payerName'],
            'telephone' => $payload['telephone'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('Payment::paymentPushSend', $payload);

        return Support::requestApi($payload);
    }

    /**
     * 支付单状态信息查询
     * 商户通过本接口向收银台发起支付单状态查询请求，收银台处理请求成功后返回支付单状态信息
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function paymentQuery($payload): Collection
    {
        $payload['apiName'] = 'PAYMENT_QUERY';
        $payload['apiVersion'] = '1.0.0.0';
        $payload['customerIP'] = Support::getIp();
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'customerIP' => $payload['customerIP'],
            'merchNo' => $payload['merchNo'],
            'merchOrderNo' => $payload['merchOrderNo'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('Payment::paymentQuery', $payload);

        return Support::requestApi($payload);
    }
}
