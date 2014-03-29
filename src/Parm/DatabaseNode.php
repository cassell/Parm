<?php

namespace Parm;

abstract class DatabaseNode
{
	var $serverDatabaseName;
	var $serverHost;
	var $serverUsername;
	var $serverPassword;
	var $serverPort;
	var $serverSocket;
	var $serverCharset = 'utf8'; // default utf8
	var $serverCaseSensitiveCollation = 'utf8_bin';  // default utf8_bin
	private $connection;
	
	const DATE_STORAGE_FORMAT = 'Y-m-d';
	const DATETIME_STORAGE_FORMAT = 'Y-m-d H:i:s';
	

	abstract function getConnection();
	abstract function getLastInsertId();
	abstract function query($queryString);
	abstract function closeConnection();

	function getDateTimeStorageFormat()
	{
		return static::DATETIME_STORAGE_FORMAT;
	}
	
	function getDateStorageFormat()
	{
		return static::DATE_STORAGE_FORMAT;
	}
	
	
}
