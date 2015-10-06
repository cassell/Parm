<?php

namespace Parm;

use Doctrine\DBAL\Connection;

interface TableInterface
{
    public static function getTableName();

    public static function getIdField();

    public static function getFields();

    public static function getDatabaseName();

    /**
     * @param Connection $connection
     * @return DataAccessObjectFactory
     */
    public static function getFactory(Connection $connection = null);

    public static function getDefaultRow();
}
