<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Yansongda\Pay\Contracts\GatewayInterface;
use Yansongda\Pay\Gateways\Mobaopay\Support;
use Yansongda\Supports\Collection;

abstract class Gateway implements GatewayInterface
{
    protected $mode;

    public function __construct()
    {
        $this->mode = Support::getInstance()->mode;
    }

    abstract public function pay($endpoint, array $payload);

    protected function buildPayHtml($endpoint, $payload, $method = 'POST'): Collection
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

        return new Collection([
            'result' => new Response($sHtml),
            'initalRequest' => $endpoint . '?' . urldecode(http_build_query($payload)),
            'initalResponse' => ''
        ]);
    }
}
