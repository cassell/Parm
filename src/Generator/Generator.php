<?php

namespace Parm\Generator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class Generator
{
    public static $DESTINATION_DIRECTORY_FOLDER_PERMISSIONS = 0777;
    public static $GENERATED_CODE_FILE_PERMISSIONS = 0777;

    public $destinationDirectory;
    public $generateToNamespace = "";
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection, $destinationDirectory, $generateToNamespace = "\\")
    {
        $this->connection = $connection;

        if (!is_string($destinationDirectory)) {
            throw new \Parm\Exception\ErrorException('destinationDirectory must be a string');
        }

        $this->destinationDirectory = $destinationDirectory;
        if ($generateToNamespace == null || $generateToNamespace == "\\") {
            $this->useGlobalNamespace();
        } else {
            $this->generateToNamespace = (string) $generateToNamespace;
        }


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

        $tableSchemas = (new SchemaGenerator($this->connection))->getTableSchemas();

        // @codeCoverageIgnoreStart
        if ($tableSchemas === null || count($tableSchemas) === 0) {
            throw new \Parm\Exception\ErrorException("No tables in database.");
        }
        // @codeCoverageIgnoreEnd

        $globalNamespaceData['tables'] = [];
        $globalNamespaceData['namespace'] = $this->generateToNamespace;
        $globalNamespaceData['escapedNamespace'] = $this->generateToNamespace != "" ? (str_replace("\\", "\\\\", $this->generateToNamespace) . "\\\\") : '';
        $globalNamespaceData['namespaceLength'] = strlen($this->generateToNamespace) + 1;

        foreach ((new SchemaGenerator($this->connection))->getTableSchemas() as $tableName => $schema) {

            $globalNamespaceData['tables'][] = ['className' => ucfirst(\Parm\Row::columnToCamelCase($tableName)) ];

            $data = $this->getTemplatingDataFromSchema($schema);

            $m = new \Mustache_Engine;

            $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/' . $data['className'] . 'Table.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/table_interface.mustache'), $data));

            $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/' . $data['className'] . 'TableFunctions.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/table_trait.mustache'), $data));

            $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/' . $data['className'] . 'DaoObject.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/dao_object.mustache'), $data));

            $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/' . $data['className'] . 'DaoFactory.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/dao_factory.mustache'), $data));

            // global namespace file
            if ($this->generateToNamespace != "\\" && $this->generateToNamespace != "") {
                $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/alias_all_tables_to_global_namespace.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/alias_all_tables_to_global_namespace.mustache'), $globalNamespaceData));
                $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/autoload.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/namespaced_autoload.mustache'), $globalNamespaceData));
            } else {
                $this->writeContentsToFile(rtrim($this->destinationDirectory, '/') . '/autoload.php', $m->render(file_get_contents(dirname(__FILE__) . '/templates/global_autoload.mustache'), $globalNamespaceData));
            }
        }
    }

    /**
     * @param $fileName
     * @param $contents
     * @return bool
     * @throws \Parm\Exception\ErrorException
     * @codeCoverageIgnore
     */
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

    /**
     * @throws \Parm\Exception\ErrorException
     * @codeCoverageIgnore
     */
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

    private function getTemplatingDataFromSchema(TableSchema $schema)
    {
        $columnData = [];
        $fieldsPack = [];
        $defaultValuePack = [];
        $bindingsPack = [];

        /** @var Column $col */
        foreach($schema->getColumns() as $col)
        {
            $columnData[] = [
                'Field' => $col->getColumnName(),
                'FieldCase' => ucfirst(\Parm\Row::columnToCamelCase($col->getColumnName())),
                'AllCaps' => $this->fieldToAllCaps($col->getColumnName()),
                'typeDate' => $col->isTypeDate(),
                'typeDatetime' => $col->isTypeDateTime(),
                'typeBoolean' => $col->isBoolean(),
                'typeInt' => $col->isInteger(),
                'typeNumeric' => $col->isNumeric(),
                'typeString' => $col->isString(),
                'isPrimaryKey' => $col->isPrimaryKey()

            ];

            $fieldsPack[] = ucfirst(\Parm\Row::columnToCamelCase($schema->getTableName())) . "Table::" . $this->fieldToAllCaps($col->getColumnName()) . "_COLUMN";

            $defaultValuePack[] = ucfirst(\Parm\Row::columnToCamelCase($schema->getTableName())) . "Table::" . $this->fieldToAllCaps($col->getColumnName()) . "_COLUMN => " . ($col->getDefaultValue() != null ? $col->getDefaultValue() : "null");

            $bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\Row::columnToCamelCase($col->getColumnName())) . "TrueBinding() { \$this->addBinding(new \\Parm\\Binding\\TrueBooleanBinding('" . $schema->getTableName() . "." . $col->getColumnName() . "')); }";
            $bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\Row::columnToCamelCase($col->getColumnName())) . "FalseBinding() { \$this->addBinding(new \\Parm\\Binding\\FalseBooleanBinding('" . $schema->getTableName() . "." . $col->getColumnName() . "')); }";
            $bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\Row::columnToCamelCase($col->getColumnName())) . "NotTrueBinding() { \$this->addBinding(new \\Parm\\Binding\\NotEqualsBinding('" . $schema->getTableName() . "." . $col->getColumnName() . "',1)); }";
            $bindingsPack[] = "\tfinal function add" . ucfirst(\Parm\Row::columnToCamelCase($col->getColumnName())) . "NotFalseBinding() { \$this->addBinding(new \\Parm\\Binding\\NotEqualsBinding('" . $schema->getTableName() . "." . $col->getColumnName() . "',0));  }";
            $bindingsPack[] = "\n";
        }

        return array('tableName' => $schema->getTableName(),
            'variableName' => \Parm\Row::columnToCamelCase($schema->getTableName()),
            'className' => ucfirst(\Parm\Row::columnToCamelCase($schema->getTableName())),
            'databaseName' => $this->connection->getDatabase(),
            'id' => [
                'exists' => $schema->getIdField() != null,
                'columnName' => $schema->getIdField(),
                'columnNameAllCaps' => $this->fieldToAllCaps($schema->getIdField()),
                'isInteger' => $schema->isIdInteger(),
                'isString' => $schema->isIdString(),
                'isCalledId' => $schema->isIdCalledId(),
                'isAutoIncremented' => $schema->isIdAutoIncremented()
            ],
            'namespace' => $this->generateToNamespace,
            'autoloaderNamespace' => $this->generateToNamespace,
            'namespaceClassSyntax' => ($this->generateToNamespace != "" && $this->generateToNamespace != "\\") ? 'namespace ' . $this->generateToNamespace . ';' : '',
            'namespaceLength' => strlen($this->generateToNamespace),
            'columns' => $columnData,
            'defaultValuePack' => implode(", ", $defaultValuePack),
            'fieldList' => implode(", ", $fieldsPack),
            'bindingsPack' => implode("\n", $bindingsPack),
        );

    }

    /**
     * @param string $columnName
     * @return string
     */
    private function fieldToAllCaps($columnName)
    {
        return strtoupper(str_replace("-", "_", $columnName));
    }

}
