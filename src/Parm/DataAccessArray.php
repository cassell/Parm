<?php
/*
 * This file is part of the Symfony package.
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
 *
 */
class DataAccessArray implements \ArrayAccess
{
	protected $__data = array();

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

	/**
     * @param string $fieldName Name of the field/column in the database
     *
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
     * @return array An associative array of the row retrieved from the database
     */
	function toArray()
	{
		return $this->__data;
	}
	
	/**
     * @return array An associative array with camel case array keys. Great for exporting data to JSON.
     */
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

	/**
     * @return string The row formatted in JSON
     */
	function toJSONString()
	{
		return self::JSONEncodeArray($this->toJSON());
	}

	
	static protected function toFieldCase($val)
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

	static protected function JSONEncodeArray($array)
	{
		return json_encode(self::utf8EncodeArray($array));
	}

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