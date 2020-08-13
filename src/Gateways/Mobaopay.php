<?php

namespace Yansongda\Pay\Gateways;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contracts\GatewayApplicationInterface;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Events;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidGatewayException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Gateways\Mobaopay\Support;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;

/**
 * 摩宝支付
 * @package Yansongda\Pay\Gateways
 * @method Response webCrossborderPayB2C(array $config) 订单支付（web）
 * @method Response webCrossborderPayB2CBatch(array $config) 订单批量支付（web）
 * @method Collection scanPay(array $config) 直连扫码支付
 * @method Collection shortcutPayApplyUnionPayNew(array $config) 跨境快捷无码支付 - 商户直连快捷支付(免密)
 * @method Collection wechatMiniProgram(array $config) 微信小程序支付
 * @method Collection appPay(array $config) APP支付
 * @method Collection wechatScanDirect(array $config) 微信直连扫码支付
 * @method Collection alScanDirect(array $config) 支付宝直连扫码支付
 */
class Mobaopay implements GatewayApplicationInterface
{
    const MODE_NORMAL = 'normal';
    const MODEL_QUICK_PAY = 'quick_pay';
    const MODEL_UPDATE_PAY_VOUCHER = 'update_pay_voucher';
    const URL = [
        self::MODE_NORMAL => 'http://cbpaycashier.mobaopay.com/cgi-bin/netpayment/pay_gate.cgi',
        self::MODEL_QUICK_PAY => 'http://cbpaycashier.mobaopay.com/cgi-bin/netpayment/quick_pay_gate.cgi',
        self::MODEL_UPDATE_PAY_VOUCHER => 'http://cbpaycashier.mobaopay.com/cgi-bin/netpayment/update_pay_voucher.cgi',
    ];
    protected $payload;
    protected $gateway;

    public function __construct(Config $config)
    {
        $this->gateway = Support::create($config)->getBaseUri();
        $this->payload = [
            'platformID' => $config->get('platformID', ''),
            'merchNo' => $config->get('merchNo', ''),
            'signMsg' => '',
        ];
    }

    public function __call($method, $params)
    {
        if (isset($this->extends[$method])) {
            return $this->makeExtend($method, ...$params);
        }
        return $this->pay($method, ...$params);
    }

    public function pay($gateway, $params = [])
    {
        Events::dispatch(new Events\PayStarting('Mobaopay', $gateway, $params));

        $this->payload = array_merge($this->payload, $params);

        $gateway = get_class($this).'\\'.Str::studly($gateway).'Gateway';

        if (class_exists($gateway)) {
            return $this->makePay($gateway);
        }
        throw new InvalidGatewayException("Pay Gateway [{$gateway}] Not Exists");
    }

    public function find($order, string $type = 'webCrossborderPayB2C'): Collection
    {
        $gateway = get_class($this).'\\'.Str::studly($type).'Gateway';

        if (!class_exists($gateway) || !is_callable([new $gateway(), 'find'])) {
            throw new GatewayException("{$gateway} Done Not Exist Or Done Not Has FIND Method");
        }

        $config = call_user_func([new $gateway(), 'find'], $order);
        $this->payload = array_merge($this->payload, $config);
        $this->payload['signMsg'] = Support::generateSign($this->payload);

        Events::dispatch(new Events\MethodCalled('Mobaopay', 'Find', $this->gateway, $this->payload));

        return Support::requestApi($this->payload);
    }

    public function refund(array $order): Collection
    {
        $this->payload = array_merge($this->payload, $order);
        $this->payload['apiName'] = 'REFUND_DIRECT';
        $this->payload['apiVersion'] = '1.0.0.1';
        $this->payload['overTime'] = $this->payload['overTime'] ?: 7200;
        $this->payload['customerIP'] = Support::getIp();
        $this->payload['signMsg'] = Support::generateSign($this->payload);

        Events::dispatch(new Events\MethodCalled('Mobaopay', 'Refund', $this->gateway, $this->payload));

        return Support::requestApi($this->payload);
    }

    public function cancel($order)
    {
        // TODO: Implement cancel() method.
    }

    public function close($order)
    {
        // TODO: Implement close() method.
    }

    public function verify($content = null, bool $refund = false): Collection
    {
        if (is_null($content)) {
            $request = Request::createFromGlobals();
            $content = $request->request->count() > 0 ? $request->request->all() : $request->query->all();
        }

        Events::dispatch(new Events\RequestReceived('Mobaopay', '', $content));

        if (Support::verifySign($content)) {
            return new Collection($content);
        }

        Events::dispatch(new Events\SignFailed('Mobaopay', '', $data));

        throw new InvalidSignException('Mobaopay Sign Verify FAILED', $data);
    }

