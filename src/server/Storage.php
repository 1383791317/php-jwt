<?php

namespace yangchao\jwt\server;

use yangchao\jwt\exception\JWTServerException;

class Storage implements StorageInterface
{
    protected $redisServer;

    public function __construct($host, $password, $port = 3306)
    {
        $this->redisServer = $this->getRedisConnect($host, $password, $port);
    }

    private function getRedisConnect($host, $password, $port): \Redis
    {
        try {
            $redis = new \Redis();
            $redis->connect($host, $port);
            if ($password){
                $redis->auth($password);
            }
        } catch (\Exception $exception) {
            JWTServerException::throwException($exception->getMessage());
        }
        return $redis;
    }

    public function get($key)
    {
        return $this->redisServer->get($key);
    }

    public function set($key, $value, $expire)
    {
        return $this->redisServer->set($key, $value, $expire);
    }

    public function delete($key)
    {
        return $this->redisServer->delete($key);
    }
}