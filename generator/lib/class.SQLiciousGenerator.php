<?php

require_once('mustache/mustache.inc.php');

class SQLiciousGeneratorDatabase
{
	function __construct($config)
	{
		$node = $config->getMaster();
		
		$this->setDatabaseName($node->serverDatabaseName);
		$this->setDatabaseHost($node->serverHost);
		$this->setDatabaseUsername($node->serverUsername);
		$this->setDatabasePassword($node->serverPassword);
		$this->setGeneratorDestinationDirectory($config->getGeneratorCodeDestinationDirectory());
	}
	
	function setDatabaseName($val) { $this->databaseName = $val; }
	function getDatabaseName() { return $this->databaseName; }
	
	function setDatabaseHost($val) { $this->databaseHost = $val; }
	function getDatabaseHost() { return $this->databaseHost; }
	
	function setDatabasePassword($val) { $this->databasePassword = $val; }
	function getDatabasePassword() { return $this->databasePassword; }
	
	function setDatabaseUsername($val) { $this->databaseUsername = $val; }
	function getDatabaseUsername() { return $this->databaseUsername; }	
	
	function setGeneratorDestinationDirectory($val) { $this->generatorDestinationDirectory = $val; }
	function getGeneratorDestinationDirectory() { return $this->generatorDestinationDirectory; }
	
	function getTableNames()
	{
		$data = $this->sqliciousQuery('SHOW TABLES');
		
		$tableNames = array();
		
		if($data != null && count($data) > 0)
		{
			foreach($data as $d)
			{
				$tableNames[] = $d['Tables_in_'.$this->getDatabaseName()];
			}
		}
		
		return $tableNames;
	}
	
	function sqliciousQuery($sql)
	{
		$dp = new DatabaseProcessor($this->getDatabaseName());
		$dp->setSQL($sql);
		return $dp->getArray();
	}
	
	function getDaoObjectClassContents($tableName)
	{
		$m = new Mustache_Engine();
		return $m->render(file_get_contents(SQLICIOUS_INCLUDE_PATH.'/generator/lib/templates/dao_object.template'),$this->getTemplatingDataFromTableName($tableName));
	}
	
	function getDaoFactoryClassContents($tableName)
	{
		$m = new Mustache_Engine();
		return $m->render(file_get_contents(SQLICIOUS_INCLUDE_PATH.'/generator/lib/templates/dao_factory.template'),$this->getTemplatingDataFromTableName($tableName));
	}
	
	function getExtendedObjectStub($tableName)
	{
		$m = new Mustache_Engine();
		return $m->render(file_get_contents(SQLICIOUS_INCLUDE_PATH.'/generator/lib/templates/extended_stub.template'),$this->getTemplatingDataFromTableName($tableName));
	}
	
	function getObjectCreationCode($tableName)
	{
		//return print_r($this->getTemplatingDataFromTableName($tableName),true);
		$m = new Mustache_Engine();
		return $m->render(file_get_contents(SQLICIOUS_INCLUDE_PATH.'/generator/lib/templates/object_creation.template'),$this->getTemplatingDataFromTableName($tableName));
	}
	
	function getTableStructureHTML($tableName)
	{
		$m = new Mustache_Engine();
		return $m->render(file_get_contents(SQLICIOUS_INCLUDE_PATH.'/generator/lib/templates/table_structure_html.template'),$this->getTemplatingDataFromTableName($tableName));
	}
	
	function getTemplatingDataFromTableName($tableName)
	{
		$idFieldName = '';
		$defaultValuePack = array();
		$fieldsPack = array();
		$bindingsPack = array();
		
		$className = ucfirst(SQLiciousGenerator::toFieldCase($tableName));
		
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
				
				$columns[$key]['FieldCase'] = ucfirst(SQLiciousGenerator::toFieldCase($column['Field']));
				
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
					$bindingsPack[] = "\tfinal function add" . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . "TrueBinding(){ \$this->addBinding(new TrueBooleanBinding('" . $tableName . "." . $column['Field'] . "')); }";
					$bindingsPack[] = "\tfinal function add" . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . "FalseBinding(){ \$this->addBinding(new FalseBooleanBinding('" . $tableName . "." . $column['Field'] . "')); }";
					$bindingsPack[] = "\tfinal function add" . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . "NotTrueBinding(){ \$this->addBinding(new NotEqualsBinding('" . $tableName . "." . $column['Field'] . "',1)); }";
					$bindingsPack[] = "\tfinal function add" . ucfirst(SQLiciousGenerator::toFieldCase($column['Field'])) . "NotFalseBinding(){ \$this->addBinding(new NotEqualsBinding('" . $tableName . "." . $column['Field'] . "',0));  }";
					$bindingsPack[] = "\n";
				}
			}
		}
		
		return array( 'tableName' => $tableName,
					   'variableName' => SQLiciousGenerator::toFieldCase($tableName),
					   'className' => $className,
					   'databaseName' => $this->getDatabaseName(),
					   'idFieldName' => $idFieldName,
					   'columns' => $columns,
					   'defaultValuePack' => implode(", ",$defaultValuePack),
					   'fieldList' => implode(", ", $fieldsPack),
					   'bindingsPack' => implode("\n", $bindingsPack)
				    );
		
	}
	
	
	
	function getColumns($tableName)
	{
		$dp = new DatabaseProcessor($this->getDatabaseName());
		$dp->setSQL("SHOW COLUMNS FROM " . $dp->escapeString($tableName) . "");
		return $dp->getArray();
	}
	
}


