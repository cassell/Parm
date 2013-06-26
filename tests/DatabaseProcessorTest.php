<?php

require dirname(__FILE__) . '/test.inc.php';

class DatabaseProcessorTest extends PHPUnit_Framework_TestCase
{
	
	public function testDatabaseConfiguration()
	{
		$node = $GLOBALS[PARM_CONFIG_GLOBAL]['parm_tests']->getMaster();
		if($node instanceof \Parm\DatabaseNode)
		{
			// hooray!
		}
		else
		{
			$this->fail();
		}
	}
	
	public function testPassingStringToConstructor()
	{
		$dp = new DatabaseProcessor('parm_tests');
		$dp->setSQL('select * from user');
		$result = $dp->query();
	}
	
	public function testPassingNodeToConstructor()
	{
		$dp = new DatabaseProcessor(new Parm\DatabaseNode($GLOBALS['db_name'],$GLOBALS['db_host'],$GLOBALS['db_username'],$GLOBALS['db_password']));
		$dp->setSQL('select * from user');
		$result = $dp->query();
	}
	
	
	
}


?>
