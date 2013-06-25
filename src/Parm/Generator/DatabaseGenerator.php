<?php

namespace Parm\Generator;

class DatabaseGenerator
{
	const DestinationDirectoryFolderPermissions = 0777;
	const GenereatedCodeFilePermissions = 0777;
	
	var $database;
	var $databaseNode;
	var $destinationDirectory;
	var $generatedNamespace = "\\Parm\\Dao\\";

	function __construct($database)
	{
		$this->setDatabase($database);
		$this->setGeneratedNamespace("\\Parm\\Dao\\");
	}
	
	function setDatabase($database)
	{
		if($database instanceof \Parm\Database)
		{
			$this->database = $database;
		}
		else
		{
			throw new \Exception('Database must be a Parm\Database');
		}
		
		$this->setDatabaseNode($database->getMaster());
	}
	
	function setDatabaseNode($databaseNode)
	{
		if($databaseNode instanceof \Parm\DatabaseNode)
		{
			$this->databaseNode = $databaseNode;
		}
		else
		{
			throw new \Exception('DatabaseNode must be a Parm\DatabaseNode');
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
	
	private function getColumns($tableName)
	{
		$dp = new \Parm\DatabaseProcessor($this->databaseNode);
		$dp->setSQL("SHOW COLUMNS FROM " . $dp->escapeString($this->databaseNode->serverDatabaseName) . "." . $dp->escapeString($tableName));
		return $dp->getArray();
	}
	
	private function getTemplatingDataFromTableName($tableName)
	{
		
		$idFieldName = '';
		$defaultValuePack = array();
		$fieldsPack = array();
		$bindingsPack = array();
		
		$className = ucfirst(\Parm\DataAccessArray::columnToCamelCase($tableName));
		
		// get columns
		$columns = $this->getColumns($tableName);
		
		// id field
		if($columns != null && count($columns) > 0)
		{
			foreach($columns as $key => $column)
			{
				if($column['Key'] == "PRI")
				{
					$columns[$key]['primaryKey'] = 1;
					$columns[$key]['notPrimaryKey'] = 0;
					$idFieldName = $column['Field'];
				}
				else
				{
					$columns[$key]['primaryKey'] = 0;
					$columns[$key]['notPrimaryKey'] = 1;
				}
				
				$fieldsPack[] = "'" . $column['Field'] . "'";
				
				$columns[$key]['FieldCase'] = ucfirst(\Parm\DataAccessArray::columnToCamelCase($column['Field']));
				
				if($column['Type'] == "datetime" || $column['Type'] == "date")
				{
					$columns[$key]['dateTimeField'] = 1;
				}
				else
				{
					$columns[$key]['dateTimeField'] = 0;
				}
				
				if($column['Default'] == null)
				{
					$defaultValuePack[] = "'" . $column['Field'] . "' => null";
				}
				else
				{
					$defaultValuePack[] = "'" . $column['Field'] . "' => '" . str_replace("'","\'",$column['Default']) . "'";
				}
				
				if($column['Type'] == "tinyint(1)" || $column['Type'] == "int(1)")
				{
					$bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\DataAccessArray::columnToCamelCase($column['Field'])) . "TrueBinding(){ \$this->addBinding(new TrueBooleanBinding('" . $tableName . "." . $column['Field'] . "')); }";
					$bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\DataAccessArray::columnToCamelCase($column['Field'])) . "FalseBinding(){ \$this->addBinding(new FalseBooleanBinding('" . $tableName . "." . $column['Field'] . "')); }";
					$bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\DataAccessArray::columnToCamelCase($column['Field'])) . "NotTrueBinding(){ \$this->addBinding(new NotEqualsBinding('" . $tableName . "." . $column['Field'] . "',1)); }";
					$bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\DataAccessArray::columnToCamelCase($column['Field'])) . "NotFalseBinding(){ \$this->addBinding(new NotEqualsBinding('" . $tableName . "." . $column['Field'] . "',0));  }";
					$bindingsPack[] = "\n";
				}
			}
		}
		
		return array(	'tableName' => $tableName,
						'variableName' => \Parm\DataAccessArray::columnToCamelCase($tableName),
						'className' => $className,
						'databaseName' => $this->databaseNode->serverDatabaseName,
						'idFieldName' => $idFieldName,
						'nameSpace' => $this->generatedNamespace,
						'columns' => $columns,
						'defaultValuePack' => implode(", ", $defaultValuePack),
						'fieldList' => implode(", ", $fieldsPack),
						'bindingsPack' => implode("\n", $bindingsPack)
		);
		
	}
	
	
	function generate()
	{
		if($this->destinationDirectory == null)
		{
			throw new \Exception('Destination directory required');
		}
		
		if(!file_exists($this->destinationDirectory))
		{
			if(!@mkdir($this->destinationDirectory))
			{
				throw new \Exception('Unable to create database destination directory "' . htmlentities($this->destinationDirectory) . '".');
			}
			try
			{
				chmod($this->destinationDirectory,self::DestinationDirectoryFolderPermissions);
			}
			catch(Exception $e)
			{
				throw new \Exception('Unable to make database destination directory "' . htmlentities($this->destinationDirectory) . '" writeable.');
			}
		}
		
		$files = glob($this->destinationDirectory.'/*.php'); 

		if($files != null)
		{
			foreach($files as $file)
			{
				@unlink($file); 
			}
		}

		$tableNames = $this->getTableNames();
		
		$globalNamespaceData = array();
		
		if($tableNames != null && count($tableNames) > 0)
		{
			foreach($tableNames as $tableName)
			{
				$data = $this->getTemplatingDataFromTableName($tableName);
				
				$globalNamespaceData[] = array("className" => $data['className'],
								"nameSpace" => $this->generatedNamespace);
				
				$m = new \Mustache_Engine;
				
				$this->writeContentsToFile( rtrim($this->destinationDirectory,'/') . '/' . $data['className'] . 'DaoObject.php' , $m->render(file_get_contents(dirname(__FILE__) . '/templates/dao_object.mustache'),$data));
				
				$this->writeContentsToFile( rtrim($this->destinationDirectory,'/') . '/' . $data['className'] . 'DaoFactory.php' , $m->render(file_get_contents(dirname(__FILE__) . '/templates/dao_factory.mustache'),$data));
				
			}
			
			// global namespace file
			$this->writeContentsToFile( rtrim($this->destinationDirectory,'/') . '/use_global_namespace.php' , $m->render(file_get_contents(dirname(__FILE__) . '/templates/use_global_namespace.mustache'),array("tables" => $globalNamespaceData)));
			
			//autoloader
			$this->writeContentsToFile( rtrim($this->destinationDirectory,'/') . '/autoload.php' , $m->render(file_get_contents(dirname(__FILE__) . '/templates/autoload.mustache'),array("nameSpace" => $this->generatedNamespace, "nameSpaceLength" => (strlen($this->generatedNamespace) + 1), "DIRECTORY_SEPARATOR" => DIRECTORY_SEPARATOR  )));
		}
		else
		{
			throw new \Exception("No tables in database.");
		}
		
	}
	
	private function writeContentsToFile($fileName,$contents)
	{
		if(file_exists($fileName) && !is_writable($fileName))
		{
			throw new \Exception('File is unwritable: ' . $fileName);
		}
		else if(@file_put_contents($fileName,$contents) !== FALSE)
		{
			try
			{
				@chmod($fileName,self::GenereatedCodeFilePermissions);
			}
			catch(Exception $e)
			{
				throw new \Exception('Unable to make file "' . htmlentities($fileName) . '" read/write by all.');
			}
			return true;
		}
		else
		{
			throw new \Exception('Unable to write file: ' . htmlentities($fileName));
		}
	}
}




?>