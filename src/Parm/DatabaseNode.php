<?php

namespace Parm;

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
	var $dateTimeStorageFormat;
	var $dateStorageFormat;
	var $connection;
	
	/**
     * Create a database node
	 * 
	 * @param string $serverDatabaseName
	 * @param string $serverHost
	 * @param string $serverUsername
	 * @param string $serverPassword
	 * @param string $serverPort
	 * @param string $serverSocket
	 * @param string $serverCharset
	 * @param string $serverCaseSensitiveCollation
	 * @param string $dateTimeStorageFormat
	 * @param string $dateStorageFormat
	 * 
     */
	function __construct($serverDatabaseName, $serverHost, $serverUsername, $serverPassword, $serverPort = null, $serverSocket = null, $serverCharset = 'utf8', $serverCaseSensitiveCollation = 'utf8_bin', $dateTimeStorageFormat = 'Y-m-d H:i:s', $dateStorageFormat = 'Y-m-d')
	{
		$this->serverDatabaseName = $serverDatabaseName;
		$this->serverHost = $serverHost;
		$this->serverUsername = $serverUsername;
		$this->serverPassword = $serverPassword;
		$this->serverPort = $serverPort;
		$this->serverSocket = $serverSocket;
		$this->serverCharset = $serverCharset;
		$this->serverCaseSensitiveCollation = $serverCaseSensitiveCollation;
		$this->dateTimeStorageFormat = $dateTimeStorageFormat;
		$this->dateStorageFormat = $dateStorageFormat;
		
	}
	
	/**
	 * Get the id of the last inserted row from the database
     * @return object mysqli connection
     */
	function getConnection()
	{
		if($this->connection == null)
		{
			$this->connection = new \mysqli($this->serverHost, $this->serverUsername, $this->serverPassword, $this->serverDatabaseName, $this->serverPort ? $this->serverPort : null, $this->serverSocket);
			$this->connection->set_charset($this->serverCharset);

			if($this->connection == null || $this->connection->connect_errno)
			{
				throw new \Parm\Exception\ConnectionErrorException($this->connection);
			}
		}
		
		return $this->connection;
	}
	
	/**
	 * Get the id of the last inserted row from the database
     * @return integer id of the last inserted row from the database
     */
	function getLastInsertId()
	{
		if($this->connection)
		{
			return $this->connection->insert_id;
		}
		else
		{
			throw new \Parm\Exception\ConnectionErrorException($this->connection);
		}
	}
	
	/**
	 * Close the connection to the database node
     */
	function closeConnection()
	{
		$this->connection->close();
	}
	
	
}
