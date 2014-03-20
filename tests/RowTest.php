<?php

require dirname(__FILE__) . '/test.inc.php';

class DataArrayTest extends PHPUnit_Framework_TestCase
{

	function testCamelCase()
	{
		$this->assertEquals('arabianCamel', Parm\Row::columnToCamelCase('arabian_camel'));
		
		$this->assertEquals('camulusFerus', Parm\Row::columnToCamelCase('camulus_ferus'));
		
	}
}

?>