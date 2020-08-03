<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;

/**
 * 商户直连信用卡支付
 * @package Yansongda\Pay\Gateways\Mobaopay
 */
class CreditPay
{
    /**
     * 信用卡一键支付申请
     * 商户通过本接口向收银台发起直连信用卡一键支付申请，
     * 收银台处理请求成功后会返回订单号、日期以及标识符sessionID,
     * 并向提交的手机发送短信验证码。
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function creditPayApply($payload): Collection
    {
        $payload['apiName'] = 'CREDIT_PAY_APPLY';
        $payload['apiVersion'] = '1.0.0.1';
        $payload['tradeDate'] = date('Ymd');
        $payload['overTime'] = $payload['overTime'] ?: 7200;
        $payload['customerIP'] = Support::getIp();
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'platformID' => $payload['platformID'],
            'merchNo' => $payload['merchNo'],
            'orderNo' => $payload['orderNo'],
            'tradeDate' => $payload['tradeDate'],
            'currency' => $payload['currency'],
            'amt' => $payload['amt'],
            'merchUrl' => $payload['merchUrl'],
            'merchParam' => $payload['merchParam'],
            'tradeSummary' => $payload['tradeSummary'],
            'mobile' => $payload['mobile'],
            'cardNo' => $payload['cardNo'],
            'overTime' => $payload['overTime'],
            'customerIP' => $payload['customerIP'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('CreditPay::creditPayApply', $payload);

        return Support::requestApi($payload);
    }

    /**
     * 信用卡一键支付确认
     * 商户通过本接口向收银台发起直连信用卡一键支付确认，
     * 收银台处理请求成功后会返回订单信息。
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function creditPayConfirm($payload): Collection
    {
        $payload['apiName'] = 'CREDIT_PAY_CONFIRM';
        $payload['apiVersion'] = '1.0.0.1';
        $payload['customerIP'] = Support::getIp();
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'platformID' => $payload['platformID'],
            'merchNo' => $payload['merchNo'],
            'accNo' => $payload['accNo'],
            'accDate' => $payload['accDate'],
            'sessionID' => $payload['sessionID'],
            'dymPwd' => $payload['dymPwd'],
            'cardExpire' => $payload['cardExpire'],
            'cardCvn2' => $payload['cardCvn2'],
            'customerIP' => $payload['customerIP'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('CreditPay::creditPayConfirm', $payload);

        return Support::requestApi($payload);
    }

    /**
     * 短信重发
     * 收银台收到请求后，会重发短信到支付时提交银行卡绑定的手机号上。
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function SMSResend($payload): Collection
    {
        $payload['apiName'] = 'SMS_RESEND';
        $payload['apiVersion'] = '1.0.0.1';
        $payload['customerIP'] = Support::getIp();
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'platformID' => $payload['platformID'],
            'merchNo' => $payload['merchNo'],
            'accNo' => $payload['accNo'],
            'accDate' => $payload['accDate'],
            'sessionID' => $payload['sessionID'],
            'customerIP' => $payload['customerIP'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('CreditPay::SMSResend', $payload);

        return Support::requestApi($payload);
    }
}
