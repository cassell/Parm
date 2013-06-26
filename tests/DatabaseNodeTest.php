<?php

require dirname(__FILE__) . '/test.inc.php';

class DatabaseNodeTest extends PHPUnit_Framework_TestCase
{
	public function testConnection()
	{
		$node = new Parm\DatabaseNode($GLOBALS['db_name'],$GLOBALS['db_host'],$GLOBALS['db_username'],$GLOBALS['db_password']);
		
		if($node instanceOf \Parm\DatabaseNode)
		{
			$connection = $node->getConnection();
			
			if($connection instanceof mysqli)
			{
				if($connection->connect_errno !== 0)
				{
					$this->fail('$connection->connect_errno !== 0');
				}
			}
			else
			{
				$this->fail('$node was not instanceOf \Parm\DatabaseNode');
			}
			
			$node->closeConnection();
		}
		else
		{
			$this->fail('$node was not instanceOf \Parm\DatabaseNode');
			
		}
	}
}


?>
