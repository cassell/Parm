<?php

namespace Parm;

use Parm\Exception\ErrorException;
use Parm\Exception\TimezoneConversionException;

class DatabaseProcessor
{
    public $databaseNode;
    protected $sql = null;

    /**
     * @param Database|DatabaseNode|string $mixed The database to connect to
     */
    public function __construct($mixed)
    {
        // setup node
        if ($mixed instanceof DatabaseNode) {
            $this->databaseNode = $mixed;
        } elseif ($mixed instanceof Database) {
            $this->databaseNode = $mixed->getMaster();
        } elseif (is_string($mixed)) {
            $this->databaseNode = Config::getDatabaseMaster($mixed);

            if ($this->databaseNode == null || !($this->databaseNode instanceof DatabaseNode)) {
                throw new \Parm\Exception\ErrorException("Unable to find database named " . \htmlentities($mixed) . " in \\Parm\\Config configuration.");
            }
        } else {
            throw new \Parm\Exception\ErrorException("A Database, DatabaseNode, or \\Parm\\Config must be used for Parm to work.");
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

        $result = $this->getMySQLResult($this->getSQL(), $conn);

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
     * @param  string $sql
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
     * @param  callable $closure Closure to process the rows of the database retrieved with, the closure is passed a Row or DataAccessObject
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
     * Using an Unbuffered Query, Loop through the rows of a query and process with a closure
     * You can use this on millions of rows without memory problems
     * Does lock the table to writes on some databases
     *
     * @param  callable $closure Closure to process the rows of the database retrieved with, the closure is passed a Row or DataAccessObject
     * @return DatabaseProcessor This DatabaseProcessor so you can chain it
     */
    public function unbufferedProcess($closure)
    {
        $conn = $this->databaseNode->getConnection();

        $conn->real_query($this->getSQL());

        $result = $conn->use_result();

        while ($row = $result->fetch_assoc()) {
            $closure($this->loadDataObject($row));
        }

        return $this;

    }

    /**
     * Get the number of rows for a query from the MySQL database via the result
     *
     * @param  \mysqli $result
     * @return integer The number of rows reported from the database
     */
    public function getNumberOfRowsFromResult($result)
    {
        return (int)$result->num_rows;
    }

    /**
     * Execute a sql update
     *
     * @param string $sql The SQL to execute
     */
    public function update($sql)
    {
        if ($this->getMySQLResult($sql) === true) {
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
     * Get a MySQL result from a SQL string
     *
     * @param  string $sql The SQL to execute
     * @return \mysqli result
     */
    public function getMySQLResult($sql)
    {
        $conn = $this->databaseNode->getConnection();

        try {
            $result = $conn->query($sql);
            if ($conn->error != null) {
                throw new \Parm\Exception\ErrorException($conn->error);
            } else {
                return $result;
            }
        } catch (\Parm\Exception\ErrorException $e) {
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
        return $this->databaseNode->getLastInsertId();
    }

    /**
     * Convert a datetime from one timezone to another using the MySQL database as the timezone source. Use the "US/Eastern" format or "Europe/London" formats
     *
     * @param  timestamp|string|DateTime $dateTime The datetime in the source timezone
     * @param  string $sourceTimezone The source timezone. "US/Eastern" mysql format (mysql.time_zone_name)
     * @param  string $destTimezone The destination timezone. "US/Eastern" mysql format (mysql.time_zone_name)
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

        $conn = $this->databaseNode->getConnection();

        $conn->real_query($this->getSQL());

        $result = $conn->use_result();

        while ($row = $result->fetch_assoc()) {
            if (!$firstRecord) {
                echo ",";
            } else {
                $firstRecord = false;
            }

            $obj = $this->loadDataObject($row);

            echo $obj->toJSONString();
        }

        $this->freeResult($result);

        echo "]";

        return true;
    }

    /**
     * Escape a string to prevent mysql injection
     */
    public function escapeString($string)
    {
        $conn = $this->databaseNode->getConnection();

        return $conn->real_escape_string($string);
    }

    /**
     * Format some text for CSV
     */
    public static function formatTextCSV($text)
    {
        $text = preg_replace("/<(.|\n)*?>/", "", $text);

        $text = str_replace("<br/>", "\n", $text);

        $text = str_replace("&nbsp;", " ", $text);

        if (strpos($text, '"') === true) {
            $text = '"' . str_replace('"', '""', $text) . '"';
        } elseif (strpos($text, ',') || strpos($text, "\n") || strpos($text, "\r")) {
            $text = '"' . str_replace('"', '""', $text) . '"';
        }

        return html_entity_decode($text);
    }

    /**
     * Useful for replacing mysql_real_escape_string in old code with DatabaseProcessor::mysql_real_escape_string()
     */
    public static function mysql_real_escape_string($string)
    {
        $firstAvailableDatabaseMaster = Config::__getFirstDatabaseMaster();

        if ($firstAvailableDatabaseMaster == null || !($firstAvailableDatabaseMaster instanceof DatabaseNode)) {
            throw new \Parm\Exception\ErrorException("DatabaseProcessor::mysql_real_escape_string requires \\Parm\\Config");
        }

        $dp = new DatabaseProcessor($firstAvailableDatabaseMaster);

        return $dp->escapeString($string);
    }

}
