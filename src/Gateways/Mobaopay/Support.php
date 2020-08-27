<?php

namespace Yansongda\Pay\Gateways\Mobaopay;

use Yansongda\Pay\Events;
use Yansongda\Pay\Exceptions\BusinessException;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Exceptions\InvalidArgumentException;
use Yansongda\Pay\Exceptions\InvalidConfigException;
use Yansongda\Pay\Exceptions\InvalidSignException;
use Yansongda\Pay\Gateways\Mobaopay;
use Yansongda\Pay\Log;
use Yansongda\Supports\Collection;
use Yansongda\Supports\Config;
use Yansongda\Supports\Str;
use Yansongda\Supports\Traits\HasHttpRequest;

/**
 * @package Yansongda\Pay\Gateways\Mobaopay
 * @property string apiName 接口名字
 * @property string apiVersion 接口版本
 * @property string platformID 平台 ID
 * @property string merchNo 商户账号
 * @property string key 密钥
 * @property string mode 当前模式
 * @property array log log 选项
 * @property array http http 选项
 */
class Support
{
    use HasHttpRequest;

    protected $baseUri;
    protected $config;
    /**
     * @var Support
     */
    private static $instance;
    /**
     * 海关所需原始支付数据
     * @var $initalRequest
     * @var $initalResponse
     */
    private $initalRequest;
    private $initalResponse;

    private function __construct(Config $config)
    {
        $this->baseUri = Mobaopay::URL[$config->get('mode', Mobaopay::MODE_NORMAL)];
        $this->config = $config;
        $this->setHttpOptions();
    }

    public function __get($key)
    {
        return $this->getConfig($key);
    }

    public static function create(Config $config)
    {
        if ('cli' === php_sapi_name() || !(self::$instance instanceof self)) {
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            throw new InvalidArgumentException('You Should [Create] First Before Using');
        }
        return self::$instance;
    }

    public function clear()
    {
        self::$instance = null;
    }

    public static function requestApi(array $data): Collection
    {
        Events::dispatch(new Events\ApiRequesting('Mobaopay', '', self::$instance->getBaseUri(), $data));

        $result = self::$instance->post('', $data);
        self::$instance->initalRequest = self::$instance->baseUri . '?' . urldecode(http_build_query($data));
        self::$instance->initalResponse = is_array($result) ? self::toXml($result) : $result;
        $result = is_array($result) ? $result : self::fromXml($result);

        Events::dispatch(new Events\ApiRequested('Mobaopay', '', self::$instance->getBaseUri(), $result));

        return self::processingApiResult($result);
    }

    protected static function processingApiResult(array $result): Collection
    {
        if (!isset($result['signMsg']) || '00' != $result['respData']['respCode']) {
            throw new GatewayException('Get Mobaopay API Error:'.($result['respData']['respDesc'] ?? ''), $result);
        }
        // ZTODO:摩宝推荐不验签
        if (true) {
            return new Collection([
                'result' => new Collection($result),
                'initalRequest' => self::getInstance()->initalRequest,
                'initalResponse' => self::getInstance()->initalResponse
            ]);
        }

        Events::dispatch(new Events\SignFailed('Mobaopay', '', $result));

        throw new InvalidSignException('Mobaopay Sign Verify FAILED', $result);
    }

    public function getConfig($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->config->all();
        }
        if ($this->config->has($key)) {
            return $this->config[$key];
        }
        return $default;
    }

    private function setHttpOptions(): self
    {
        if ($this->config->has('http') && is_array($this->config->get('http'))) {
            $this->config->forget('http.base_uri');
            $this->httpOptions = $this->config->get('http');
        }
        return $this;
    }

    public static function generateSign(array $params): string
    {
        $key = self::$instance->key;

        if (is_null($key)) {
            throw new InvalidConfigException('Missing Mobaopay Config -- [key]');
        }

        $sign = md5(self::getSignContent($params) . $key);

        Log::debug('Mobaopay Generate Sign', [$params, $sign]);

        return $sign;
    }

    public static function getSignContent(array $data): string
    {
        $buff = '';

        foreach ($data as $k => $v) {
            if (empty($v)) {
                $v = '';
            }

            $buff .= ('signMsg' != $k) ? "{$k}={$v}&" : '';
        }

        Log::debug('Mobaopay Generate Sign Content Before Trim', [$data, $buff]);

        return trim($buff, '&');
    }

    public static function toXml($data): string
    {
        if (!is_array($data) || count($data) <= 0) {
            throw new InvalidArgumentException('Convert To Xml Error! Invalid Array!');
        }

        $xml = '<?xml version="1.0" encoding="utf-8" ?><moboAccount>';

        foreach ($data as $key => $val) {
            $xml .= "<{$key}>{$val}</{$key}>";
        }

        $xml .= '</moboAccount>';

        return $xml;
    }

    public static function fromXml($xml): array
    {
        if (!$xml) {
            throw new InvalidArgumentException('Convert To Array Error! Invalid Xml!');
        }

        libxml_disable_entity_loader(true);

        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }

    public static function getIp(): string
    {
        if (@$_SERVER['HTTP_CLIENT_IP'] && $_SERVER['HTTP_CLIENT_IP'] != 'unknown') {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (@$_SERVER['HTTP_X_FORWARDED_FOR'] && $_SERVER['HTTP_X_FORWARDED_FOR'] != 'unknown') {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match('/^\d[\d.]+\d$/', $ip) ? $ip : '';
    }

    public static function verifySign(array $data, $sign = null): bool
    {
        $sign = $sign ?? $data['signMsg'];
        unset($data['signMsg'], $data['notifyType']);

        return self::generateSign($data) == $sign;
    }
}
