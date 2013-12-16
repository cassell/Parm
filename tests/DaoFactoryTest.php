<?php

require dirname(__FILE__) . '/test.inc.php';

class DaoFactoryTest extends PHPUnit_Framework_TestCase
{
	function testFindId()
	{
		$f = new ParmTests\Dao\ZipcodesDaoFactory();
		$sharon = $f->findId(1445);
		$this->assertEquals('16146', $sharon->getZipcode());
	}
	
	function testFind()
	{
		$f = new ParmTests\Dao\ZipcodesDaoFactory();
		$objects = $f->find("where zipcode_id = 1445");
		$sharon = reset($objects);
		$this->assertEquals('16146', $sharon->getZipcode());
	}
	
	function testFindThrowException()
	{
		$exceptionCaught = false;
		try
		{
			$f = new ParmTests\Dao\ZipcodesDaoFactory();
			$f->addBinding(new \Parm\Binding\EqualsBinding("zipcode_id", 1445));
			$sharon = $f->find("where zipcode_id = 1445");
		}
		catch(\Parm\Exception\ErrorException $e)
		{
			$exceptionCaught = true;
		}
		
		$this->assertTrue($exceptionCaught);
	}
	
	// return all objects
	function testFindAll()
	{
		$f = new ParmTests\Dao\ZipcodesDaoFactory();
		$allZipcodes = $f->findAll();
		$this->assertEquals('1776', count($allZipcodes));
	}
	
	// return all objects
	function testFirstObject()
	{
		$stein = new ParmTests\Dao\PeopleDaoObject();
		$stein->setFirstName("Gertrude");
		$stein->setLastName("Stein");
		$stein->setCreateDate(time());
		$stein->setCreateDatetime(time());
		$stein->setZipcodeId(72);
		$stein->setArchived(0);
		$stein->save();
		
		$steinId = $stein->getId();
		
		$f = new ParmTests\Dao\PeopleDaoFactory();
		$f->addBinding(new \Parm\Binding\EqualsBinding("people_id", $steinId));
		$steinClone = $f->getFirstObject();
		$this->assertEquals($stein->toJSON(), $steinClone->toJSON());
	}

	function testWhereEquals()
	{
		$perry = new ParmTests\Dao\PeopleDaoObject();
		$perry->setFirstName("Edward");
		$perry->setLastName("Perry");
		$perry->setCreateDate(time());
		$perry->setCreateDatetime(time());
		$perry->setZipcodeId(500);
		$perry->setArchived(0);
		$perry->save();
		
		$perryId = $perry->getId();
		
		$f = new ParmTests\Dao\PeopleDaoFactory();
		$f->whereEquals("people_id", $perryId);
		$perryClone = $f->getFirstObject();
		$this->assertEquals($perry->toJSON(), $perryClone->toJSON());
	}
	
	function testDelete()
	{
		$hoffa = new ParmTests\Dao\PeopleDaoObject();
		$hoffa->setFirstName("Jimmy");
		$hoffa->setLastName("Hoffa");
		$hoffa->setCreateDate(time());
		$hoffa->setCreateDatetime(time());
		$hoffa->setZipcodeId(1687);
		$hoffa->setArchived(0);
		$hoffa->save();
		
		$hoffaId = $hoffa->getId();
		
		$f = new ParmTests\Dao\PeopleDaoFactory();
		$f->whereEquals("people_id", $hoffaId);
		$oldCount = $f->count();
		
		$f->delete();
		
		$this->assertEquals(1, $oldCount);
		
		$f = new ParmTests\Dao\PeopleDaoFactory();
		$f->whereEquals("people_id", $hoffaId);
		$newCount = $f->count();
		
		$this->assertEquals(0, $newCount);
		
	}
	
	function testSelectClause()
	{
		$f =  new ParmTests\Dao\PeopleDaoFactory();
		$this->assertEquals("SELECT people.people_id,people.first_name,people.last_name,people.zipcode_id,people.archived,people.test_data_blob,people.create_date,people.create_datetime,people.create_timestamp", $f->getSelectClause());
	}
	
	function testSetSelectFields()
	{
		$f =  new ParmTests\Dao\PeopleDaoFactory();
		$f->setSelectFields("first_name","last_name");
		$this->assertEquals("SELECT people.people_id,people.first_name,people.last_name", $f->getSelectClause());
	}
	
	function testSingleSelectFields()
	{
		$f =  new ParmTests\Dao\PeopleDaoFactory();
		$f->setSelectFields("last_name");
		$this->assertEquals("SELECT people.people_id,people.last_name", $f->getSelectClause());
	}
	
	function testAddSelectField()
	{
		$f =  new ParmTests\Dao\PeopleDaoFactory();
		$f->setSelectFields(array("first_name"));
		$f->addSelectField("last_name");
		$this->assertEquals("SELECT people.people_id,people.first_name,people.last_name", $f->getSelectClause());
	}
	
	function testSetSelectFieldNullThrowsException()
	{
		$exceptionCaught = false;
		try
		{
			$f =  new ParmTests\Dao\PeopleDaoFactory();
			$f->setSelectFields(null);
		}
		catch(\Parm\Exception\ErrorException $e)
		{
			$exceptionCaught = true;
		}
		
		$this->assertTrue($exceptionCaught);
	}
	
	function testSelectDashedColumns()
	{
		$f = new CountryDaoFactory();
		$countries = $f->getObjects();
		
		$this->assertEquals(239,count($countries));
		
	}
	
}


?>