class SQLiciousGenerator
{
	var $databases = array();
	var $generatedFileNameFormat = 'class.{{className}}DaoObject.php';
	
	function __construct()
	{
		
	}
	
	function addDatabase($sqliciousDatabase)
	{
		$this->databases[$sqliciousDatabase->getDatabaseName()] = $sqliciousDatabase;
	}
	
	function generate()
	{
		foreach($this->databases as $database)
		{
			if(!$this->generateDatabaseDestinationDirectory($database))
			{
				return false;
			}
			
			if(!$this->cleanDatabaseDestinationDirectory($database))
			{
				return false;
			}
			
			if(!$this->generateTableClasses($database))
			{
				return false;
			}
		}
		
		// methods succeeded
		return true;
	}
	
	function cleanDatabaseDestinationDirectory($database)
	{
		$files = glob($database->getGeneratorDestinationDirectory().'/*.php'); 
		
		if($files != null)
		{
			foreach($files as $file)
			{
				@unlink($file); 
			}
		}
		
		return true;
	}
	
	function setGeneratedFileNameForma($format)
	{
		$this->generatedFileNameFormat = $format;
	}
	
	function generateTableClasses($database)
	{
		$tables = $database->getTableNames();
		
		if($tables != null && count($tables) > 0)
		{
			foreach($tables as $tableName)
			{
				$className = ucfirst($this->toFieldCase($tableName));
				
				$m = new Mustache_Engine();
				if(!$this->writeContents($database->getGeneratorDestinationDirectory().'/'.$m->render($this->generatedFileNameFormat, array("tableName"=>$tableName,"className"=>$className)),$database->getDaoObjectClassContents($tableName)))
				{
					return false;
				}
			}
			
			return true;
		}
		else
		{
			$this->setErrorMessage("No tables in database.");
			return false;
		}
	}
	
	function generateDatabaseDestinationDirectory($database)
	{
		if($database->getGeneratorDestinationDirectory() != "")
		{
			if(!file_exists($database->getGeneratorDestinationDirectory()))
			{
				if(!@mkdir($database->getGeneratorDestinationDirectory()))
				{
					$this->setErrorMessage('Unable to create database destination directory "' . htmlentities($database->getGeneratorDestinationDirectory()) . '".');
					return false;
				}
				
				try
				{
					chmod($database->getGeneratorDestinationDirectory(),0777);
					chown($database->getGeneratorDestinationDirectory(),'_www');
				}
				catch(Exception $e)
				{
					// do nothing
				}
				
			}
			else
			{
				// folder exists
				return true;
			}
		}
		else
		{
			$this->setErrorMessage('Database destination directory not specified.');
			return false;
		}
	}
	
	function writeContents($fileName,$contents)
	{
		if(file_exists($fileName) && !is_writable($fileName))
		{
			$this->setErrorMessage('File is unwritable: ' . $fileName);
			return false;
		}
		else if(@file_put_contents($fileName,$contents) !== FALSE)
		{
			try
			{
				@chmod($fileName,0777);
			}
			catch(Exception $e)
			{
				// do nothing
			}
			
			return true;
		}
		else
		{
			$this->setErrorMessage('Unable to write file: ' . $fileName);
			return false;
		}
	}
	
	static function jsonEncode($array)
	{
		return json_encode(self::utf8EncodeArray($array));
	}
	
	static function utf8EncodeArray($array)
	{
	    foreach($array as $key => $value)
	    {
	    	if(is_array($value))
	    	{
	    		$array[$key] = self::utf8EncodeArray($value);
	    	}
	    	else
	    	{
	    		$array[$key] = utf8_encode($value);
	    	}
	    }
	       
	    return $array;
	}
	
	static function toFieldCase($val)
	{
		$result = '';
		
		$segments = explode("_", $val);
		for ($i = 0; $i < count($segments); $i++)
		{
			$segment = $segments[$i];
			if ($i == 0)
				$result .= $segment;
			else
				$result .= strtoupper(substr($segment, 0, 1)).substr($segment, 1);
		}
		return $result;
		
	}
	
	function setErrorMessage($val) { $this->errorMessage = $val; }
	function getErrorMessage() { return $this->errorMessage; }
	
}

?>