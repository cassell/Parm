<?php

class SQLiciousConfig implements ArrayAccess
{
	var $databases = array();
	
	function __construct(){ }
	
	function addDatabaseConfiguration($name,$configuration)
	{
		$this->databases[$name] = $configuration;
	}
	
	function getDatabases()
	{
		return $this->databases;
	}
	
	public function offsetSet($offset, $value)
	{
        if (is_null($offset))
		{
            $this->databases[] = $value;
        }
		else
		{
            $this->databases[$offset] = $value;
        }
    }
	
    public function offsetExists($offset)
	{
        return isset($this->databases[$offset]);
    }
	
    public function offsetUnset($offset)
	{
        unset($this->databases[$offset]);
    }
	
    public function offsetGet($offset)
	{
        return isset($this->databases[$offset]) ? $this->databases[$offset] : null;
    }
	
	// returns an associative array of the row retrieved from the database
	function toArray()
	{
		return $this->databases;
	}
	
}

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
				throw new SQLiciousConnectionErrorException("SQLicioius Connection Error");
			}
		}
		
		return $this->connection;
	}
	
	function closeConnection($conn)
	{
		$this->connection->close();
	}
	
	
}


class DatabaseConfiguration
{
	private $databaseName = null;
	private $master;
	private $slaves = array();

	function __construct($databaseName)
	{
		$this->databaseName = $databaseName;
		
		// default to a folder per database in the same folder as classes, generator, etc.
		$this->setGeneratorCodeDestinationDirectory(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $this->databaseName);
	}
	
	function setMaster($master)
	{
		$this->master = $master;
	}
	
	function getMaster()
	{
		return $this->master;
	}
	
	function addSlave($slave, $slaveName = null)
	{
		if($slaveName != null)
		{
			$this->slaves[$slaveName] = $slave;
		}
		else
		{
			$this->slaves[] = $slave;
		}
	}
	
	function getSlave($slaveName = null)
	{
		if($slaveName != null && $this->slaves[$slaveName] instanceof DatabaseNode)
		{
			return $this->slaves[$slaveName];
		}
		else
		{
			return array_rand($this->slaves,1);
		}
	}
	
	function setGeneratorCodeDestinationDirectory($val) { $this->generatorCodeDestinationDirectory = $val; }
	function getGeneratorCodeDestinationDirectory() { return $this->generatorCodeDestinationDirectory; }
	
}

?>