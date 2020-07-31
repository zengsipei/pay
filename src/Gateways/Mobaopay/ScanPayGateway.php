<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Events;
use Yansongda\Supports\Collection;

/**
 * 直连扫码支付
 * 商户通过本接口向收银台发起直连扫码支付，收银台处理请求成功后返回 base64 编码的 codeUrl，
 * 商户根据 codeUrl 进行 base64 解码后生成二维码图片，用户扫描二维码进行支付，
 * 支持 QQ 扫码支付、微信扫码支付及支付宝扫码支付、后期可能还支持摩宝扫码支付
 * @package Yansongda\Pay\Gateways\Mobaopay
 */
class ScanPay extends Gateway
{
    public function pay($endpoint, array $payload): Collection
    {
        $payload['apiName'] = 'SCAN_PAY';
        $payload['apiVersion'] = '1.0.0.1';
        $payload['tradeDate'] = date('Ymd');
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
            'scanType' => $payload['scanType'],
        ];
        $payload['signMsg'] = Support::generateSign($sign_requird);

        Events::dispatch(new Events\PayStarted('Mobaopay', 'WebCrossborderPayB2C', $endpoint, $payload));

        return Support::requestApi($payload);
    }
}
