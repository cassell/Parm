<?php

namespace Parm;

class Database
{
	private $master;

	function __construct()
	{
		
	}
	
	/**
     * Set the master database node for the configuration
	 * 
	 * @param DatabaseNode $masterDatabaseNode
     */
	function setMaster($masterDatabaseNode)
	{
		if($masterDatabaseNode instanceof \Parm\DatabaseNode)
		{
			$this->master = $masterDatabaseNode;
		}
		else
		{
			throw new \Parm\Exception\ErrorException('Master DatabaseNode must be a Parm\DatabaseNode');
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
	
}