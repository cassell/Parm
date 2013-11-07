<?php

namespace Parm\Generator;

class DatabaseGenerator
{
	const DESTINATION_DIRECTORY_FOLDER_PERMISSIONS = 0777;
	const GENERATED_CODE_FILE_PERMISSIONS = 0777;
	
	var $database;
	var $databaseNode;
	var $destinationDirectory;
	var $generatedNamespace = "\\Parm\\Dao\\"; // default
	
	var $arrayOfInvalidColumnNames = array("");

	function __construct($database)
	{
		$this->setDatabase($database);
	}
	
	/**
	 * Set the database configuration to use the master from
     * @param \Parm\Database $database The database to connect to
     */
	function setDatabase(\Parm\Database $database)
	{
		$this->setDatabaseNode($database->getMaster());
	}
	
	/**
	 * Set the database node to use
     * @param \Parm\DatabaseNode $databaseNode The database to connect to
     */
	function setDatabaseNode(\Parm\DatabaseNode $databaseNode)
	{
		$this->databaseNode = $databaseNode;
	}
	
	/**
	 * Set the destination directory to generate the objects and factories into
     * @param string $directory
     */
	function setDestinationDirectory($directory)
	{
		if(is_string($directory))
		{
			$this->destinationDirectory = $directory;
		}
		else
		{
			throw new \Parm\Exception\ErrorException('setDestinationDirectory($directory) must be a string');
		}
		
	}
	
	/**
	 * The namespace to generate the objects and factories into
     * @param string $directory
     */
	function setGeneratedNamespace($namespaceString)
	{
		if(is_string($namespaceString))
		{
			$this->generatedNamespace = $namespaceString;
		}
		else
		{
			throw new \Parm\Exception\ErrorException('setGeneratedNamespace($namespaceString) must be a string');
		}
		
	}
	
	/**
	 * Use the global namespace for generated objects and factories
     */
	function useGlobalNamespace()
	{
		$this->setGeneratedNamespace("");
	}
	
	/**
	 * Generate the objects and factories
     */
	function generate()
	{
		if($this->destinationDirectory == null)
		{
			throw new \Parm\Exception\ErrorException('Destination directory required');
		}
		
		if(!file_exists($this->destinationDirectory))
		{
			if(!@mkdir($this->destinationDirectory))
			{
				throw new \Parm\Exception\ErrorException('Unable to create database destination directory "' . htmlentities($this->destinationDirectory) . '".');
			}
			try
			{
				chmod($this->destinationDirectory,self::DESTINATION_DIRECTORY_FOLDER_PERMISSIONS);
			}
			catch(\Exception $e)
			{
				throw new \Parm\Exception\ErrorException('Unable to make database destination directory "' . htmlentities($this->destinationDirectory) . '" writeable.');
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
				
				$globalNamespaceData["tables"][] = $data;
				
				$m = new \Mustache_Engine;
				
				$this->writeContentsToFile( rtrim($this->destinationDirectory,'/') . '/' . $data['className'] . 'DaoObject.php' , $m->render(file_get_contents(dirname(__FILE__) . '/templates/dao_object.mustache'),$data));
				
				$this->writeContentsToFile( rtrim($this->destinationDirectory,'/') . '/' . $data['className'] . 'DaoFactory.php' , $m->render(file_get_contents(dirname(__FILE__) . '/templates/dao_factory.mustache'),$data));
				
			}
			
			$globalNamespaceData['namespace'] = $this->generatedNamespace;
			$globalNamespaceData['escapedNamespace'] = $this->generatedNamespace != "" ? (str_replace("\\", "\\\\", $this->generatedNamespace) . "\\\\") : '';
			$globalNamespaceData['namespaceLength'] = strlen($this->generatedNamespace) + 1;
			
			// global namespace file
			if($this->generatedNamespace != "\\" && $this->generatedNamespace != "")
			{
				$this->writeContentsToFile( rtrim($this->destinationDirectory,'/') . '/alias_all_tables_to_global_namespace.php' , $m->render(file_get_contents(dirname(__FILE__) . '/templates/alias_all_tables_to_global_namespace.mustache'),$globalNamespaceData));
				
				//autoloader
				$this->writeContentsToFile( rtrim($this->destinationDirectory,'/') . '/autoload.php' , $m->render(file_get_contents(dirname(__FILE__) . '/templates/namespaced_autoload.mustache'),$globalNamespaceData));
			}
			else
			{
				$this->writeContentsToFile( rtrim($this->destinationDirectory,'/') . '/autoload.php' , $m->render(file_get_contents(dirname(__FILE__) . '/templates/global_autoload.mustache'),$globalNamespaceData));
			}
			
			
			
		}
		else
		{
			throw new \Parm\Exception\ErrorException("No tables in database.");
		}
		
	}
	
	
	/* Private Functions */
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
	
