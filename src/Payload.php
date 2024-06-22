<?php

namespace yangchao\jwt;

class Payload
{
    protected array $payload = [];

    protected Config $config;
    private $iss;
    private $aud;
    private $iat;// 签发时间
    private $nbf;// 定义在什么时间之前，该jwt都是不可用的.
    private $exp; // 过期时间
    private $jti;// 唯一身份标识，主要用来作为一次性token,从而回避重放攻击。
    private $from_jti;
    private $device;
    private $claims;
    private $store;
    protected $payloadFields = [
        'iss',
        'aud',
        'iat',
        'exp',
        'nbf',
        'jti',
        'from_jti',
        'device',
        'claims',
        'store'
    ];

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->store = $this->config->getAppStore();
    }

    public function setDefaultClaims(): void
    {
        foreach ($this->payloadFields as $value) {
            $this->payload[$value] = $this->$value;
        }
    }

    public function setPayload(array $data)
    {
        $this->payload = $data;
        foreach ($this->payloadFields as $value) {
            $this->$value = $data[$value] ?? null;
        }
    }

    public function setDeviceVerify(): void
    {
        if ($this->config->getIsSingleDevice()) {
            $this->device = $this->getDeviceEncode();
            $this->payload['device'] = $this->device;
        }
    }

    public function getDeviceEncode(): string
    {
        switch ($this->config->getDeviceVerify()) {
            case Config::DEVICE_VERIFY_IP:
                $device = $_SERVER['REMOTE_ADDR'];
                break;
            case Config::DEVICE_VERIFY_UA:
                $device = md5($_SERVER['HTTP_USER_AGENT']);
                break;
            case Config::DEVICE_VERIFY_IP_UA:
                $device = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
                break;
        }
        return md5($device . $this->config->getAppStore());
    }

    public function createJti(): string
    {
        return md5(time() . uniqid() . rand(100000, 999999));
    }

    public function customClaims(array $data = [], bool $refresh = false): Payload
    {
        $this->exp = $refresh ? $this->getRefreshTtlTime() : $this->getExpiresTime();
        $this->iss = $this->config->getIis();
        $this->aud = $this->config->getIis();
        $this->iat = time();
        $this->nbf = $this->config->getNbf();
        $this->jti = $this->createJti();
        $this->form_jti = $refresh ? $this->jti : null;
        $this->claims = $data;

        $this->setDefaultClaims();
        $this->setDeviceVerify();

        return $this;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getExpireTime(): int
    {
        return $this->exp;
    }

    public function getExpiresTime(): int
    {
        return time() + $this->config->getExpiresAt();
    }
    public function getRefreshTtlTime(): int
    {
        return $this->iat + $this->config->getRefreshTtl();
    }

    public function getClaims()
    {
        return $this->claims;
    }

    public function getDevice()
    {
        return $this->device;
    }

    public function getIss()
    {
        return $this->iss;
    }
    public function getStore()
    {
        return $this->store;
    }
    public function getJti()
    {
        return $this->jti;
    }
    public function getFromJti()
    {
        return $this->from_jti;
    }
}