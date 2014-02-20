<?php

namespace Parm;

class ParmConfig
{
	public static $databases = [];

	static function addDatabase($name, DatabaseNode $masterNode)
	{
		static::$databases[$name] = new Database();
		static::$databases[$name]->setMaster($masterNode);
	}

	static function getDatabase($name)
	{
		return static::$databases[$name];
	}

	static function getDatabaseMaster($name)
	{
		return static::$databases[$name]->getMaster();
	}

	static function __getFirstDatabaseMaster()
	{
		return static::getDatabaseMaster(reset(array_keys(static::$databases)));
	}

}