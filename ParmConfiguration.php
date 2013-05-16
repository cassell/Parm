<?php

class ParmConfiguration implements ArrayAccess
{
	var $databases = array();
	
	function __construct(){ }
	
	function addDatabaseConfiguration($name,$configuration)
	{
		$this->databases[$name] = $configuration;
	}
	
	function getDatabases()
	{
		return $this->databases;
	}
	
	public function offsetSet($offset, $value)
	{
        if (is_null($offset))
		{
            $this->databases[] = $value;
        }
		else
		{
            $this->databases[$offset] = $value;
        }
    }
	
    public function offsetExists($offset)
	{
        return isset($this->databases[$offset]);
    }
	
    public function offsetUnset($offset)
	{
        unset($this->databases[$offset]);
    }
	
    public function offsetGet($offset)
	{
        return isset($this->databases[$offset]) ? $this->databases[$offset] : null;
    }
	
	// returns an associative array of the row retrieved from the database
	function toArray()
	{
		return $this->databases;
	}
	
}

?>