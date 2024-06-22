<?php
namespace yangchao\jwt\server;
interface StorageInterface
{
    public function set($key, $value, $expire);
    public function get($key);
    public function delete($key);
}

