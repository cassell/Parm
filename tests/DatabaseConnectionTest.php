<?php

require dirname(dirname(__FILE__)) . '/vendor/autoload.php';

class DatabaseConnectionTest extends PHPUnit_Framework_TestCase
{
	public function testConnection()
	{
		$this->assertEquals(5, 5);
	}
	
	
}


?>
