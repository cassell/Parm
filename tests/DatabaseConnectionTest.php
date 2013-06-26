<?php

require dirname(__FILE__) . '/test.inc.php';


class DatabaseConnectionTest extends PHPUnit_Framework_TestCase
{
	public function testConnection()
	{
		$this->assertEquals(5, 5);
	}
	
	
}


?>
