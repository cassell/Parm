<?php

namespace Parm;

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