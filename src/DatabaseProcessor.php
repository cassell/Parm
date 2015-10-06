<?php

namespace Parm;

use Doctrine\DBAL\Statement;
use Parm\Exception\ErrorException;
use Parm\Exception\TimezoneConversionException;
use Parm\Exception\UpdateFailedException;

class DatabaseProcessor implements \Serializable
{
    protected $sql = null;
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @param  \Doctrine\DBAL\Connection|string $connection The database to connect to
     * @throws ErrorException
     */
    public function __construct($connection)
    {
        // setup node
        if ($connection instanceof \Doctrine\DBAL\Connection) {
            $this->connection = $connection;
        } else {
            $this->connection = Config::getConnection($connection);
        }
    }

    /**
     * @return Rows
     */
    public function getRows()
    {
        return new Rows($this);
    }

    /**
     * Get the rows as an associative array
     *
     * @return array
     */
    public function getArray()
    {
        $rows = $this->getRows();

        $data = $rows->toArray();

        return $data;
    }

    /**
     * Get the rows as an associative array JSON-ified with camelCase array keys
     * @return array
     */
    public function getJSON()
    {
        $rows = $this->getRows();

        $json = $rows->toJson();

        return $json;
    }

    /**
     * Get the first value from a single column from the database
     * @param  string         $columnName
     * @return string
     * @throws ErrorException
     */
    public function getFirstField($columnName)
    {
        $a = current($this->getArray());

        if (!array_key_exists($columnName,$a)) {
            throw new ErrorException("getFirstField: columnName was not in the result");
        }

        return $a[$columnName];

    }

    /**
     * Get a single dimension array of values
     *
     * @return array
     */
    public function getSingleColumnArray($columnName = null)
    {
        $data = [];

        foreach ($this->getArray() as $row) {
            if ($columnName != null && !array_key_exists($columnName,$row)) {
                throw new ErrorException("getSingleColumnArray: columnName was not in the result");
            }

            $data[] = $row[$columnName ? $columnName : 0];

        }

        return $data;
    }

    /**
     * Set the SQL to process
     *
     * @param  string            $sql
     * @return DatabaseProcessor
     */
    public function setSQL($sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * Get the SQL that has been set
     * @return string
     */
    public function getSQL()
    {
        return $this->sql;
    }

    /**
     * Build a data object from the row data. This function is overridden in the factories.
     *
     * @param  array $row The associative array of data
     * @return Row
     */
    public function loadDataObject(Array $row)
    {
        return new Row($row);
    }

    /**
     * Loop through the rows of a query and process with a closure
     *
     * @param  callable          $closure Closure to process the rows of the database retrieved with, the closure is passed a Row or DataAccessObject
     * @return DatabaseProcessor This DatabaseProcessor so you can chain it
     */
    public function process($closure)
    {
        $rows = $this->getRows();

        foreach ($rows as $row) {
            $closure($row);
        }

        return $this;
    }

    /**
     * Get the number of rows for a query from the MySQL database via the result
     *
     * @param  \Doctrine\DBAL\Driver\Statement $result
     * @return int                             The number of rows reported from the database
     */
    public function getNumberOfRowsFromResult(\Doctrine\DBAL\Driver\Statement $result)
    {
        return (int) $result->rowCount();
    }

    /**
     * Execute a sql update
     *
     * @param string $sql The SQL to execute
     * @throws UpdateFailedException
     */
    public function update($sql)
    {
        try {
            $this->getResult($sql);
        } catch (\Parm\Exception\ErrorException $e) {
            throw new \Parm\Exception\UpdateFailedException($sql);
        }

    }

    /**
     * @param $sql
     * @return \Doctrine\DBAL\Driver\Statement
     * @throws ErrorException
     */
    public function getResult($sql)
    {
        try {
            return $this->connection->query($sql);
        } catch (\Doctrine\DBAL\DBALException $e) {
            throw new \Parm\Exception\ErrorException("DatabaseProcessor SQL Error. MySQL Query Failed: " . htmlentities($sql) . '. Reason given ' . $e);
        }
    }

    /**
     * Get the id of the last inserted object
     *
     * @return string
     */
    public function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Convert a datetime from one timezone to another using the MySQL database as the timezone source. Use the "US/Eastern" format or "Europe/London" formats
     *
     * @codeCoverageIgnore
     * @param  int|string|\DateTime        $dateTime       The datetime in the source timezone
     * @param  string                      $sourceTimezone The source timezone. "US/Eastern" mysql format (mysql.time_zone_name)
     * @param  string                      $destTimezone   The destination timezone. "US/Eastern" mysql format (mysql.time_zone_name)
     * @return \DateTime
     * @throws ErrorException
     * @throws TimezoneConversionException
     */
    public function convertTimezone($dateTime, $sourceTimezone, $destTimezone)
    {
        if ($dateTime === NULL) {
            return NULL;
        } elseif ($dateTime instanceof \DateTime) {
            $dateTimeObject = $dateTime;
        } elseif (is_numeric($dateTime)) {
            $dateTimeObject = new \DateTime();
            $dateTimeObject->setTimestamp($dateTime);
        } else {
            $dateTimeObject = new \DateTime($dateTime);
        }

        $this->setSQL("SELECT CONVERT_TZ('" . $dateTimeObject->format(Config::getDatetimeStorageFormat()) . "','" . $this->escapeString($sourceTimezone) . "','" . $this->escapeString($destTimezone) . "') as convertTimezone;");

        $result = $this->getSingleColumnArray("convertTimezone");
        if (is_array($result)) {
            $val = $result[0];
            if ($val != "") {
                return new \DateTime(reset($result));
            } else {
                throw new TimezoneConversionException("Timezone conversion failed. Possible invalid timezone.");
            }
        } else {
            throw new TimezoneConversionException("Timezone conversion failed");
        }
    }

    /**
     * @return string
     */
    public function getJsonString()
    {
        return json_encode($this->getJSON());
    }

    /**
     * @codeCoverageIgnore
     * Output a JSON string using a real_query from the SQL that has been set using setSQL($sql)
     */
    public function outputJSONString()
    {
        echo $this->getJsonString();

        return true;
    }

    /**
     * Escape a string to prevent mysql injection
     */
    public function escapeString($string)
    {
        return $this->connection->quote($string);
    }

    public function getDateStorageFormat()
    {
        return Config::getDateStorageFormat();
    }

    public function getDatetimeStorageFormat()
    {
        return Config::getDatetimeStorageFormat();
    }

    /**
     * Useful for replacing mysql_real_escape_string in old code with DatabaseProcessor::mysql_real_escape_string()
     */
    public static function mysql_real_escape_string($string)
    {
        $dp = new DatabaseProcessor(current(Config::getAllConnections()));

        return $dp->escapeString($string);
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        throw new \RuntimeException("You can not serialize a DatabaseProcessor because it contains an open database connection.");
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param  string $serialized <p>
     *                            The string representation of the object.
     *                            </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        throw new \RuntimeException("Unable to deserialize DatabaseProcessor because it requires a database connection.");
    }

}
