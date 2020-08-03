<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Events;
use Yansongda\Supports\Collection;

/**
 * 微信小程序支付
 * @package Yansongda\Pay\Gateways\Mobaopay
 */
class WechatMiniProgramGateway extends Gateway
{
    /**
     * 支付
     * 商户通过本接口向收银台发起微信小程序支付，
     * 收银台处理请求成功后返回 JSON 格式的 wcPayData，
     * 商户根据 wcPayData 唤醒微信支付
     * @param string $endpoint
     * @param array $payload
     * @return Collection
     * @throws \Yansongda\Pay\Exceptions\InvalidConfigException
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['apiName'] = 'WECHAT_MINI_PROGRAM';
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
            'overTime' => $payload['overTime'],
            'customerIP' => $payload['customerIP'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Events::dispatch(new Events\PayStarted('Mobaopay', 'WechatMiniProgram', $endpoint, $payload));

        return Support::requestApi($payload);
    }
}
