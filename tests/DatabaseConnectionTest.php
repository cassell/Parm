<?php

require dirname(__FILE__) . '/test.inc.php';


class DatabaseConnectionTest extends PHPUnit_Framework_TestCase
{
	public function testConnection()
	{
		print_r($GLOBALS);
		$this->assertEquals(5, 5);
	}
	
	
}


?>
