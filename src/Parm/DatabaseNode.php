<?php

namespace Parm;

use \mysqli;

class DatabaseNode
{
	var $serverDatabaseName;
	var $serverHost;
	var $serverUsername;
	var $serverPassword;
	var $serverPort;
	var $serverSocket;
	var $serverCharset; // default utf8
	var $serverCaseSensitiveCollation;  // default utf8_bin
	var $connection;
	
	function __construct($serverDatabaseName, $serverHost, $serverUsername, $serverPassword, $serverPort = null, $serverSocket = null, $serverCharset = 'utf8', $serverCaseSensitiveCollation = 'utf8_bin')
	{
		$this->serverDatabaseName = $serverDatabaseName;
		$this->serverHost = $serverHost;
		$this->serverUsername = $serverUsername;
		$this->serverPassword = $serverPassword;
		$this->serverPort = $serverPort;
		$this->serverSocket = $serverSocket;
		$this->serverCharset = $serverCharset;
		$this->serverCaseSensitiveCollation = $serverCaseSensitiveCollation;
	}
	
	function getConnection()
	{
		if($this->connection == null)
		{
			$this->connection = new mysqli($this->serverHost, $this->serverUsername, $this->serverPassword, $this->serverDatabaseName, $this->serverPort ? $this->serverPort : null, $this->serverSocket);
			$this->connection->set_charset($this->serverCharset);

			if($this->connection == null || $this->connection->connect_errno)
			{
				throw new Parm\Exception\ConnectionErrorException($this->connection);
			}
		}
		
		return $this->connection;
	}
	
	function closeConnection($conn)
	{
		$this->connection->close();
	}
	
	
}

?>