<?php

namespace Parm;

interface TableInterface
{
	static function getTableName();
	static function getIdField();
	static function getFields();
	static function getDatabaseName();
}

