<?php

namespace Parm\Generator;

class DatabaseGenerator
{
    public static $DESTINATION_DIRECTORY_FOLDER_PERMISSIONS = 0777;
    public static $GENERATED_CODE_FILE_PERMISSIONS = 0777;

    public $destinationDirectory;
    public $generateToNamespace = "";
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    public function __construct(\Doctrine\DBAL\Connection $connection, $destinationDirectory, $generateToNamespace = "\\")
    {
        if (!is_string($destinationDirectory)) {
            throw new \Parm\Exception\ErrorException('destinationDirectory must be a string');
        }

        if (!is_string($generateToNamespace)) {
            throw new \Parm\Exception\ErrorException('generateToNamespace must be a string');
        }

        $this->connection = $connection;
        $this->destinationDirectory = $destinationDirectory;
        $this->generateToNamespace = $generateToNamespace;
    }

    /**
     * Use the global namespace for generated objects and factories
     */
    public function useGlobalNamespace()
    {
        $this->generateToNamespace = "\\";
    }

    /**
     * Generate the objects and factories
     */
    public function generate()
    {
        $this->readyDestinationDirectory();

        $tableNames = $this->getTableNames();

        $globalNamespaceData = array();

        if ($tableNames != null && count($tableNames) > 0) {
            foreach ($tableNames as $tableName) {
                $data = $this->getTemplatingDataFromTableName($tableName);

                $globalNamespaceData["tables"][] = $data;

                $m = new \Mustache_Engine;

                $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/' . $data['className'] . 'Table.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/table_interface.mustache'), $data));

                $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/' . $data['className'] . 'TableFunctions.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/table_trait.mustache'), $data));

                $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/' . $data['className'] . 'DaoObject.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/dao_object.mustache'), $data));

                $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/' . $data['className'] . 'DaoFactory.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/dao_factory.mustache'), $data));

            }

            $globalNamespaceData['namespace'] = $this->generateToNamespace;
            $globalNamespaceData['escapedNamespace'] = $this->generateToNamespace != "" ? (str_replace("\\", "\\\\", $this->generateToNamespace) . "\\\\") : '';
            $globalNamespaceData['namespaceLength'] = strlen($this->generateToNamespace) + 1;

            // global namespace file
            if ($this->generateToNamespace != "\\" && $this->generateToNamespace != "") {
                $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/alias_all_tables_to_global_namespace.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/alias_all_tables_to_global_namespace.mustache'), $globalNamespaceData));

                //autoloader
                $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/autoload.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/namespaced_autoload.mustache'), $globalNamespaceData));
            } else {
                $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/autoload.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/global_autoload.mustache'), $globalNamespaceData));
            }
        } else {
            throw new \Parm\Exception\ErrorException("No tables in database.");
        }
    }

    /**
     * Return the list of tables in a database
     * @return Array An array of table names
     */
    public function getTableNames()
    {
        $databaseName = $this->connection->getDatabase();

        $dp = new \Parm\DatabaseProcessor($this->connection);
        $dp->setSQL('SHOW TABLES');

        $tableNames = array();

        $dp->process(function ($row) use (&$tableNames, $databaseName) {

            $tableNames[] = $row['Tables_in_' . $databaseName];
        });

        return $tableNames;
    }