	private function getTemplatingDataFromTableName($tableName)
	{
		
		$idFieldName = '';
		$defaultValuePack = array();
		$fieldsPack = array();
		$bindingsPack = array();
		
		$className = ucfirst(\Parm\DataArray::columnToCamelCase($tableName));
		
		$dp = new \Parm\DatabaseProcessor($this->databaseNode);
		$dp->setSQL("SHOW COLUMNS FROM " . $dp->escapeString($this->databaseNode->serverDatabaseName) . "." . $dp->escapeString($tableName));
		$columns = $dp->getArray();
		
		// id field
		if($columns != null && count($columns) > 0)
		{
			foreach($columns as $key => $column)
			{
				if(in_array($column['Field'],$this->arrayOfInvalidColumnNames))
				{
					throw new \Parm\Exception\ErrorException($column['Field'] . ' is an invalid column name for the Parm\DatabaseGenerator. It causes collisions with internal functions.');
				}
				
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
				
				$columns[$key]['FieldCase'] = ucfirst(\Parm\DataArray::columnToCamelCase($column['Field']));
				
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
					$bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\DataArray::columnToCamelCase($column['Field'])) . "TrueBinding(){ \$this->addBinding(new \Parm\Binding\TrueBooleanBinding('" . $tableName . "." . $column['Field'] . "')); }";
					$bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\DataArray::columnToCamelCase($column['Field'])) . "FalseBinding(){ \$this->addBinding(new \Parm\Binding\FalseBooleanBinding('" . $tableName . "." . $column['Field'] . "')); }";
					$bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\DataArray::columnToCamelCase($column['Field'])) . "NotTrueBinding(){ \$this->addBinding(new \Parm\Binding\NotEqualsBinding('" . $tableName . "." . $column['Field'] . "',1)); }";
					$bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\DataArray::columnToCamelCase($column['Field'])) . "NotFalseBinding(){ \$this->addBinding(new \Parm\Binding\NotEqualsBinding('" . $tableName . "." . $column['Field'] . "',0));  }";
					$bindingsPack[] = "\n";
				}
			}
		}
		
		return array(	'tableName' => $tableName,
						'variableName' => \Parm\DataArray::columnToCamelCase($tableName),
						'className' => $className,
						'databaseName' => $this->databaseNode->serverDatabaseName,
						'idFieldName' => $idFieldName,
						'namespace' => $this->generatedNamespace,
						'autoloaderNamespace' => $this->generatedNamespace,
						'namespaceClassSyntax' => ($this->generatedNamespace != "" && $this->generatedNamespace != "\\") ? 'namespace ' . $this->generatedNamespace . ';' : '',
						'namespaceLength' => strlen($this->generatedNamespace),
						'columns' => $columns,
						'defaultValuePack' => implode(", ", $defaultValuePack),
						'fieldList' => implode(", ", $fieldsPack),
						'bindingsPack' => implode("\n", $bindingsPack),
		);
		
	}
	
	private function writeContentsToFile($fileName,$contents)
	{
		if(file_exists($fileName) && !is_writable($fileName))
		{
			throw new \Parm\Exception\ErrorException('File is unwritable: ' . $fileName);
		}
		else if(@file_put_contents($fileName,$contents) !== FALSE)
		{
			try
			{
				@chmod($fileName,self::GENERATED_CODE_FILE_PERMISSIONS);
			}
			catch(\Exception $e)
			{
				throw new \Parm\Exception\ErrorException('Unable to make file "' . htmlentities($fileName) . '" read/write by all.');
			}
			return true;
		}
		else
		{
			throw new \Parm\Exception\ErrorException('Unable to write file: ' . htmlentities($fileName));
		}
	}
}
