<?php

namespace Parm\Generator;

class DatabaseGenerator
{
	var $databaseNode;
	var $destinationDirectory;
	var $generatedNamespace;
	
	function __construct()
	{
		
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
	
	function getTableNames()
	{
		$dp = new DatabaseProcessor($this->getDatabaseName());
		$dp->setSQL('SHOW TABLES');
		
//		$tableNames = array();
		
		$dp->process(function($array){
			
			
		});
		
//		if($data != null && count($data) > 0)
//		{
//			foreach($data as $d)
//			{
//				$tableNames[] = $d['Tables_in_'.$this->getDatabaseName()];
//			}
//		}
//		
//		return $tableNames;
	}
	
	
}




?>