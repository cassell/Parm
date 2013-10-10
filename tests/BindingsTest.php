<?php

require dirname(__FILE__) . '/test.inc.php';

class BindingsTest extends PHPUnit_Framework_TestCase
{
	function testStringBinding()
	{
		$f = new Parm\Dao\PeopleDaoFactory();
		
		$binding = new \Parm\Binding\StringBinding("people.people_id = 1");
		$this->assertEquals('people.people_id = 1', $binding->getSQL($f));
		
	}
	
	
	function testEqualsBindingNull()
	{
		
	}
	
	
}

?>