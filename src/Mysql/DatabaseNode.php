<?php

namespace Parm\Mysql;

class DatabaseNode extends \Parm\DatabaseNode
{
    public $serverDatabaseName;
    public $serverHost;
    public $serverUsername;
    public $serverPassword;
    public $serverPort;
    public $serverSocket;
    public $serverCharset = 'utf8'; // default utf8
    public $serverCaseSensitiveCollation = 'utf8_bin'; // default utf8_bin
    private $connection;

    const DATE_STORAGE_FORMAT = 'Y-m-d';
    const DATETIME_STORAGE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Create a database node
     *
     * @param string $serverDatabaseName
     * @param string $serverHost
     * @param string $serverUsername
     * @param string $serverPassword
     * @param string $serverPort
     * @param string $serverSocket
     * @param string $serverCharset
     * @param string $serverCaseSensitiveCollation
     *
     */
    public function __construct($serverDatabaseName, $serverHost, $serverUsername, $serverPassword, $serverPort = null, $serverSocket = null, $serverCharset = 'utf8', $serverCaseSensitiveCollation = 'utf8_bin')
    {
        $this->serverDatabaseName = $serverDatabaseName;
        $this->serverHost = $serverHost;
        $this->serverUsername = $serverUsername;
        $this->serverPassword = $serverPassword;
        $this->serverPort = $serverPort;
        $this->serverSocket = $serverSocket;
        $this->serverCharset = $serverCharset;
        $this->serverCaseSensitiveCollation = $serverCaseSensitiveCollation;

    }

    /**
     * Get the id of the last inserted row from the database
     * @return object mysqli connection
     */
    public function getConnection()
    {
        if ($this->connection == null) {
            $this->connection = new \mysqli($this->serverHost, $this->serverUsername, $this->serverPassword, $this->serverDatabaseName, $this->serverPort ? $this->serverPort : null, $this->serverSocket);
            $this->connection->set_charset($this->serverCharset);

        }

        if ($this->connection == null || $this->connection->connect_errno || $this->connection->client_info == "") {
            throw new \Parm\Exception\ConnectionErrorException($this->connection);
        }

        return $this->connection;
    }

    /**
     * Get the id of the last inserted row from the database
     * @return integer id of the last inserted row from the database
     */
    public function getLastInsertId()
    {
        if ($this->connection) {
            return $this->connection->insert_id;
        } else {
            throw new \Parm\Exception\ConnectionErrorException($this->connection);
        }
    }

    public function query($queryString)
    {

        //$this->connection->close();
    }

    /**
     * Close the connection to the database node
     */
    public function closeConnection()
    {
        $this->connection->close();
    }

    public function getDateTimeStorageFormat()
    {
        return static::DATETIME_STORAGE_FORMAT;
    }

    public function getDateStorageFormat()
    {
        return static::DATE_STORAGE_FORMAT;
    }

}
