<?php

error_reporting(E_ALL);

set_error_handler(function($number, $message, $file, $line)
{
	print_r(array( 'type' => $number, 'message' => $message, 'file' => $file, 'line' => $line ));
});

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';

\Parm\Config::addDatabase('parm_namespaced_tests',new Parm\Mysql\DatabaseNode($GLOBALS['db_namespaced_name'],$GLOBALS['db_namespaced_host'],$GLOBALS['db_namespaced_username'],$GLOBALS['db_namespaced_password']));
\Parm\Config::addDatabase('parm-global-tests',new Parm\Mysql\DatabaseNode($GLOBALS['db_global_name'],$GLOBALS['db_global_host'],$GLOBALS['db_global_username'],$GLOBALS['db_global_password']));

if(!file_exists(dirname(__FILE__).'/dao'))
{
	mkdir(dirname(__FILE__).'/dao');
	chmod(dirname(__FILE__).'/dao', 0777);
	
	$generator = new Parm\Generator\DatabaseGenerator(\Parm\Config::getDatabase('parm_namespaced_tests'));
	$generator->setDestinationDirectory(dirname(__FILE__).'/dao/namespaced');
	$generator->setGeneratedNamespace("ParmTests\\Dao");
	$generator->generate();

	$generator = new Parm\Generator\DatabaseGenerator(\Parm\Config::getDatabase('parm-global-tests'));
	$generator->setDestinationDirectory(dirname(__FILE__).'/dao/global');
	$generator->useGlobalNamespace();
	$generator->generate();

}

require_once dirname(__FILE__).'/dao/namespaced/autoload.php';
require_once dirname(__FILE__).'/dao/global/autoload.php';

?>