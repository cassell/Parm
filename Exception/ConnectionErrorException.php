<?php

namespace Parm\Exception;

class ConnectionErrorException extends Parm\ErrorException
{
	var $connection;
	
	function __construct($connection)
	{
		parent::__construct("Connection Error " . print_r($connection,true), null, E_USER_ERROR);
	}
}

?>