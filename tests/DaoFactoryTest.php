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
	
	// return all objects
	function testFindAll()
	{
		$f = new Parm\Dao\ZipcodesDaoFactory();
		$allZipcodes = $f->findAll();
		$this->assertEquals('1776', count($allZipcodes));
	}
	
	// return all objects
	function testFirstObject()
	{
		$stein = new Parm\Dao\PeopleDaoObject();
		$stein->setFirstName("Gertrude");
		$stein->setLastName("Stein");
		$stein->setCreateDate(time());
		$stein->setCreateDatetime(time());
		$stein->setZipcodeId(72);
		$stein->setArchived(0);
		$stein->save();
		
		$steinId = $stein->getId();
		
		$f = new Parm\Dao\PeopleDaoFactory();
		$f->addBinding(new \Parm\Binding\EqualsBinding("people_id", $steinId));
		$steinClone = $f->getFirstObject();
		$this->assertEquals($stein->toJSON(), $steinClone->toJSON());
	}
	
	function testWhereEquals()
	{
		$perry = new Parm\Dao\PeopleDaoObject();
		$perry->setFirstName("Edward");
		$perry->setLastName("Perry");
		$perry->setCreateDate(time());
		$perry->setCreateDatetime(time());
		$perry->setZipcodeId(500);
		$perry->setArchived(0);
		$perry->save();
		
		$perryId = $perry->getId();
		
		$f = new Parm\Dao\PeopleDaoFactory();
		$f->whereEquals("people_id", $perryId);
		$perryClone = $f->getFirstObject();
		$this->assertEquals($perry->toJSON(), $perryClone->toJSON());
	}
	
	function testDelete()
	{
		$hoffa = new Parm\Dao\PeopleDaoObject();
		$hoffa->setFirstName("Jimmy");
		$hoffa->setLastName("Hoffa");
		$hoffa->setCreateDate(time());
		$hoffa->setCreateDatetime(time());
		$hoffa->setZipcodeId(1687);
		$hoffa->setArchived(0);
		$hoffa->save();
		
		$hoffaId = $hoffa->getId();
		
		$f = new Parm\Dao\PeopleDaoFactory();
		$f->whereEquals("people_id", $hoffaId);
		$oldCount = $f->count();
		
		$f->delete();
		
		$this->assertEquals(1, $oldCount);
		
		$f = new Parm\Dao\PeopleDaoFactory();
		$f->whereEquals("people_id", $hoffaId);
		$newCount = $f->count();
		
		$this->assertEquals(0, $newCount);
		
	}
	
