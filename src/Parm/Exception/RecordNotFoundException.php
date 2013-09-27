<?php

namespace Parm\Exception;

class RecordNotFoundException extends \ErrorException
{
	function __construct($message)
	{
		parent::__construct($message, null, E_USER_ERROR);
	}
}

?>