<?php

namespace Parm\Exception;

class ErrorException extends \ErrorException
{
    public function __construct($message)
    {
        parent::__construct($message, null, E_USER_ERROR);
    }
}
