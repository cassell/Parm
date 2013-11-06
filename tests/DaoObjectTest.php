<?php

require dirname(__FILE__) . '/test.inc.php';

class DaoObjectTest extends PHPUnit_Framework_TestCase
{
	function testFindId()
	{
		$sharon = ParmTests\Dao\ZipcodesDaoObject::findId(1445);
		$this->assertEquals('16146', $sharon->getZipcode());
	}
	
	function testCreateClone()
	{
		$sharon = ParmTests\Dao\ZipcodesDaoObject::findId(1445);
		$sharonClone = clone $sharon;
		$this->assertEquals($sharon->getZipcode(),$sharonClone->getZipcode());
	}
	
	function testGetDatabaseName()
	{
		$new = new ParmTests\Dao\ZipcodesDaoObject();
		$this->assertEquals('parm_namespaced_tests', $new->getDatabaseName());
	}

	function testGetTableName()
	{
		$new = new ParmTests\Dao\ZipcodesDaoObject();
		$this->assertEquals('zipcodes', $new->getTableName());
	}

	function testGetIdField()
	{
		$new = new ParmTests\Dao\ZipcodesDaoObject();
		$this->assertEquals('zipcode_id', $new->getIdField());
	}
	
	function testInsertNewObject()
	{
		$new = new ParmTests\Dao\PeopleDaoObject();
		$new->setFirstName("Núñez"); // spanish
		$new->setLastName("κόσμε"); // greek
		$new->setCreateDate(time());
		$new->setCreateDatetime(time());
		$new->setZipcodeId(1529);
		$new->setArchived(0);
		$new->save();
		
		$test = ParmTests\Dao\PeopleDaoObject::findId($new->getId());
		
		if($test != null)
		{
			$this->assertEquals("Núñez", $test->getFirstName());
			$this->assertEquals("κόσμε", $test->getLastName());
		}
		else
		{
			$this->fail();
		}
		
	}
	
	function testUTF8Saving()
	{
		$new = new ParmTests\Dao\PeopleDaoObject();
		$new->setFirstName("Coach");
		$new->setLastName("Parmo");
		$new->setCreateDate(time());
		$new->setCreateDatetime(time());
		$new->setZipcodeId(1529);
		$new->setArchived(0);
		$new->save();
		
		$test = ParmTests\Dao\PeopleDaoObject::findId($new->getId());
		
		if($test != null)
		{
			$this->assertEquals($test->getLastName(), $new->getLastName());
		}
		else
		{
			$this->fail();
		}
		
	}
	
	function testJSONNewObject()
	{
		$new = new ParmTests\Dao\PeopleDaoObject();
		$new->setFirstName("James");
		$new->setLastName("Buchanan");
		$new->setZipcodeId(555);
		$new->setArchived(0);
		
		$this->assertEquals('a:10:{s:8:"peopleId";N;s:9:"firstName";s:5:"James";s:8:"lastName";s:8:"Buchanan";s:9:"zipcodeId";i:555;s:8:"archived";i:0;s:12:"testDataBlob";N;s:16:"testDataLongtext";N;s:10:"createDate";N;s:14:"createDatetime";N;s:2:"id";N;}', serialize($new->toJSON()));
		
	}
	
	function testJSON()
	{
		$buchananBirthplace = ParmTests\Dao\ZipcodesDaoObject::findId(555);
		$this->assertEquals('a:9:{s:9:"zipcodeId";s:3:"555";s:7:"zipcode";s:5:"17224";s:5:"state";s:2:"PA";s:9:"longitude";s:16:"-77.906230000000";s:8:"latitude";s:15:"39.957564000000";s:8:"archived";s:1:"0";s:4:"city";s:11:"Fort Loudon";s:9:"stateName";s:12:"Pennsylvania";s:2:"id";s:3:"555";}', serialize($buchananBirthplace->toJSON()));
	}
	
	function testJSONString()
	{
		$buchananBirthplace = ParmTests\Dao\ZipcodesDaoObject::findId(555);
		$this->assertEquals('{"zipcodeId":"555","zipcode":"17224","state":"PA","longitude":"-77.906230000000","latitude":"39.957564000000","archived":"0","city":"Fort Loudon","stateName":"Pennsylvania","id":"555"}',$buchananBirthplace->toJSONString());
	}

	
	
	
}

?>