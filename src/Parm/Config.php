<?php

namespace Parm;

/**
 * Class Config
 * @package Parm
 */
class Config
{
	/**
	 * @var array
	 */
	public static $databases = Array();

	/**
	 * @param string $name
	 * @param DatabaseNode $masterNode
	 */
	static function addDatabase($name, DatabaseNode $masterNode)
	{
		static::$databases[$name] = new Database();
		static::$databases[$name]->setMaster($masterNode);
	}

	/**
	 * @param $name
	 * @return Database
	 */
	static function getDatabase($name)
	{
		return static::$databases[$name];
	}

	/**
	 * @param $name
	 * @return Database
	 */
	static function getDatabaseMaster($name)
	{
		return static::$databases[$name]->getMaster();
	}

	/**
	 * @return Database
	 */
	static function __getFirstDatabaseMaster()
	{
		return static::getDatabaseMaster(reset(array_keys(static::$databases)));
	}

}