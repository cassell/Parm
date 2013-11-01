<?php

namespace Parm;

class Database
{
	private $master;

	/**
     * Set the master database node for the configuration
	 * 
	 * @param DatabaseNode $masterDatabaseNode
     */
	function setMaster(DatabaseNode $masterDatabaseNode)
	{
		$this->master = $masterDatabaseNode;
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