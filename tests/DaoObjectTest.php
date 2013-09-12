<?php

require dirname(__FILE__) . '/test.inc.php';

class DaoObjectTest extends PHPUnit_Framework_TestCase
{
	function testFindId()
	{
		$sharon = Parm\Dao\ZipcodesDaoObject::findId(1445);
		$this->assertEquals('16146', $sharon->getZipcode());
	}
	
	function testCreateClone()
	{
		$sharon = Parm\Dao\ZipcodesDaoObject::findId(1445);
		$sharonClone = $sharon->createClone();
		$this->assertEquals($sharon->getZipcode(),$sharonClone->getZipcode());
	}
	
	function testGetDatabaseName()
	{
		$new = new Parm\Dao\ZipcodesDaoObject();
		$this->assertEquals('parm_tests', $new->getDatabaseName());
	}

	function testGetTableName()
	{
		$new = new Parm\Dao\ZipcodesDaoObject();
		$this->assertEquals('zipcodes', $new->getTableName());
	}

	function testGetIdField()
	{
		$new = new Parm\Dao\ZipcodesDaoObject();
		$this->assertEquals('zipcode_id', $new->getIdField());
	}
	
	function testInsertNewObject()
	{
		$new = new Parm\Dao\PeopleDaoObject();
		$new->setFirstName("Joe");
		$new->setLastName("Paterno");
		$new->setCreateDate(time());
		$new->setCreateDatetime(time());
		$new->setZipcodeId(1529);
		$new->save();
		
	}

	
	
	
}

?>