    public function success(): Response
    {
        Events::dispatch(new Events\MethodCalled('Mobaopay', 'Success', $this->gateway));

        return new Response('success');
    }

    public function extend(string $method, callable $function, bool $now = true): ?Collection
    {
        if (!$now && !method_exists($this, $method)) {
            $this->extends[$method] = $function;

            return null;
        }

        $customize = $function($this->payload);

        if (!is_array($customize) && !($customize instanceof Collection)) {
            throw new InvalidArgumentException('Return Type Must Be Array Or Collection');
        }

        Events::dispatch(new Events\MethodCalled('MobaoPay', 'extend', $this->gateway, $customize));

        if (is_array($customize)) {
            $this->payload = $customize;
            $this->payload['sign'] = Support::generateSign($this->payload);

            return Support::requestApi($this->payload);
        }

        return $customize;
    }

    protected function makePay(string $gateway)
    {
        $app = new $gateway();

        if ($app instanceof GatewayInterface) {
            return $app->pay($this->gateway, $this->payload);
        }
        throw new InvalidGatewayException("Pay Gateway [{$gateway}] Must Be An Instance Of GatewayInterface");
    }

    protected function makeExtend(string $method, array ...$params): Collection
    {
        $params = count($params) >= 1 ? $params[0] : $params;

        $function = $this->extends[$method];

        $customize = $function($this->payload, $params);

        if (!is_array($customize) && !($customize instanceof Collection)) {
            throw new InvalidArgumentException('Return Type Must Be Array Or Collection');
        }

        Events::dispatch(new Events\MethodCalled(
            'Mobaopay',
            'extend - '.$method,
            $this->gateway,
            is_array($customize) ? $customize : $customize->toArray()
        ));

        if (is_array($customize)) {
            $this->payload = $customize;
            $this->payload['sign'] = Support::generateSign($this->payload);

            return Support::requestApi($this->payload);
        }

        return $customize;
    }

    /**
     * 商户直连信用卡支付
     * @param $order
     * @param string $type
     * @return Collection
     * @throws GatewayException
     */
    public function creditPay($order, string $type = 'creditPayApply'): Collection
    {
        $gateway = get_class($this).'\\CreditPay';

        if (!is_callable([new $gateway(), $type])) {
            throw new GatewayException("{$gateway} Done Not Exist Or Done Not Has {$type} Method");
        }

        $this->payload = array_merge($this->payload, $order);

        return call_user_func([new $gateway(), $type], $this->payload);
    }

    /**
     * 特色功能
     * @param $order
     * @param string $type
     * @return Collection
     * @throws GatewayException
     */
    public function feature($order, string $type): Collection
    {
        $gateway = get_class($this).'\\Feature';

        if (!is_callable([new $gateway(), $type])) {
            throw new GatewayException("{$gateway} Done Not Exist Or Done Not Has {$type} Method");
        }

        $this->payload = array_merge($this->payload, $order);

        return call_user_func([new $gateway(), $type], $this->payload);
    }

    /**
     * 跨境银联快捷支付
     * @param $order
     * @param string $type
     * @return Collection
     * @throws GatewayException
     */
    public function shortcutPay($order, string $type = 'getSignMessageUnionPay'): Collection
    {
        $gateway = get_class($this).'\\ShortcutPay';

        if (!is_callable([new $gateway(), $type])) {
            throw new GatewayException("{$gateway} Done Not Exist Or Done Not Has {$type} Method");
        }

        $this->payload = array_merge($this->payload, $order);

        return call_user_func([new $gateway(), $type], $this->payload);
    }

    /**
     * 海关支付单
     * @param $order
     * @param string $type
     * @return Collection
     * @throws GatewayException
     */
    public function payment($order, string $type = 'PAYMENT_PUSH_SEND'): Collection
    {
        $gateway = get_class($this).'\\Payment';

        if (!is_callable([new $gateway(), $type])) {
            throw new GatewayException("{$gateway} Done Not Exist Or Done Not Has {$type} Method");
        }

        $this->payload = array_merge($this->payload, $order);

        return call_user_func([new $gateway(), $type], $this->payload);
    }

    /**
     * 通关宝
     * @param $order
     * @param string $type
     * @return Collection
     * @throws GatewayException
     */
    public function thirdPartyPayment($order, string $type = 'THIRD_PARTY_PAYMENT_SUBMIT'): Collection
    {
        $gateway = get_class($this).'\\ThirdPartyPayment';

        if (!is_callable([new $gateway(), $type])) {
            throw new GatewayException("{$gateway} Done Not Exist Or Done Not Has {$type} Method");
        }

        $this->payload = array_merge($this->payload, $order);

        return call_user_func([new $gateway(), $type], $this->payload);
    }
}
