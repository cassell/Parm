<?php

namespace Parm;

interface TableInterface
{
    public static function getTableName();

    public static function getIdField();

    public static function getFields();

    public static function getDatabaseName();
}
