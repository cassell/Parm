<?php

require dirname(__FILE__) . '/test.inc.php';

class DaoObjectTest extends PHPUnit_Framework_TestCase
{
    public function testFindId()
    {
        $sharon = ParmTests\Dao\ZipcodesDaoObject::findId(1445);
        $this->assertEquals('16146', $sharon->getZipcode());
    }

    public function testCreateClone()
    {
        $sharon = ParmTests\Dao\ZipcodesDaoObject::findId(1445);
        $sharonClone = clone $sharon;
        $this->assertEquals($sharon->getZipcode(),$sharonClone->getZipcode());
        $this->assertEquals($sharon->getId(),$sharonClone->getId());
    }

    public function testGetDatabaseName()
    {
        $new = new ParmTests\Dao\ZipcodesDaoObject();
        $this->assertEquals('parm_namespaced_tests', $new->getDatabaseName());
    }

    public function testGetTableName()
    {
        $new = new ParmTests\Dao\ZipcodesDaoObject();
        $this->assertEquals('zipcodes', $new->getTableName());
    }

    public function testGetIdField()
    {
        $new = new ParmTests\Dao\ZipcodesDaoObject();
        $this->assertEquals('zipcode_id', $new->getIdField());
    }

    public function testSetStringValue()
    {

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setFirstName("String");

        $this->assertEquals('String', $new->getFirstName());
    }

    public function testSetDateValueString()
    {
        $time = time();

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate('2012-03-04');
        $this->assertEquals('2012-03-04', $new->getCreateDate());
    }

    public function testSetDateValueTimestamp()
    {
        $time = time();

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate(time());
        $this->assertEquals(date("Y-m-d"), $new->getCreateDate());
    }

    public function testSetDateValueDateTime()
    {
        $time = new \DateTime();

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate($time);
        $this->assertEquals($time->format("Y-m-d"), $new->getCreateDate());
    }

    public function testgetDateValueDateTimeObject()
    {
        $time = new \DateTime();

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate($time);

        $newTime = $new->getCreateDateDateTimeObject();

        $this->assertEquals($time->format("Y-m-d"), $newTime->format("Y-m-d"));
    }

    public function testSetDateValueNull()
    {
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate(null);
        $this->assertEquals(null, $new->getCreateDate());
    }

    public function testSetDatetimeValueTimestamp()
    {
        $time = time();

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDatetime($time);

        $this->assertEquals(date("Y-m-d H:i:s",$time), $new->getCreateDatetime());

    }

	public function testGetDateFieldValue()
	{
		$time = time();

		$new = new ParmTests\Dao\PeopleDaoObject();
		$new->setCreateDate($time);

		$this->assertEquals(date("Y-m-d",$time), $new->getCreateDate());

		$this->assertEquals(date("Y-m-d 00:00:00",$time), $new->getCreateDate("Y-m-d H:i:s"));
		$this->assertNotEquals(date("Y-m-d H:i:s",$time), $new->getCreateDate("Y-m-d H:i:s"));

		$this->assertEquals(date("m/d/Y",$time), $new->getCreateDate("m/d/Y"));

	}

	public function testGetDatetimeFieldValue()
	{
		$time = time();

		$new = new ParmTests\Dao\PeopleDaoObject();
		$new->setCreateDatetime($time);

		$this->assertEquals(date("Y-m-d H:i:s",$time), $new->getCreateDatetime());
		$this->assertEquals(date("Y-m-d H:i:s",$time), $new->getCreateDatetime("Y-m-d H:i:s"));
		$this->assertEquals(date("m/d/Y",$time), $new->getCreateDatetime("m/d/Y"));

	}

    public function testInsertNewObject()
    {
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setFirstName("Coach");
        $new->setLastName("Parmo");
        $new->setCreateDate(time());
        $new->setCreateDatetime(time());
        $new->setZipcodeId(1529);
        $new->save();

        $test = ParmTests\Dao\PeopleDaoObject::findId($new->getId());

        if ($test != null) {
            $this->assertEquals($test->getLastName(), $new->getLastName());
			$this->assertEquals($test->getArchived(), $new->getArchived());
        } else {
            $this->fail();
        }

		echo $test->getArchived();

    }

	public function testInsertBooleanValuesNullAllowed()
	{
		$new = new ParmTests\Dao\PeopleDaoObject();
		$new->setFirstName("Winnie");
		$new->setLastName("Bool");
		$new->setArchived(true);
		$new->save();

		$test = ParmTests\Dao\PeopleDaoObject::findId($new->getId());

		if ($test != null) {
			$this->assertEquals($test->getLastName(), $new->getLastName());
			$this->assertEquals($test->getArchived(), true);
		} else {
			$this->fail();
		}


		$new = new ParmTests\Dao\PeopleDaoObject();
		$new->setFirstName("Winnie");
		$new->setLastName("Bool");
		$new->setArchived(false);
		$new->save();

		$test = ParmTests\Dao\PeopleDaoObject::findId($new->getId());

		if ($test != null) {
			$this->assertEquals($test->getLastName(), $new->getLastName());
			$this->assertEquals($test->getArchived(), false);
		} else {
			$this->fail();
		}

		$new = new ParmTests\Dao\PeopleDaoObject();
		$new->setFirstName("Winnie");
		$new->setLastName("Bool");
		$new->setArchived(NULL);
		$new->save();

		$test = ParmTests\Dao\PeopleDaoObject::findId($new->getId());

		if ($test != null) {
			$this->assertEquals($test->getLastName(), $new->getLastName());
			$this->assertEquals($test->getArchived(), NULL);
		} else {
			$this->fail();
		}

	}

