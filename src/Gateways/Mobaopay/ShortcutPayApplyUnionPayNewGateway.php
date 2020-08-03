<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Events;
use Yansongda\Supports\Collection;

/**
 * 商户直连快捷支付
 * @package Yansongda\Pay\Gateways\Mobaopay
 */
class ShortcutPayApplyUnionPayNewGateway extends Gateway
{
    /**
     * 支付
     * 用户在电商系统直连下单
     * @param string $endpoint
     * @param array $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['apiName'] = 'SHORTCUT_PAY_APPLY_UNION_PAY_NEW';
        $payload['apiVersion'] = '1.0.0.0';
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
            'overTime' => $payload['overTime'],
            'customerIP' => $payload['customerIP'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Events::dispatch(new Events\PayStarted('Mobaopay', 'ShortcutPayApplyUnionPayNew', $endpoint, $payload));

        return Support::requestApi($payload);
    }
}
