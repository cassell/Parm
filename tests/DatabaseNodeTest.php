<?php

require dirname(__FILE__) . '/test.inc.php';

class DatabaseNodeTest extends PHPUnit_Framework_TestCase
{
	public function testConnection()
	{
		$node = $GLOBALS[PARM_CONFIG_GLOBAL]['parm_tests']->getMaster();
		
		if($node instanceOf \Parm\DatabaseNode)
		{
			$connection = $node->getConnection();
			
			$connection->closeConnection();
		}
		else
		{
			$this->fail('$node was not instanceOf \Parm\DatabaseNode');
			
		}
	}
}


?>
