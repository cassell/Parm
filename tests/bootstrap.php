<?php

error_reporting(E_ALL);

set_error_handler(function ($number, $message, $file, $line) {
    print_r(array('type' => $number, 'message' => $message, 'file' => $file, 'line' => $line));
});

ini_set('date.timezone', 'UTC');

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

\Parm\Config::addConnection('parm_namespaced_tests', new Doctrine\DBAL\Connection([
    'dbname' => $GLOBALS['db_namespaced_name'],
    'user' => $GLOBALS['db_namespaced_username'],
    'password' => $GLOBALS['db_namespaced_password'],
    'host' => $GLOBALS['db_namespaced_host'],
    'driver' => 'pdo_mysql',
], new Doctrine\DBAL\Driver\PDOMySql\Driver(), null, null));
\Parm\Config::addConnection('parm-global-tests', new Doctrine\DBAL\Connection([
    'dbname' => $GLOBALS['db_global_name'],
    'user' => $GLOBALS['db_global_username'],
    'password' => $GLOBALS['db_global_password'],
    'host' => $GLOBALS['db_global_host'],
    'driver' => 'pdo_mysql',
], new Doctrine\DBAL\Driver\PDOMySql\Driver(), null, null));

if (!file_exists(dirname(__FILE__) . '/dao')) {
    mkdir(dirname(__FILE__) . '/dao');
    chmod(dirname(__FILE__) . '/dao', 0777);
}

$generator = new Parm\Generator\DatabaseGenerator(\Parm\Config::getConnection('parm_namespaced_tests'),dirname(__FILE__) . '/dao/namespaced','ParmTests\Dao');
$generator->generate();

$generator = new Parm\Generator\DatabaseGenerator(\Parm\Config::getConnection('parm-global-tests'),dirname(__FILE__) . '/dao/global');
$generator->generate();

require_once dirname(__FILE__) . '/dao/namespaced/autoload.php';
require_once dirname(__FILE__) . '/dao/global/autoload.php';
