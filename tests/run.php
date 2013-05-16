<?php

// Include the test framework
include_once('enhance/EnhanceTestFramework.php');
include_once('config/tests.config.inc.php');
include_once('units/class.DatabaseProcessorTests.php');
include_once('units/class.DaoFactoryTests.php');
include_once('units/class.DaoObjectTests.php');

shell_exec('/usr/local/mysql/bin/mysql --user=' . escapeshellarg($_SERVER['MYSQL_USERNAME']) . ' --password=' . escapeshellarg($_SERVER['MYSQL_PASSWORD']) . ' --batch -e "drop database if exists sqlicious_test; create database sqlicious_test;"');

shell_exec('/usr/local/mysql/bin/mysql --user=' . escapeshellarg($_SERVER['MYSQL_USERNAME']) . ' --password=' . escapeshellarg($_SERVER['MYSQL_PASSWORD']) . ' sqlicious_test < ' . dirname(__FILE__).'/database/sqlicious-test.sql');

// Run the tests
\Enhance\Core::runTests();


?>