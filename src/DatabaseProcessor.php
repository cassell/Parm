<?php

namespace Parm;

use Doctrine\DBAL\Statement;
use Parm\Exception\ErrorException;
use Parm\Exception\TimezoneConversionException;
use Parm\Exception\UpdateFailedException;

class DatabaseProcessor
{
    public $databaseNode;
    protected $sql = null;
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * @param \Doctrine\DBAL\Connection|string $connection The database to connect to
     */
    public function __construct($connection)
    {
        // setup node
        if ($connection instanceof \Doctrine\DBAL\Connection) {
            $this->connection = $connection;
        } elseif (is_string($connection)) {
            $this->connection = Config::getConnection($connection);

            if ($this->connection == null) {
                throw new \Parm\Exception\ErrorException("Unable to find database named " . \htmlentities($connection) . " in \\Parm\\Config configuration.");
            }
        }
    }

    /**
     * @return Rows
     */
    public function query()
    {
        return $this->getRows();
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
     *
     * @param  string $columnName The name of the column to select from
     * @return array
     */
    public function getFirstField($columnName)
    {
        $a = $this->getArray();

        if (is_array($a)) {
            $a = reset($a);

            return $a[$columnName];
        }
    }

    /**
     * Get a single dimension array of values
     *
     * @return array
     */
    public function getSingleColumnArray($columnName = null)
    {
        $data = array();

        $conn = $this->databaseNode->getConnection();

        $result = $this->getResult($this->getSQL(), $conn);

        if ($result != null) {
            if ($this->getNumberOfRowsFromResult($result) > 0) {
                $result->data_seek(0);

                while ($row = $result->fetch_array($columnName ? MYSQLI_ASSOC : MYSQLI_NUM)) {
                    $data[] = $row[$columnName ? $columnName : 0];
                }
            }
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

//    public function prepare($sql,$params = [])
//    {
//        $this->setSQL($this->connection->prepare($sql))
//
//    }

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
     */
    public function update($sql)
    {
        if ($this->getResult($sql) != null) {
            return true;
        } else {
            throw new UpdateFailedException($sql);
        }
    }

    public function executeMultiQuery()
    {
        $conn = $this->databaseNode->getConnection();

        $conn->multi_query($this->getSQL());

        do {
            if ($conn->errno != 0) {
                throw new \Parm\Exception\ErrorException("Parm DatabaseProcessor multiQuery SQL Error. Reason given " . $conn->error);
            }

            if (!$conn->more_results() || (!$conn->next_result() && $conn->error == null)) {
                break;
            }

        } while (true);

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
     * Get the id of the last inserted object from the database node
     *
     * @param  string $sql The SQL to execute
     * @return mysql  result
     */
    public function getLastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Convert a datetime from one timezone to another using the MySQL database as the timezone source. Use the "US/Eastern" format or "Europe/London" formats
     *
     * @param  timestamp|string|DateTime $dateTime       The datetime in the source timezone
     * @param  string                    $sourceTimezone The source timezone. "US/Eastern" mysql format (mysql.time_zone_name)
     * @param  string                    $destTimezone   The destination timezone. "US/Eastern" mysql format (mysql.time_zone_name)
     * @return \DateTime
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

        $this->setSQL("SELECT CONVERT_TZ('" . $dateTimeObject->format($this->databaseNode->getDatetimeStorageFormat()) . "','" . $this->escapeString($sourceTimezone) . "','" . $this->escapeString($destTimezone) . "') as convertTimezone;");

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
     * Free a mysqli_result
     */
    public function freeResult(\mysqli_result $result)
    {
        $result->free();
    }

    /**
     * Output a JSON string using a real_query from the SQL that has been set using setSQL($sql)
     */
    public function outputJSONString()
    {
        echo "[";

        $firstRecord = true;

        foreach($this->getRows() as $row)
        {
            if (!$firstRecord) {
                echo ",";
            } else {
                $firstRecord = false;
            }

            echo $row->toJSONString();
        }

        echo "]";

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

}
