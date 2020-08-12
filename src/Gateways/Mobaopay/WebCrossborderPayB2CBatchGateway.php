<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Events;
use Yansongda\Supports\Collection;

/**
 * 订单批量支付
 * @package Yansongda\Pay\Gateways\Mobaopay
 */
class WebCrossborderPayB2CBatchGateway extends Gateway
{
    /**
     * 支付（web）
     * 1、用户在电商系统下单后，电商系统引导用户浏览器跳转到收银台
     * 2、用户在收银台选择支付方式（支付方式有网银支付、一键支付、非银行卡支付、快捷支付、微信扫码、支付宝扫码等支付方式）用户可选择支付方式为用户所在电商系统在跨境支付开通的支付方式。
     * 3、根据商户支付请求，创建支付订单
     * @param string $endpoint
     * @param array $payload
     */
    public function pay($endpoint, array $payload): Collection
    {
        $payload['apiName'] = 'WEB_CROSSBORDER_PAY_B2C_BATCH';
        $payload['apiVersion'] = '1.0.0.1';
        $payload['currency'] = 'CNY';
        $payload['overTime'] = $payload['overTime'] ?: 7200;
        $payload['customerIP'] = Support::getIp();
        $sign_requird = [
            'apiName' => $payload['apiName'],
            'apiVersion' => $payload['apiVersion'],
            'platformID' => $payload['platformID'],
            'merchNo' => $payload['merchNo'],
            'tradeDate' => $payload['tradeDate'],
            'batchNo' => $payload['batchNo'],
            'currency' => $payload['currency'],
            'sumAmt' => $payload['sumAmt'],
            'countNum' => $payload['countNum'],
            'orderInfoList' => $payload['orderInfoList'],
            'merchUrl' => $payload['merchUrl'],
            'frontMerchUrl' => $payload['frontMerchUrl'],
            'merchParam' => $payload['merchParam'],
            'tradeSummary' => $payload['tradeSummary'],
            'overTime' => $payload['overTime'],
            'customerIP' => $payload['customerIP'],
        ];
        $empty_unset_field_list = ['frontMerchUrl', 'overTIme'];

        foreach ($empty_unset_field_list as $field) {
            if (empty($sign_requird[$field])) {
                unset($sign_requird[$field]);
            }
        }

        $payload['signMsg'] = Support::generateSign($sign_requird);

        Events::dispatch(new Events\PayStarted('Mobaopay', 'WebCrossborderPayB2CBatchGateway', $endpoint, $payload));

        return $this->buildPayHtml($endpoint, $payload);
    }

    /**
     * 查询
     * 查询批量支付订单详情，返回指定商户批次号的指定订单订单交易详情，
     * 若orderNoList=""，则返回该批次号下的所有订单交易详情
     * @param $order
     * @return array|string[]
     */
    public function find($order): array
    {
        return [
            'apiName' => 'MOBO_BATCH_TRAN_QUERY',
            'apiVersion' => '1.0.0.0',
        ];
    }
}
