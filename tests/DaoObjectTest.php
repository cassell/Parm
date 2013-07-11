<?php

require dirname(__FILE__) . '/test.inc.php';

class DaoFactoryTest extends PHPUnit_Framework_TestCase
{
	function testFindId()
	{
		$sharon = Parm\Dao\ZipcodesDaoObject::findId(1445);
		$this->assertEquals('16146', $sharon->getZipcode());
	}
	
}

?>