<?php

class SQLiciousErrorException extends ErrorException
{
	function __construct($message)
	{
		parent::__construct($message, null, E_USER_ERROR);
	}
}

class SQLiciousGetFieldValueException extends SQLiciousErrorException
{
	function __construct($message)
	{
		parent::__construct($message, null, E_USER_ERROR);
	}
}

class SQLiciousConnectionErrorException extends SQLiciousErrorException
{
	function __construct($message)
	{
		parent::__construct($message, null, E_USER_ERROR);
	}
}

?>