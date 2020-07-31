<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Gateways\Mobaopay\Support;

abstract class Gateway implements GatewayInterface
{
    protected $mode;

    public function __construct()
    {
        $this->mode = Support::getInstance()->mode;
    }

    abstract public function pay($endpoint, array $payload);
}
