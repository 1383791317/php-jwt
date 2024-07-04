<?php

namespace yangchao\jwt;

class JWTAuth
{
    protected JWT $jwt;

    public function __construct(array $config = [])
    {
        $this->jwt = new JWT(new Config($config));
    }

    public function store(string $store): JWTAuth
    {
        $this->jwt->setAppStore($store);
        return $this;
    }

    public function createToken(array $data): array
    {
        $res = [
            'token' => $this->jwt->make($data),
            'expire_time' => $this->jwt->payload()->getExpireTime(),
            'refresh_time' => $this->jwt->payload()->getRefreshTtlTime()
        ];
        if (!$this->jwt->getConfig()->getRefreshDisable()) {
            $res['refresh_token'] = $this->jwt->make($data, true);
        }
        return $res;
    }

    public function verifyToken(string $token)
    {
        return $this->jwt->verify($token);
    }

    public function refreshToken(string $token): array
    {
        $res = [
            'token' => $this->jwt->refresh($token),
            'expire_time' => $this->jwt->payload()->getExpireTime(),
            'refresh_time' => $this->jwt->payload()->getRefreshTtlTime()
        ];
        if (!$this->jwt->getConfig()->getRefreshDisable()) {
            $res['refresh_token'] = $this->jwt->make($this->jwt->payload()->getClaims(), true);
        }
        return $res;
    }

    public function addBlacklist(string $token)
    {
        return $this->jwt->addBlacklist($token);
    }
    public function removeBlacklist(string $token)
    {
        return $this->jwt->removeBlacklist($token);
    }

    public function jwt()
    {
        return $this->jwt();
    }

    public function getRefreshTtlTime(): int
    {
        return $this->jwt->payload()->getRefreshTtlTime();
    }

    public function getExpireTime(): int
    {
        return $this->jwt->payload()->getExpireTime();
    }
}