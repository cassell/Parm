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
    public static function addDatabase($name, DatabaseNode $masterNode)
    {
        static::$databases[$name] = new Database();
        static::$databases[$name]->setMaster($masterNode);
    }

    /**
     * @param $name
     * @return Database
     */
    public static function getDatabase($name)
    {
        return static::$databases[$name];
    }

    /**
     * @param $name
     * @return Database
     */
    public static function getDatabaseMaster($name)
    {
        return static::$databases[$name]->getMaster();
    }

    /**
     * @return Database
     */
    public static function __getFirstDatabaseMaster()
    {
        $arrayKeys = array_keys(static::$databases);
        return static::getDatabaseMaster(reset($arrayKeys));
    }

}