	/*
	public function testInsertBooleanValuesNullNotAllowed()
	{
		$new = new ParmTests\Dao\PeopleDaoObject();
		$new->setFirstName("Winnie");
		$new->setLastName("Bool");
		$new->setVerified(true);
		$new->save();

		$test = ParmTests\Dao\PeopleDaoObject::findId($new->getId());

		if ($test != null) {
			$this->assertEquals($test->getLastName(), $new->getLastName());
			$this->assertEquals($test->getVerified(), true);
		} else {
			$this->fail();
		}

		$new = new ParmTests\Dao\PeopleDaoObject();
		$new->setFirstName("Winnie");
		$new->setLastName("Bool");
		$new->setVerified(false);
		$new->save();

		$test = ParmTests\Dao\PeopleDaoObject::findId($new->getId());

		if ($test != null) {
			$this->assertEquals($test->getLastName(), $new->getLastName());
			$this->assertEquals($test->getVerified(), false);
		} else {
			$this->fail();
		}

	}
	*/

    public function testInsertNewObjectByArray()
    {
        $new = new ParmTests\Dao\PeopleDaoObject(array(
            "first_name" => "Mister",
            "last_name" => "Muenster",
            "archived" => "0",
            ));
        $new->save();

        $test = ParmTests\Dao\PeopleDaoObject::findId($new->getId());

        if ($test != null) {
            $this->assertEquals($new->getLastName(),$test->getLastName());
        } else {
            $this->fail();
        }

    }

    public function testDeleteNewObjectFailed()
    {
        $exceptionCaught = false;
        try {
            $new = new ParmTests\Dao\PeopleDaoObject();
            $new->setFirstName("Coach");
            $new->setLastName("Parmo");
            $new->setCreateDate(time());
            $new->setCreateDatetime(time());
            $new->setZipcodeId(1529);
            $new->setArchived(0);
            $new->delete();
        } catch (Parm\Exception\RecordNotFoundException $e) {
            $exceptionCaught = true;
        }

        $this->assertTrue($exceptionCaught);

    }

    public function testUTF8Saving()
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

        if ($test != null) {
            $this->assertEquals("Núñez", $test->getFirstName());
            $this->assertEquals("κόσμε", $test->getLastName());
        } else {
            $this->fail();
        }

    }

    public function testJSONNewObject()
    {
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setFirstName("James");
        $new->setLastName("Buchanan");
        $new->setZipcodeId(555);
        $new->setArchived(0);

        $this->assertEquals('a:11:{s:8:"peopleId";N;s:9:"firstName";s:5:"James";s:8:"lastName";s:8:"Buchanan";s:9:"zipcodeId";i:555;s:8:"archived";N;s:8:"verified";s:1:"0";s:12:"testDataBlob";N;s:10:"createDate";N;s:14:"createDatetime";N;s:15:"createTimestamp";N;s:2:"id";N;}', serialize($new->toJSON()));

    }

    public function testJSON()
    {
        $buchananBirthplace = ParmTests\Dao\ZipcodesDaoObject::findId(555);
        $this->assertEquals('a:9:{s:9:"zipcodeId";s:3:"555";s:7:"zipcode";s:5:"17224";s:5:"state";s:2:"PA";s:9:"longitude";s:16:"-77.906230000000";s:8:"latitude";s:15:"39.957564000000";s:8:"archived";s:1:"0";s:4:"city";s:11:"Fort Loudon";s:9:"stateName";s:12:"Pennsylvania";s:2:"id";s:3:"555";}', serialize($buchananBirthplace->toJSON()));
    }

    public function testJSONString()
    {
        $buchananBirthplace = ParmTests\Dao\ZipcodesDaoObject::findId(555);
        $this->assertEquals('{"zipcodeId":"555","zipcode":"17224","state":"PA","longitude":"-77.906230000000","latitude":"39.957564000000","archived":"0","city":"Fort Loudon","stateName":"Pennsylvania","id":"555"}',$buchananBirthplace->toJSONString());
    }

    public function testDuplicateAsNewObject()
    {
        $fredonia = ParmTests\Dao\ZipcodesDaoObject::findId(565);
        $delaware = $fredonia->duplicateAsNewObject();
        $this->assertTrue($delaware->isNewObject());
        $this->assertEquals($fredonia->getZipcode(),$delaware->getZipcode());
        $this->assertEquals($fredonia['city'],$delaware['city']);
        $delaware->setCity("Delaware Township");
        $delaware->save();

        $this->assertEquals(1777,$delaware->getId());

        $newDelaware = ParmTests\Dao\ZipcodesDaoObject::findId($delaware->getId());

        $this->assertEquals("16124",$newDelaware->getZipcode());
        $this->assertEquals("PA",$newDelaware->getState());
        $this->assertEquals("Delaware Township",$newDelaware['city']);

        $newDelaware->delete();
    }

}
