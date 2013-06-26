<?php

require_once dirname(dirname(__FILE__)) . '/vendor/autoload.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/Database.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/DatabaseNode.php';
require_once dirname(dirname(__FILE__)) . '/src/Parm/DataAccessArray.php';
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

define('PARM_CONFIG_GLOBAL','PARM_CONFIG_GLOBAL');
$GLOBALS[PARM_CONFIG_GLOBAL]['devq'] = new Parm\Database();
$GLOBALS[PARM_CONFIG_GLOBAL]['devq']->setMaster(new Parm\DatabaseNode('devq', 'localhost', 'web', 'spillresponse'));


?>