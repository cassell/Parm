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
	
}