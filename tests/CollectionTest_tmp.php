<?php

require dirname(__FILE__) . '/test.inc.php';

class CollectionTest extends PHPUnit_Framework_TestCase
{

	function testCollectionInstance()
	{
		$f = new \ParmTests\Dao\ZipcodesDaoFactory();
		$collection = new \Parm\Collection($f);

		$this->assertFalse(is_array($collection));

		$this->assertTrue($collection instanceof \Iterator);

	}

	function testCollectionIteration()
	{
		$zipCodeTotal = 0;

		$f = new \ParmTests\Dao\ZipcodesDaoFactory();

		foreach(new \Parm\Collection($f) as $primaryKey => $zipcode) {

			$zipCodeTotal += (int)$zipcode->getZipcode();
		}

		$this->assertEquals(29498849,$zipCodeTotal);

	}

	function testCollectionPageSize10()
	{
		$zipCodeTotal = 0;

		$f = new \ParmTests\Dao\ZipcodesDaoFactory();

		foreach(new \Parm\Collection($f,10) as $primaryKey => $zipcode) {

			$zipCodeTotal += (int)$zipcode->getZipcode();
		}

		$this->assertEquals(29498849,$zipCodeTotal);

	}

	function testCollectionPageSize1()
	{
		$zipCodeTotal = 0;

		$f = new \ParmTests\Dao\ZipcodesDaoFactory();

		foreach(new \Parm\Collection($f,1) as $primaryKey => $zipcode) {

			$zipCodeTotal += (int)$zipcode->getZipcode();
		}

		$this->assertEquals(29498849,$zipCodeTotal);

	}


	function testCollectionPageSize0ThrowsException()
	{

		try
		{
			$zipCodeTotal = 0;

			$f = new \ParmTests\Dao\ZipcodesDaoFactory();

			foreach(new \Parm\Collection($f,0) as $primaryKey => $zipcode) {

				$zipCodeTotal += (int)$zipcode->getZipcode();
			}
		}
		catch(\Exception $e)
		{
			// do nothing
		}

		$this->assertInstanceOf("\\Parm\\Exception\\ErrorException",$e);

	}

	function testCollectionWithFactoryBindings()
	{
		$f = new \ParmTests\Dao\ZipcodesDaoFactory();
		$f->whereEquals(\ParmTests\Dao\ZipcodesDaoObject::CITY_COLUMN,"Scranton");

		$zipCodeTotal = 0;

		foreach(new \Parm\Collection($f,2) as $primaryKey => $zipcode) {

			$zipCodeTotal += (int)$zipcode->getZipcode();
		}

		$this->assertEquals(111039,$zipCodeTotal);

	}





}

?>