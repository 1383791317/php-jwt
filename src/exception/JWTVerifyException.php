<?php

namespace yangchao\jwt\exception;

class JWTVerifyException extends JWTException
{
    public static function throwException($message = "", $code = 0, $previous = null)
    {
        throw (new static('验证失败：' . $message, $code, $previous));
    }
}