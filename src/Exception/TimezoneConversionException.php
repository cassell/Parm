<?php

namespace Parm\Exception;

/**
 * Class TimezoneConversionException
 * @codeCoverageIgnore
 * @package Parm\Exception
 */
class TimezoneConversionException extends \ErrorException
{
    /**
     * @param string $message
     */
    public function __construct($message)
    {
        parent::__construct($message, null, E_USER_ERROR);
    }
}
