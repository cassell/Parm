<?php
namespace Parm\Generator;

class SchemaGenerator
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    public function __construct(\Doctrine\DBAL\Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getTableSchemas()
    {
        $schemas = [];
        foreach ($this->getTableNames() as $tableName) {
            $schemas[$tableName] = $this->getSchemaForTable($tableName);
        }

        return $schemas;
    }

    /**
     * @param  string $tableName
     * @return array
     */
    private function getSchemaForTable($tableName)
    {
        $idField = null;
        $columns = [];

        foreach ($this->connection->fetchAll("SHOW COLUMNS FROM `" . $this->connection->getDatabase() . "`.`" . $tableName."`") as $column) {

            if ($column['Key'] === "PRI") {
                $columns[] = new Column($tableName,$column['Field'],$column['Type'],$column['Extra'],$column['Default'],true);
                $idField = $column['Field'];
            } else {
                $columns[] = new Column($tableName,$column['Field'],$column['Type'],$column['Extra'],$column['Default'],false);
            }

        }

        return new TableSchema($this->connection->getDatabase(),$tableName,$idField,$columns);

    }

    private function getTableNames()
    {
        $tableNames = [];

        foreach ($this->connection->fetchAll('SHOW TABLES') as $row) {
            $tableNames[] = $row['Tables_in_' . $this->connection->getDatabase()];
        }

        return $tableNames;

    }

}
