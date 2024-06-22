<?php


namespace yangchao\jwt\claim;

use yangchao\jwt\Claim;

class Customer extends Claim
{
    public function __construct($name, $value)
    {
        parent::__construct($value);
        $this->setName($name);
    }
}
