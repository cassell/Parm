<?php

error_reporting(E_ALL | E_STRICT);

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Database.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/DatabaseNode.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/DataArray.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/DatabaseProcessor.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/DataAccessObject.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/DataAccessObjectFactory.php';

require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/SQLString.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/Conditional/Conditional.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/Conditional/AndConditional.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/Conditional/OrConditional.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/StringBinding.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/CaseSensitiveEqualsBinding.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/ContainsBinding.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/EqualsBinding.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/FalseBooleanBinding.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/ForeignKeyObjectBinding.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/InBinding.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/NotEqualsBinding.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Binding/TrueBooleanBinding.php';

if(!defined('PARM_CONFIG_GLOBAL'))
{
	define('PARM_CONFIG_GLOBAL','PARM_CONFIG_GLOBAL');
}

$GLOBALS[PARM_CONFIG_GLOBAL]['parm_tests'] = new Parm\Database();
$GLOBALS[PARM_CONFIG_GLOBAL]['parm_tests']->setMaster(new Parm\DatabaseNode($GLOBALS['db_name'],$GLOBALS['db_host'],$GLOBALS['db_username'],$GLOBALS['db_password']));

if(!file_exists(dirname(__FILE__).'/dao'))
{
	mkdir(dirname(__FILE__).'/dao');
	chmod(dirname(__FILE__).'/dao', 0777);
}

$generator = new Parm\Generator\DatabaseGenerator($GLOBALS[PARM_CONFIG_GLOBAL]['parm_tests']);
$generator->setDestinationDirectory(dirname(__FILE__).'/dao');
$generator->setGeneratedNamespace("Parm\\Dao");
$generator->generate();

$GLOBALS[PARM_CONFIG_GLOBAL]['parm_tests'] = new Parm\Database();
$GLOBALS[PARM_CONFIG_GLOBAL]['parm_tests']->setMaster(new Parm\DatabaseNode($GLOBALS['db_name'],$GLOBALS['db_host'],$GLOBALS['db_username'],$GLOBALS['db_password']));
	

if(!file_exists(dirname(__FILE__).'/dao/PeopleDaoObject.php'))
{
	throw new Exception('PeopleDaoObject does not exist');
}

if(!file_exists(dirname(__FILE__).'/dao/PeopleDaoFactory.php'))
{
	throw new Exception('PeopleDaoFactory does not exist');
}

if(!file_exists(dirname(__FILE__).'/dao/ZipcodesDaoFactory.php'))
{
	throw new Exception('ZipcodesDaoFactory does not exist');
}

if(!file_exists(dirname(__FILE__).'/dao/ZipcodesDaoObject.php'))
{
	throw new Exception('ZipcodesDaoObject does not exist');
}

require_once dirname(__FILE__).'/dao/autoload.php';

?>