//	
//	// generate the select clause from $this->fields
//	function getSelectClause()
//	{
//		$f = new ZipcodesDaoFactory();
//		
//		\Enhance\Assert::areIdentical('s:129:"SELECT zipcodes.zipcode_id,zipcodes.zipcode,zipcodes.state,zipcodes.longitude,zipcodes.latitude,zipcodes.city,zipcodes.state_name";',serialize($f->getSelectClause()));
//	}
//	
//	function setSelectFields()
//	{
//		$f = new ZipcodesDaoFactory();
//		$f->setSelectFields(array("zipcode","state"));
//		\Enhance\Assert::areIdentical('s:58:"SELECT zipcodes.zipcode_id,zipcodes.zipcode,zipcodes.state";',serialize($f->getSelectClause()));
//		
//		$f = new ZipcodesDaoFactory();
//		$f->setSelectFields(array("zipcodes.longitude","zipcodes.latitude"));
//		\Enhance\Assert::areIdentical('s:63:"SELECT zipcodes.zipcode_id,zipcodes.longitude,zipcodes.latitude";',serialize($f->getSelectClause()));
//		
//	}
//	
//	function addSelectField()
//	{
//		$f = new ZipcodesDaoFactory();
//		$f->setSelectFields(array("zipcode"));
//		$f->addSelectField("state");
//		
//		\Enhance\Assert::areIdentical('s:58:"SELECT zipcodes.zipcode_id,zipcodes.zipcode,zipcodes.state";',serialize($f->getSelectClause()));
//	}
//	
//    
//    function getFromClause()
//    {
//		$f = new ZipcodesDaoFactory();
//		\Enhance\Assert::areIdentical('FROM zipcodes',$f->getFromClause());
//    }
//	
//	private static function clearPeopleTestData()
//	{
//		$dp = new DatabaseProcessor('sqlicious_test');
//		$dp->update("TRUNCATE TABLE `people`;");
//	}
//	
//	private static function insertPeopleTestData()
//	{
//		self::clearPeopleTestData();
//		
//		$dp = new DatabaseProcessor('sqlicious_test');
//		
//		$dp->update("INSERT INTO `people` (`people_id`, `first_name`, `last_name`, `zipcode_id`, `archived`, `create_date`, `create_datetime`)
//VALUES
//	(1, 'Barack', 'Obama', 4505, 0, now(), now()),
//	(2, 'George', 'Bush', 4505, 0, now(), now()),
//	(3, 'Bill', 'Clinton', 4505, 0, now(), now());
//");
//
//	}
//	
//	
//	// joins
//	function join()
//	{
//		self::insertPeopleTestData();
//		
//		$f = new PeopleDaoFactory();
//		
//		$f->join("join zipcodes on zipcodes.zipcode_id = people.zipcode_id");
//		$f->addBinding(new EqualsBinding("zipcodes.state", "DC"));
//
//		\Enhance\Assert::areIdentical(3,$f->count());
//		
//		self::clearPeopleTestData();
//		
//	}
//	
//	
//	// group by
//	function groupBy()
//	{
//		$f = new ZipcodesDaoFactory();
//		$f->groupBy("state");
//		$f->addBinding(new EqualsBinding("zipcodes.state", "DC"));
//		
//		\Enhance\Assert::areIdentical(28,$f->count());
//		
//	}
//	
//	// order by
//	function orderBy()
//	{
//		$f = new ZipcodesDaoFactory();
//		$f->orderBy("zipcode","desc");
//		$f->addBinding(new EqualsBinding("zipcodes.state", "VA"));
//		$f->addBinding(new EqualsBinding("city", "Herndon"));
//		
//		$herndon = $f->getFirstObject();
//		
//		\Enhance\Assert::areIdentical('20171',$herndon->getZipcode());
//		
//	}
//	
//	public function equalsBinding()
//	{
//		$f = new ZipcodesDaoFactory();
//		$f->addBinding(new EqualsBinding('state', 'PA'));
//		
//		\Enhance\Assert::areIdentical($f->count(), 1776);
//	}
//	
//	public function containsBinding()
//	{
//		$f = new ZipcodesDaoFactory();
//		$f->addBinding(new ContainsBinding('state_name', 'New'));
//		
//		\Enhance\Assert::areIdentical($f->count(), 2877);
//	}
//	
//	public function getFirstObject()
//	{
//		$f = new ZipcodesDaoFactory();
//		$f->addBinding(new EqualsBinding('zipcode', '20170'));
//		
//		$herndon = $f->getFirstObject();
//		
//		\Enhance\Assert::areIdentical(get_class($herndon), "ZipcodesDaoObject");
//		\Enhance\Assert::areIdentical($herndon->getCity(), "Herndon");
//		\Enhance\Assert::areIdentical($herndon->getStateName(), "Virginia");
//	}
	
	
//	function orderByAsc()
//	{
//		\Enhance\Assert::inconclusive();
//	}
	
	// limits
//	function limit()
//	{
//		$f = new ZipcodesDaoFactory();
//		$f->orderBy("zipcode");
//		$f->addBinding(new EqualsBinding("zipcodes.state", "VA"));
//		$f->limit(10,5);
//		print_r($f->getFirstObject());
//		
//		$herndon = $f->getFirstObject();
//		
//		\Enhance\Assert::areIdentical('20171',$herndon->getZipcode());
//		
//		
//		\Enhance\Assert::inconclusive();
//	}
	
//	function delete()
//	{
//		\Enhance\Assert::inconclusive();
//	}
	
//	function count()
//	{
//       $f = new ZipcodesDaoFactory();
//	   \Enhance\Assert::areIdentical($f->count(), 33178);
//	   
//	   
//	   $f = new ZipcodesDaoFactory();
//	   $f->addBinding("state LIKE 'PA'");
//	   \Enhance\Assert::areIdentical($f->count(), 1776);
//	}
		
//    function sum()
//    {
//         \Enhance\Assert::inconclusive();
//    }
//    
//	function paging()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//    
//    public function truncateTable()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//    /* below are functions that are slowly being phased out */
//    // used to do custom queries, uses the same get select clause that the query() method 
//	function find()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//    
//    function deleteWhere()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	// find the first object matching the clause
//	function findFirst()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	function findDistinctField()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	function findField()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	function findFirstField()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	function getCount()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	function getMaxField()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	function getSumField()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//    // deprecate old naming convetion
//	function orderByField()
//	{
//		\Enhance\Assert::inconclusive();
//	}



	
}


?>
