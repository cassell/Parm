<?php

require dirname(__FILE__) . '/test.inc.php';

class DataArrayTest extends PHPUnit_Framework_TestCase
{

	function testCamelCase()
	{
		$this->assertEquals('arabianCamel', Parm\DataArray::columnToCamelCase('arabian_camel'));
		
		$this->assertEquals('camulusFerus', Parm\DataArray::columnToCamelCase('camulus_ferus'));
		
	}
}

?>