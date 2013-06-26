<?php

require dirname(__FILE__) . '/test.inc.php';

class DaoFactoryTest extends PHPUnit_Framework_TestCase
{
	
	function testFindId()
	{
		$f = new Parm\Dao\ZipcodesDaoFactory();
		$sharon = $f->findId(1445);
		$this->assertEquals('16146', $sharon->getZipcode());
	}
	
	
	
}


?>
