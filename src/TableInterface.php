<?php

namespace Parm;

interface TableInterface
{
    public static function getTableName();

    public static function getIdField();

    public static function getFields();

    public static function getDatabaseName();

    /**
     * @return DataAccessObjectFactory
     */
    public static function getFactory();

    public static function getDefaultRow();
}
