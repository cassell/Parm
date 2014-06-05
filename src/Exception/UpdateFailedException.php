<?php

namespace Parm\Exception;

class UpdateFailedException extends \ErrorException
{
    public function __construct($sql)
    {
        parent::__construct("Update failed using sql: " . $sql, null, E_USER_ERROR);
    }
}
