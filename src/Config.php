<?php

namespace Parm;

/**
 * Class Config
 * @package Parm
 */
class Config
{
    /**
     * @var \Doctrine\DBAL\Connection[]
     */
    private static $connections = Array();
    private static $dateStorageFormat = 'Y-m-d';
    private static $datetimeStorageFormat = 'Y-m-d H:i:s';
    private static $caseSenstitiveCollation = 'utf8_bin';


    /**
     * @param string $name
     * @param \Doctrine\DBAL\Connection $connection
     */
    public static function addConnection($name, \Doctrine\DBAL\Connection $connection)
    {
        static::$connections[$name] = $connection;
    }

    /**
     * @param $name
     * @return \Doctrine\DBAL\Connection $connection
     */
    public static function getConnection($name)
    {
        return static::$connections[$name];
    }

    public static function getAllConnections()
    {
        return static::$connections;
    }

    /**
     * @return string
     */
    public static function getCaseSenstitiveCollation()
    {
        return self::$caseSenstitiveCollation;
    }

    /**
     * @param string $caseSenstitiveCollation
     */
    public static function setCaseSenstitiveCollation($caseSenstitiveCollation)
    {
        self::$caseSenstitiveCollation = $caseSenstitiveCollation;
    }

    /**
     * @return string
     */
    public static function getDateStorageFormat()
    {
        return self::$dateStorageFormat;
    }

    /**
     * @param string $dateStorageFormat
     */
    public static function setDateStorageFormat($dateStorageFormat)
    {
        self::$dateStorageFormat = $dateStorageFormat;
    }

    /**
     * @return string
     */
    public static function getDatetimeStorageFormat()
    {
        return self::$datetimeStorageFormat;
    }

    /**
     * @param string $datetimeStorageFormat
     */
    public static function setDatetimeStorageFormat($datetimeStorageFormat)
    {
        self::$datetimeStorageFormat = $datetimeStorageFormat;
    }



}
