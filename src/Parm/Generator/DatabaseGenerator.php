<?php

namespace Parm\Generator;

class DatabaseGenerator
{
	var $databaseNode;
	var $destinationDirectory;
	var $generatedNamespace;
	
	function __construct($databaseNode)
	{
		$this->setDatabaseNode($databaseNode);
	}
	
	function setDatabaseNode($databaseNode)
	{
		if($databaseNode instanceof \Parm\DatabaseNode)
		{
			$this->databaseNode = $databaseNode;
		}
		else
		{
			throw new Exception('Database must be a \Parm\DatabaseNode');
		}
	}
	
	function setDestinationDirectory($directory)
	{
		$this->destinationDirectory = $directory;
	}
	
	function setGeneratedNamespace($namespaceString)
	{
		$this->generatedNamespace = $namespaceString;
	}
	
	private function getTableNames()
	{
		$databaseName = $this->databaseNode->serverDatabaseName;
		
		$dp = new \Parm\DatabaseProcessor($this->databaseNode);
		$dp->setSQL('SHOW TABLES');
		
		$tableNames = array();
		
		$dp->process(function($row) use(&$tableNames,$databaseName) {
			
			$tableNames[] = $row['Tables_in_'.$databaseName];
		});
		
		return $tableNames;
	}
	
	function generate()
	{
		$tableNames = $this->getTableNames();
		
		print_r($tableNames);
		
	}
	
	
	
}




?>