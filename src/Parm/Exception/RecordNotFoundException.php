<?php

namespace Parm\Exception;

class RecordNotFoundException extends \ErrorException
{
    public function __construct($message)
    {
        parent::__construct($message, null, E_USER_ERROR);
    }
}
