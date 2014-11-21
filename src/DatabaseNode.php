<?php

namespace Parm;

abstract class DatabaseNode
{
    public $serverDatabaseName;
    public $serverHost;
    public $serverUsername;
    public $serverPassword;
    public $serverPort;
    public $serverSocket;
    public $serverCharset = 'utf8'; // default utf8
    public $serverCaseSensitiveCollation = 'utf8_bin'; // default utf8_bin

    const DATE_STORAGE_FORMAT = 'Y-m-d';
    const DATETIME_STORAGE_FORMAT = 'Y-m-d H:i:s';

    abstract public function getConnection();

    abstract public function getLastInsertId();

    abstract public function query($queryString);

    abstract public function closeConnection();

    public function getDateTimeStorageFormat()
    {
        return static::DATETIME_STORAGE_FORMAT;
    }

    public function getDateStorageFormat()
    {
        return static::DATE_STORAGE_FORMAT;
    }

}
