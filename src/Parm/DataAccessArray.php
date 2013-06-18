<?php
/*
 * This file is part of the Parm package.
 *
 * (c) Andrew Cassell <me@andrewcassell.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Parm;

/**
 * DataAccessArray is used for creating an object wrapper around an array.
 * The DatabaseProcessor returns DataAccessArray objects when the getArray function is called.
 * The DataAccessObject extends this class.
 */
class DataAccessArray implements \ArrayAccess
{
	protected $__data = array();

	/**
	 * Constructor
     * @param array $row Array of data
     */
	function __construct($row)
	{
		$this->__data = $row;
		
		return $this;
	}
	
	
	/**
	 * Whether a offset exists
	 * @param mixed $offset  An offset to check for.
	 * @return boolean TRUE on success or FALSE on failure.
	 * The return value will be casted to boolean if non-boolean was returned.
	 */
	public function offsetExists($offset)
	{
		return isset($this->__data[$offset]);
	}

	
	/**
	 * Offset to retrieve
	 * @param mixed $offset The offset to retrieve.
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset)
	{
		return isset($this->__data[$offset]) ? $this->__data[$offset] : null;
	}

	
	/**
	 * Offset to set
	 * @param mixed $offset The offset to assign the value to.
	 * @param mixed $value The value to set.
	 * @return void No value is returned.
	 */
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

	
	/**
	 * Offset to unset
	 * @param mixed $offset The offset to unset.
	 * @return void No value is returned.
	 */
	public function offsetUnset($offset)
	{
		unset($this->__data[$offset]);
	}

	
	/**
     * Get the value of a column from the database row
	 * @param string $fieldName Name of the field/column in the database
     * @return string|null The value of the field
     */
	function getFieldValue($fieldName)
	{
		if (array_key_exists($fieldName, $this->__data))
		{
			return $this->__data[$fieldName];
		}
		else
		{
			throw new \Parm\Exception\GetFieldValueException($fieldName . ' not initilized for get method in ' . get_class($this));
		}
	}

	
	/**
	 * Get the array of data
     * @return array An associative array of the row retrieved from the database
     */
	function toArray()
	{
		return $this->__data;
	}
	
	
	/**
	 * Convert to a JSON ready array
     * @return array An associative array with camel case array keys
     */
	function toJSON()
	{
		$json = array();
		if ($this->__data != null)
		{
			foreach ($this->__data as $field => $value)
			{
				$json[self::columnToCamelCase($field)] = $value;
			}
		}

		return $json;
	}

	
	/**
	 * Convert to a JSON string
     * @return string The row formatted in JSON
     */
	function toJSONString()
	{
		return json_encode(self::utf8EncodeArray($this->toJSON()));
	}

	
	/**
	 * Encode the values of an array to UTF-8
     * @return string A column name with underscores converted to camel case. Example: "first_name" becomes "firstName", "first_born_child_id" becomes "firstBornChildId"
     */
	static protected function columnToCamelCase($columnName)
	{
		$result = '';

		$segments = explode("_", $columnName);
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

	
	/**
	 * Encode the values of an array to UTF-8
     * @return array with UTF-8 Encoded values
     */
	static protected function utf8EncodeArray($array)
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