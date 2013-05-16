<?php

namespace Parm;

use \ArrayAccess;

class DataAccessArray implements ArrayAccess
{
	protected $data = array();

	function __construct($row)
	{
		$this->__data = $row;
	}

	public function offsetSet($offset, $value)
	{
		if(is_null($offset))
		{
			$this->__data[] = $value;
		}
		else
		{
			$this->__data[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this->__data[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->__data[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this->__data[$offset]) ? $this->__data[$offset] : null;
	}

	function getFieldValue($fieldName)
	{
		if (array_key_exists($fieldName, $this->__data))
		{
			return $this->__data[$fieldName];
		}
		else
		{
			throw new Parm\GetFieldValueException($fieldName . ' not initilized for get method in ' . get_class($this));
		}
	}

	// returns an associative array of the row retrieved from the database
	function toArray()
	{
		return $this->__data;
	}

	// returns an associative array with camel case array keys for use in javascript
	function toJSON()
	{
		$json = array();
		if ($this->__data != null)
		{
			foreach ($this->__data as $field => $value)
			{
				$json[self::toFieldCase($field)] = $value;
			}
		}

		return $json;
	}

	// returns a string 
	function toJSONString()
	{
		return self::JSONEncodeArray($this->toJSON());
	}

	// utils
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
				$result .= strtoupper(substr($segment, 0, 1)) . substr($segment, 1);
		}
		return $result;
	}

	static function JSONEncodeArray($array)
	{
		return json_encode(self::utf8EncodeArray($array));
	}

	static function utf8EncodeArray($array)
	{
		foreach ($array as $key => $value)
		{
			if (is_array($value))
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

}

?>