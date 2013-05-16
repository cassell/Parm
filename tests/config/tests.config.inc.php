<?php

define("TESTS_CONFIG_PATH", dirname(__FILE__) . '/');

if(file_exists(TESTS_CONFIG_PATH.'tests.environment.inc.php'))
{
	include(TESTS_CONFIG_PATH.'tests.environment.inc.php');
}

define("SQLICIOUS_INCLUDE_PATH", dirname(dirname(dirname(__FILE__))));
define("SQLICIOUS_CONFIG_GLOBAL", "DATABASE_CONFIG");
define("SQLICIOUS_MYSQL_DATE_FORMAT","Y-m-d");
define("SQLICIOUS_MYSQL_DATETIME_FORMAT","Y-m-d H:i:s");

require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.SQLiciousErrorException.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DatabaseConfiguration.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DataAccessArray.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DatabaseProcessor.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DataAccessObject.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DataAccessObjectFactory.php');

$GLOBALS[SQLICIOUS_CONFIG_GLOBAL] = new SQLiciousConfig();

$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['sqlicious_test'] = new DatabaseConfiguration('sqlicious_test');
$GLOBALS[SQLICIOUS_CONFIG_GLOBAL]['sqlicious_test']->setMaster(new DatabaseNode($_SERVER['MYSQL_DB_NAME'], $_SERVER['MYSQL_DB_HOST'], $_SERVER['MYSQL_USERNAME'], $_SERVER['MYSQL_PASSWORD']));

?>