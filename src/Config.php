<?php

namespace yangchao\jwt;

use yangchao\jwt\exception\JWTConfigException;

class Config
{
    const ALGO_HS256 = 'HS256';
    const ALGO_HS384 = 'HS384';
    const ALGO_HS512 = 'HS512';
    const ALGO_RS256 = 'RS256';
    const ALGO_RS384 = 'RS384';
    const ALGO_RS512 = 'RS512';
    const ALGO_ES256 = 'ES256';
    const ALGO_ES384 = 'ES384';
    const ALGO_ES512 = 'ES512';
    const DEVICE_VERIFY_IP = 'ip';
    const DEVICE_VERIFY_UA = 'ua';
    const DEVICE_VERIFY_IP_UA = 'ip_ua';
    /**
     * 令牌签发者
     * @var string
     */
    protected string $iss = 'yang-chao-php-jwt';
    /**
     * 某个时间点后才能访问，单位秒
     * @var int
     */
    protected int $nbf = 0;
    /**
     * 过期时间，单位：秒
     * @var int
     */
    protected int $expires_at = 3600;
    /**
     * 是否禁用刷新
     * @var bool
     */
    protected bool $refresh_disable = false;
    /**
     * 刷新令牌过期时间，单位：秒
     * @var int
     */
    protected int $refresh_ttl = 7200;
    /**
     * 是否自动续签
     * @var bool
     */
    protected bool $auto_refresh = false;

    /**
     * Token 时钟偏差冗余时间
     * @var int
     */
    protected int $leeway = 0;
    /**
     * 黑名单
     * @var array
     */
    protected array $black_list = [];

    /**
     * 加密类型
     * @var string
     */
    protected string $signer = 'HS256';
    /**
     * 是否单设备登录
     * @var string
     */
    protected bool $is_single_device = false;
    protected string $device_verify = 'ip';
    /**
     * RSA 加密下公钥地址
     * @var string
     */
    protected string $public_key = '';

    /**
     * RSA 加密下私钥地址
     * @var string
     */
    protected string $private_key = '';
    /**
     * RSA 加密下公钥地址
     * @var string
     */
    protected string $refresh_public_key = '';

    /**
     * RSA 加密下私钥地址
     * @var string
     */
    protected string $refresh_private_key = '';
    protected string|null $secret_key = null;
    protected string|null $refresh_secret_key = null;
    protected array $config = [];
    protected string|null $appStore = null;

    public function __construct(array $config, $store = null)
    {
        $this->config = $config;
        $this->setAppStore($store);
    }

    private function initialize(): void
    {
        if ($this->appStore && !isset($this->config[$this->appStore])) {
            JWTConfigException::throwException("The configuration for {$this->appStore} could not be found");
        }
        foreach ($this->config as $key => $value) {
            $this->$key = $this->appStore ? $this->config[$this->appStore][$key] : $value;
        }
    }

    public function getAppStore(): string|null
    {
        return $this->appStore;
    }

    /**
     * 描述：设置应用名称
     * @param $name
     * @return void
     */
    public function setAppStore($name)
    {
        $this->appStore = $name;
        $this->initialize();
    }

    public function getSignerPrivateKey(bool $isRefresh = false): string
    {
        if ($this->signer === self::ALGO_HS256) {
            return $isRefresh ? $this->getRefreshSecretKey() : $this->getSecretKey();
        } elseif ($this->signer === self::ALGO_RS256) {
            return $isRefresh ? $this->getRefreshPrivateKey() : $this->getPrivateKey();
        } else {
            return $isRefresh ? $this->getRefreshSecretKey() : $this->getSecretKey();
        }
    }

    public function getSignerPublicKey(bool $isRefresh = false): string
    {
        if ($this->signer === self::ALGO_HS256) {
            return $isRefresh ? $this->getRefreshSecretKey() : $this->getSecretKey();
        } elseif ($this->signer === self::ALGO_RS256) {
            return $isRefresh ? $this->getRefreshPublicKey() : $this->getPublicKey();
        } else {
            return $isRefresh ? $this->getRefreshSecretKey() : $this->getSecretKey();
        }
    }

    public function getRefreshPublicKey(): string
    {
        if (empty($this->refresh_public_key)) {
            JWTConfigException::throwException('config refresh_public_key required.');
        }
        return $this->refresh_public_key;
    }

    public function getRefreshPrivateKey(): string
    {
        if (empty($this->refresh_private_key)) {
            JWTConfigException::throwException('config refresh_private_key required.');
        }
        return $this->refresh_private_key;
    }

    public function getRefreshSecretKey(): string
    {
        if (empty($this->refresh_secret_key)) {
            JWTConfigException::throwException('config refresh_secret_key required.');
        }
        return $this->refresh_secret_key;
    }

    public function getSecretKey(): string
    {
        if (empty($this->secret_key)) {
            JWTConfigException::throwException('config secret_key required.');
        }
        return $this->secret_key;
    }

    public function getPublicKey(): string
    {
        if (empty($this->public_key)) {
            JWTConfigException::throwException('config public_key required.');
        }
        return $this->public_key;
    }

    public function getPrivateKey(): string
    {
        if (empty($this->private_key)) {
            JWTConfigException::throwException('config private_key required.');
        }
        return $this->private_key;
    }

    public function getSigner(): string
    {
        return $this->signer;
    }

    public function getIis(): string
    {
        return $this->iss;
    }

    public function getExpiresAt(): int
    {
        return $this->expires_at;
    }

    public function getRefreshTtl(): int
    {
        return $this->refresh_ttl;
    }

    public function getRefreshDisable(): bool
    {
        return $this->refresh_disable;
    }

    public function getNbf(): int
    {
        return $this->nbf;
    }

    public function getLeeway()
    {
        return $this->leeway;
    }

    public function getIsSingleDevice(): bool
    {
        return $this->is_single_device;
    }

    public function getDeviceVerify(): string
    {
        return $this->device_verify;
    }

    public function getBlackList(): array
    {
        return $this->black_list;
    }
}