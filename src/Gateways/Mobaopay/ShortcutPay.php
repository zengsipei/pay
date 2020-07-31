<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Events;
use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;

/**
 * 跨境银联快捷支付
 * @package Yansongda\Pay\Gateways\Mobaopay
 */
class ShortcutPay
{
    /**
     * 获取银行卡签约短信
     * 如果用户银行卡未签约,调用此接口,接收短信验证码
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function getSignMessageUnionPay($payload): Collection
    {
        $payload['apiName'] = 'GET_SIGN_MESSAGE_UNION_PAY';
        $payload['apiVersion'] = '1.0.0.0';
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'platformID' => $payload['platformID'],
            'merchNo' => $payload['merchNo'],
            'cardNo' => $payload['cardNo'],
            'mobile' => $payload['mobile'],
            'cardName' => $payload['cardName'],
            'idCardNo' => $payload['idCardNo'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('ShortcutPay::getSignMessageUnionPay', $payload);

        return Support::requestApi($payload);
    }

    /**
     * 确认银联签约
     * 调用签约短信后,根据短信码,调用此接口进行绑定签约
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function signConfirmUnionPay($payload): Collection
    {
        $payload['apiName'] = 'SIGN_CONFIRM_UNION_PAY';
        $payload['apiVersion'] = '1.0.0.0';
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'platformID' => $payload['platformID'],
            'merchNo' => $payload['merchNo'],
            'cardNo' => $payload['cardNo'],
            'mobile' => $payload['mobile'],
            'cardName' => $payload['cardName'],
            'idCardNo' => $payload['idCardNo'],
            'smsCode' => $payload['smsCode'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('ShortcutPay::signConfirmUnionPay', $payload);

        return Support::requestApi($payload);
    }

    /**
     * 商户直连快捷订单创建及申请
     * 用户在电商系统直连下单,该步骤相当于创建订单,并申请
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function shortcutPayCreateOrder($payload): Collection
    {
        $payload['apiName'] = 'SHORTCUT_PAY_CREATE_ORDER';
        $payload['apiVersion'] = '1.0.0.0';
        $payload['tradeDate'] = date('Ymd');
        $payload['currency'] = 'CNY';
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
            'overTime' => $payload['overTime'],
            'customerIP' => $payload['customerIP'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('ShortcutPay::shortcutPayCreateOrder', $payload);

        return Support::requestApi($payload);
    }

    /**
     * 商户直连快捷支付确认
     * 商户在调用订单创建成功及申请接口成功后,调用该接口确认
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function shortcutPayConfirmUnionPay($payload): Collection
    {
        $payload['apiName'] = 'SHORTCUT_PAY_CONFIRM_UNION_PAY';
        $payload['apiVersion'] = '1.0.0.0';

        Log::debug('ShortcutPay::shortcutPayConfirmUnionPay', $payload);

        return Support::requestApi($payload);
    }

    /**
     * 商户直连快捷支付申请
     * 用户在创建订单成功后,如果提示某种原因造成申请失败了的,
     * 可以调用该接口发起重新发起申请,需要提供创建成功后的订单号,
     * 调用成功后,在调用商户直连快捷支付确认接口
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function shortcutPayApplyUnionPay($payload): Collection
    {
        $payload['apiName'] = 'SHORTCUT_PAY_APPLY_UNION_PAY';
        $payload['apiVersion'] = '1.0.0.0';
        $payload['tradeDate'] = date('Ymd');
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'platformID' => $payload['platformID'],
            'merchNo' => $payload['merchNo'],
            'cardNo' => $payload['cardNo'],
            'cardBankCode' => $payload['cardBankCode'],
            'mobile' => $payload['mobile'],
            'cardName' => $payload['cardName'],
            'idCardNo' => $payload['idCardNo'],
            'orderNo' => $payload['orderNo'],
            'tradeDate' => $payload['tradeDate'],
            'amt' => $payload['amt'],
            'merchUrl' => $payload['merchUrl'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('ShortcutPay::shortcutPayApplyUnionPay', $payload);

        return Support::requestApi($payload);
    }
}
