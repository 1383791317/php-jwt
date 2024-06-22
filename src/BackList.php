<?php

namespace yangchao\jwt;

use yangchao\jwt\exception\JWTConfigException;
use yangchao\jwt\server\Storage;

class BackList
{
    protected $blackListConfig;
    protected $storageServer;
    protected $storageKey = 'JWT:BLACKLIST:';
    protected $storageExpire = 604800; //默认储存7天

    private static $instance;

    public static function instance(Config $config): BackList
    {
        if (self::$instance == null) {
            self::$instance = new static($config);
        }
        return self::$instance;
    }

    public function __construct(Config $config)
    {
        $this->blackListConfig = $config->getBlackList();
        $this->validateConfig();
    }

    private function validateConfig(): void
    {
        if (isset($this->blackListConfig['storage_server']) && $this->blackListConfig['storage_server'] && class_exists($this->blackListConfig['storage_server'])) {
            $this->storageServer = new $this->blackListConfig['storage_server'];
        } else {
            if (!isset($this->blackListConfig['redis_host'])) {
                JWTConfigException::throwException('config redis_host required.');
            }
            if (!isset($this->blackListConfig['redis_password'])) {
                JWTConfigException::throwException('config redis_password required.');
            }
            $port = isset($this->blackListConfig['redis_port']) ? ($this->blackListConfig['redis_port'] ?: 3306) : 3306;
            $this->storageServer = new Storage($this->blackListConfig['redis_host'], $this->blackListConfig['redis_password'], $port);
        };
    }

    public function add($jti, $token, $expire = 0)
    {
        $expire = $expire > 0 ? $expire : $this->storageExpire;
        return $this->storageServer->set($this->storageKey . $jti, $token, $expire ?: $this->storageExpire);
    }

    public function remove($jti)
    {
        return $this->storageServer->delete($this->storageKey . $jti);
    }

    public function getList()
    {
        return $this->storageServer->get($this->storageKey . '*');
    }

    public function get($jti)
    {
        return $this->storageServer->get($this->storageKey . $jti);
    }
}