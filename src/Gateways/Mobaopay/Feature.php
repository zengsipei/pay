<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;

/**
 * 特色功能
 * @package Yansongda\Pay\Gateways\Mobaopay
 */
class Feature
{
    /**
     * 订单拆分
     * 电商客户同时支付普通订单与跨境订单的情况下，电商企业可调用订单拆分接口，
     * 使将支付订单拆分出跨境订单，并将跨境订单进行通关申报。
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function mobaoOrderSplit($payload): Collection
    {
        $payload['apiName'] = 'MOBO_ORDER_SPLIT';
        $payload['apiVersion'] = '1.0.0.1';
        $payload['customerIP'] = Support::getIp();
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'merchNo' => $payload['merchNo'],
            'merchOrderNo' => $payload['merchOrderNo'],
            'sumAmt' => $payload['sumAmt'],
            'countNum' => $payload['countNum'],
            'orderSplitList' => $payload['orderSplitList'],
            'customerIP' => $payload['customerIP'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('Feature::mobaoOrderSplit', $payload);

        return Support::requestApi($payload);
    }

    /**
     * 二要素实名认证
     * 商户通过本接口向收银台发起 二要素实名认证交易，收银台处理请求成功后返回处理结果
     * @param $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function realNameAuth($payload): Collection
    {
        $payload['apiName'] = 'REAL_NAME_AUTH';
        $payload['apiVersion'] = '1.0.0.1';
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'merchNo' => $payload['merchNo'],
            'realName' => $payload['realName'],
            'idCard' => $payload['idCard'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Log::debug('Feature::realNameAuth', $payload);

        return Support::requestApi($payload);
    }
}
