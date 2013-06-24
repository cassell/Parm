<?php

namespace Parm;

class Database
{
	private $master;
	private $slaves = array();

	
	function __construct()
	{
		
	}
	
	/**
     * Set the master database node for the configuration
	 * 
	 * @param $master DatabaseNode
     */
	function setMaster($masterDatabaseNode)
	{
		if($masterDatabaseNode instanceof \Parm\DatabaseNode)
		{
			$this->master = $masterDatabaseNode;
		}
		else
		{
			throw new \Exception('Master DatabaseNode must be a Parm\DatabaseNode');
		}
	}
	
	/**
     * Get the master database node for the configuration
	 * 
	 * @return DatabaseNode|null The DatabaseNode that is the Master connection
     */
	function getMaster()
	{
		return $this->master;
	}
	
	/*
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
	 */
	
	
}

?>