<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Events;

/**
 * 订单批量支付（web）
 * 1、用户在电商系统下单后，电商系统引导用户浏览器跳转到收银台
 * 2、用户在收银台选择支付方式（支付方式有网银支付、一键支付、非银行卡支付、快捷支付、微信扫码、支付宝扫码等支付方式）用户可选择支付方式为用户所在电商系统在跨境支付开通的支付方式。
 * 3、根据商户支付请求，创建支付订单
 * @package Yansongda\Pay\Gateways\Mobaopay
 */
class WebCrossborderPayB2CBatch extends Gateway
{
    public function pay($endpoint, array $payload): Response
    {
        $payload['apiName'] = 'WEB_CROSSBORDER_PAY_B2C_BATCH';
        $payload['apiVersion'] = '1.0.0.1';
        $payload['currency'] = 'CNY';
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

        Events::dispatch(new Events\PayStarted('Mobaopay', 'WebCrossborderPayB2CBatch', $endpoint, $payload));

        return $this->buildPayHtml($endpoint, $payload);
    }

    protected function buildPayHtml($endpoint, $payload, $method = 'POST'): Response
    {
        if ('GET' === strtoupper($method)) {
            return new RedirectResponse($endpoint.'&'.http_build_query($payload));
        }

        $sHtml = "<form id='mobaopay_submit' name='mobaopay_submit' action='".$endpoint."' method='".$method."'>";

        foreach ($payload as $key => $val) {
            $val = str_replace("'", '&apos;', $val);
            $sHtml .= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

        $sHtml .= "<input type='submit' value='ok' style='display:none;'></form>";
        $sHtml .= "<script>document.forms['mobaopay_submit'].submit();</script>";

        return new Response($sHtml);
    }

    public function find($order): array
    {
        return [
            'apiName' => 'MOBO_BATCH_TRAN_QUERY',
            'apiVersion' => '1.0.0.0',
        ];
    }
}
