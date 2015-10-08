<?php

namespace Parm;
use Parm\Exception\ErrorException;

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
    private static $characterSet = 'utf8';
    private static $caseSensitiveCollation = 'utf8_bin';

    /**
     * @param string $name
     */
    public static function setupConnection($name, $databaseNameOnServer, $databaseUser, $databasePassword, $databaseHost)
    {
        self::addConnection($name, new \Doctrine\DBAL\Connection([
            'dbname' => $databaseNameOnServer,
            'user' => $databaseUser,
            'password' => $databasePassword,
            'host' => $databaseHost,
            'driver' => 'pdo_mysql',
            'charset' => static::getCharacterSet()
        ], new \Doctrine\DBAL\Driver\PDOMySql\Driver(), null, null));
    }

    /**
     * @param string                    $name
     * @param \Doctrine\DBAL\Connection $connection
     */
    public static function addConnection($name, \Doctrine\DBAL\Connection $connection)
    {
        if ($connection->getDriver() instanceof \Doctrine\DBAL\Driver\PDOMySql\Driver) {
            static::$connections[$name] = $connection;
        } else {
            throw new ErrorException("Database connection must use a \\Doctrine\\DBAL\\Driver\\PDOMySql\\Driver Driver");
        }
    }

    /**
     * @param $name
     * @return \Doctrine\DBAL\Connection $connection
     * @throws ErrorException
     */
    public static function getConnection($name)
    {
        if (array_key_exists($name,static::$connections)) {
            return static::$connections[$name];
        }
        else {
            throw new ErrorException("Database connection not found in Config");
        }
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
        return self::$caseSensitiveCollation;
    }

    /**
     * @param string $caseSensitiveCollation
     */
    public static function setCaseSensitiveCollation($caseSensitiveCollation)
    {
        self::$caseSensitiveCollation = $caseSensitiveCollation;
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

    /**
     * @return string
     */
    public static function getCharacterSet()
    {
        return self::$characterSet;
    }

    /**
     * @param string $characterSet
     */
    public static function setCharacterSet($characterSet)
    {
        self::$characterSet = $characterSet;
    }



}
