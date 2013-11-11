<?php

require dirname(__FILE__) . '/test.inc.php';

class ConditionalsText extends PHPUnit_Framework_TestCase
{
	function testAndConditionalEmpty()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$cond = new \Parm\Binding\Conditional\AndConditional();
		$this->assertEquals('', $cond->getSQL($f));
		
	}
	
	function testAndConditionalStringsAndBindings()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$cond = new \Parm\Binding\Conditional\AndConditional();
		
		$cond->addBinding("zipcode_id = 0");
		$cond->addBinding("archived = 1");
		$cond->addBinding(new Parm\Binding\EqualsBinding("people_id", 99999999999999999));
		
		$this->assertEquals("(zipcode_id = 0 AND archived = 1 AND people_id = '99999999999999999')", $cond->getSQL($f));
		
		
	}
	
	function testOrCondtionalEmpty()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$cond = new \Parm\Binding\Conditional\OrConditional();
		$this->assertEquals('', $cond->getSQL($f));
	}
	
	function testOrConditionalStringsAndBindings()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$cond = new \Parm\Binding\Conditional\OrConditional();
		$cond->addBinding("zipcode_id = 0");
		$cond->addBinding("archived = 1");
		$cond->addBinding(new Parm\Binding\EqualsBinding("people_id", 99999999999999999));
		
		$this->assertEquals("(zipcode_id = 0 OR archived = 1 OR people_id = '99999999999999999')", $cond->getSQL($f));
	}
	
	function testNestedConditionals()
	{
		$f = new ParmTests\Dao\PeopleDaoFactory();
		
		$cond = new \Parm\Binding\Conditional\AndConditional();
		$cond->addBinding("zipcode_id = 0");
		$cond->addBinding("archived = 1");
		
		$orConditional = new \Parm\Binding\Conditional\OrConditional();
		
		$orConditional->addBinding(new Parm\Binding\Binding("people_id", '>', 1));
		$orConditional->addBinding(new Parm\Binding\Binding("people_id", 'is', NULL));
		
		$cond->addConditional($orConditional);
		
		$this->assertEquals("(zipcode_id = 0 AND archived = 1 AND (people_id > '1' OR people_id is NULL))", $cond->getSQL($f));
		
	}
	
	
}

?>