<?php

require dirname(__FILE__) . '/test.inc.php';

class BindingsTest extends PHPUnit_Framework_TestCase
{
	function testAndConditional()
	{
		$f = new Parm\Dao\PeopleDaoFactory();
		
		$cond = new \Parm\Binding\Conditional\AndConditional();
		$this->assertEquals('', $cond->getSQL($f));
	}
	
	
}

?>