    /**
     * Return data and templating information about a table.
     * @return Array Data for the Table
     */
    public function getTemplatingDataFromTableName($tableName)
    {
        $idFieldName = '';
        $idFieldInt = 0;
        $defaultValuePack = array();
        $fieldsPack = array();
        $bindingsPack = array();

        $className = ucfirst(\Parm\Row::columnToCamelCase($tableName));

        $dp = new \Parm\DatabaseProcessor($this->connection);
        $dp->setSQL("SHOW COLUMNS FROM `" . $this->connection->getDatabase() . "`.`" . $tableName."`");
        $columns = $dp->getArray();

        // id field
        if ($columns != null && count($columns) > 0) {
            foreach ($columns as $key => $column) {

                if ($column['Field'] == "id" && $column['Key'] != "PRI") {
                    throw new \Parm\Exception\ErrorException('"id" is an invalid column name unless it is the primary key for the Parm\DatabaseGenerator. It causes a collision with the function getId() which always returns the primary key.');
                }

                if ($column['Key'] == "PRI") {
                    $idFieldName = strtoupper(str_replace("-", "_",$column['Field']));
                    $columns[$key]['isPrimaryKey'] = true;

                    if ($column['Field'] != "id" && preg_match("/int\(/", $column['Type'])) {
                        $idFieldInt = 1;
                    }

                } else {
                    $columns[$key]['isPrimaryKey'] = false;
                }

                $columns[$key]['FieldCase'] = ucfirst(\Parm\Row::columnToCamelCase($column['Field']));
                $columns[$key]['AllCaps'] = strtoupper(str_replace("-", "_", $column['Field']));

                $fieldsPack[] = $className . "Table::" . $columns[$key]['AllCaps'] . "_COLUMN";

                // column type
                $columns[$key]['typeDate'] = 0;
                $columns[$key]['typeDatetime'] = 0;
                $columns[$key]['typeBoolean'] = 0;
                $columns[$key]['typeInt'] = 0;
                $columns[$key]['typeNumeric'] = 0;
                $columns[$key]['typeString'] = 0;

                if ($column['Type'] == "date") {
                    $columns[$key]['typeDate'] = 1;
                } elseif ($column['Type'] == "datetime" || $column['Type'] == "timestamp") {
                    $columns[$key]['typeDatetime'] = 1;
                } elseif ($column['Type'] == "tinyint(1)" || $column['Type'] == "int(1)") {
                    $columns[$key]['typeBoolean'] = 1;

                    $bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\Row::columnToCamelCase($column['Field'])) . "TrueBinding() { \$this->addBinding(new \Parm\Binding\TrueBooleanBinding('" . $tableName . "." . $column['Field'] . "')); }";
                    $bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\Row::columnToCamelCase($column['Field'])) . "FalseBinding() { \$this->addBinding(new \Parm\Binding\FalseBooleanBinding('" . $tableName . "." . $column['Field'] . "')); }";
                    $bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\Row::columnToCamelCase($column['Field'])) . "NotTrueBinding() { \$this->addBinding(new \Parm\Binding\NotEqualsBinding('" . $tableName . "." . $column['Field'] . "',1)); }";
                    $bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\Row::columnToCamelCase($column['Field'])) . "NotFalseBinding() { \$this->addBinding(new \Parm\Binding\NotEqualsBinding('" . $tableName . "." . $column['Field'] . "',0));  }";
                    $bindingsPack[] = "\n";

                } elseif (preg_match("/int\(/", $column['Type'])) {
                    $columns[$key]['typeInt'] = 1;
                } elseif (preg_match("/decimal/", $column['Type']) || preg_match("/float/", $column['Type']) || preg_match("/double/", $column['Type']) || preg_match("/real/", $column['Type'])) {
                    $columns[$key]['typeNumeric'] = 1;
                } else {
                    $columns[$key]['typeString'] = 1;
                }

                $defaultValuePack[] = $className . "Table::" . $columns[$key]['AllCaps'] . "_COLUMN => " . ($column['Default'] == null ? "null" : "'" . str_replace("'", "\'", $column['Default']) . "'");

            }
        }

        return array('tableName' => $tableName,
            'variableName' => \Parm\Row::columnToCamelCase($tableName),
            'className' => $className,
            'databaseName' => $this->connection->getDatabase(),
            'idFieldIsInteger' => $idFieldInt,
            'idFieldName' => $idFieldName,
            'idFieldNameAllCaps' => strtoupper($idFieldName),
            'namespace' => $this->generateToNamespace,
            'autoloaderNamespace' => $this->generateToNamespace,
            'namespaceClassSyntax' => ($this->generateToNamespace != "" && $this->generateToNamespace != "\\") ? 'namespace ' . $this->generateToNamespace . ';' : '',
            'namespaceLength' => strlen($this->generateToNamespace),
            'columns' => $columns,
            'defaultValuePack' => implode(", ", $defaultValuePack),
            'fieldList' => implode(", ", $fieldsPack),
            'bindingsPack' => implode("\n", $bindingsPack),
        );

    }

    private function writeContentsToFile($fileName, $contents)
    {
        if (file_exists($fileName) && !is_writable($fileName)) {
            throw new \Parm\Exception\ErrorException('File is unwritable: ' . $fileName);
        } elseif (@file_put_contents($fileName, $contents) !== FALSE) {
            try {
                @chmod($fileName, self::$GENERATED_CODE_FILE_PERMISSIONS);
            } catch (\Exception $e) {
                throw new \Parm\Exception\ErrorException('Unable to make file "' . htmlentities($fileName) . '" read/write by all.');
            }

            return true;
        } else {
            throw new \Parm\Exception\ErrorException('Unable to write file: ' . htmlentities($fileName));
        }
    }

    private function readyDestinationDirectory()
    {
        if (!file_exists($this->destinationDirectory)) {
            if (!@mkdir($this->destinationDirectory)) {
                throw new \Parm\Exception\ErrorException('Unable to create database destination directory "' . htmlentities($this->destinationDirectory) . '".');
            }
            try {
                chmod($this->destinationDirectory, self::$DESTINATION_DIRECTORY_FOLDER_PERMISSIONS);
            } catch (\Exception $e) {
                throw new \Parm\Exception\ErrorException('Unable to write to database destination directory "' . htmlentities($this->destinationDirectory) . '".');
            }
        }

        $this->cleanupPreviousGeneration();
    }

    private function cleanupPreviousGeneration()
    {
        $this->deleteFiles(glob($this->destinationDirectory . '/autoload.php'));
        $this->deleteFiles(glob($this->destinationDirectory . '/*DaoFactory.php'));
        $this->deleteFiles(glob($this->destinationDirectory . '/*DaoObject.php'));
        $this->deleteFiles(glob($this->destinationDirectory . '/*Table.php'));
        $this->deleteFiles(glob($this->destinationDirectory . '/*TableFunctions.php'));
    }

    private function deleteFiles($files)
    {
        if ($files != null) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }

}
