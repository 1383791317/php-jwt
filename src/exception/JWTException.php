<?php

namespace yangchao\jwt\exception;

class JWTException extends \Exception
{
    public static function throwException($message = "", $code = 0, $previous = null)
    {
        throw (new static($message, $code, $previous));
    }

}