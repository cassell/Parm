<?php



class DaoObjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testFindId()
    {
        $sharon = ParmTests\Dao\ZipcodesDaoObject::findId(1445);
        $this->assertEquals('16146', $sharon->getZipcode());
    }

    /**
     * @test
     */
    public function testCreateClone()
    {
        $sharon = ParmTests\Dao\ZipcodesDaoObject::findId(1445);
        $sharonClone = clone $sharon;
        $this->assertEquals($sharon->getZipcode(), $sharonClone->getZipcode());
        $this->assertEquals($sharon->getId(), $sharonClone->getId());
    }

    /**
     * @test
     */
    public function testGetDatabaseName()
    {
        $new = new ParmTests\Dao\ZipcodesDaoObject();
        $this->assertEquals('parm_namespaced_tests', $new->getDatabaseName());
    }

    /**
     * @test
     */
    public function testGetTableName()
    {
        $new = new ParmTests\Dao\ZipcodesDaoObject();
        $this->assertEquals('zipcodes', $new->getTableName());
    }

    /**
     * @test
     */
    public function testGetIdField()
    {
        $new = new ParmTests\Dao\ZipcodesDaoObject();
        $this->assertEquals('zipcode_id', $new->getIdField());
    }

    /**
     * @test
     */
    public function testSetStringValue()
    {

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setFirstName("String");

        $this->assertEquals('String', $new->getFirstName());
    }

    /**
     * @test
     */
    public function testSetDateValueString()
    {
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate('2012-03-04');
        $this->assertEquals('2012-03-04', $new->getCreateDate());
    }

    /**
     * @test
     */
    public function testSetDateValueTimestamp()
    {
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate(time());
        $this->assertEquals(date("Y-m-d"), $new->getCreateDate());
    }

    /**
     * @test
     */
    public function testSetDateValueDateTime()
    {
        $time = new \DateTime();

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate($time);
        $this->assertEquals($time->format("Y-m-d"), $new->getCreateDate());
    }

    /**
     * @test
     */
    public function testgetDateValueDateTimeObject()
    {
        $time = new \DateTime();

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate($time);

        $newTime = $new->getCreateDateDateTimeObject();

        $this->assertEquals($time->format("Y-m-d"), $newTime->format("Y-m-d"));
    }

    /**
     * @test
     */
    public function testSetDateValueNull()
    {
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate(null);
        $this->assertNull($new->getCreateDate());
        $this->assertNull($new->getCreateDate("Y-m-d H:i:s"));
        $this->assertNull($new->getCreateDateDateTimeObject());
    }

    /**
     * @test
     */
    public function testSetDateCrapValue()
    {
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate("Crap");
        $this->assertEquals("Crap",$new->getCreateDate());
        $this->assertEquals("Crap",$new->getCreateDate('Y-m-d'));
    }

    /**
     * @test
     */
    public function testSetDatetimeCrapValue()
    {
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDatetime("Crap");
        $this->assertEquals("Crap",$new->getCreateDatetime());
        $this->assertEquals("Crap",$new->getCreateDatetime('Y-m-d'));
    }

    /**
     * @test
     */
    public function testSetNumericalValueNull()
    {
        $new = new \ParmTests\Dao\ZipcodesDaoObject();
        $new->setLatitude(null);
        $this->assertNull($new->getLatitude());
    }

    /**
     * @test
     */
    public function testSetNumericalValue()
    {
        $new = new \ParmTests\Dao\ZipcodesDaoObject();
        $new->setLatitude(42.195488);
        $this->assertEquals(42.195488,$new->getLatitude());
    }


    /**
     * @test
     */
    public function testSetDateUsingDatetime()
    {
        $datetime = new \DateTime();
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDatetime($datetime);
        $this->assertEquals($datetime->format("Y-m-d H:i:s"),$new->getCreateDatetime());
    }

    /**
     * @test
     */
    public function testSetDatetimeValueTimestamp()
    {
        $time = time();

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDatetime($time);

        $this->assertEquals(date("Y-m-d H:i:s", $time), $new->getCreateDatetime());

    }

    /**
     * @test
     */
    public function testGetDateFieldValue()
    {
        $time = time();

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDate($time);

        $this->assertEquals(date("Y-m-d", $time), $new->getCreateDate());

        $this->assertEquals(date("Y-m-d 00:00:00", $time), $new->getCreateDate("Y-m-d H:i:s"));
        $this->assertNotEquals(date("Y-m-d H:i:s", $time), $new->getCreateDate("Y-m-d H:i:s"));

        $this->assertEquals(date("m/d/Y", $time), $new->getCreateDate("m/d/Y"));

    }

    /**
     * @test
     */
    public function testGetDatetimeFieldValue()
    {
        $time = time();

        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setCreateDatetime($time);

        $this->assertEquals(date("Y-m-d H:i:s", $time), $new->getCreateDatetime());
        $this->assertEquals(date("Y-m-d H:i:s", $time), $new->getCreateDatetime("Y-m-d H:i:s"));
        $this->assertEquals(date("m/d/Y", $time), $new->getCreateDatetime("m/d/Y"));

    }

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
     */
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

    /**
     * @test
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
            $this->assertEquals($new->getLastName(), $test->getLastName());
        } else {
            $this->fail();
        }

    }

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function testSaveWithoutSettingAnythingOnTheObject()
    {
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->save();
        $this->assertGreaterThan(0,$new->getId());
    }

    /**
     * @test
     * @expectedException \Parm\Exception\ErrorException
     */
    public function testIntegrityConstraintViolation()
    {
        $new = new ParmTests\Dao\PeopleDaoObject([]);
        $new->setVerified(null);
        $new->save();
    }

    /**
     * @test
     */
    public function testSetNull()
    {
        $new = new ParmTests\Dao\PeopleDaoObject([]);
        $new->setFirstName(null);
        $new->setLastName(null);
        $new->setArchived(null);
        $new->setZipcodeId(null);
        $new->save();
        $this->assertNull($new->getFirstName());
        $this->assertNull($new->getLastName());
        $this->assertNull($new->getArchived());
        $this->assertGreaterThan(0,$new->getId());

        $findAgain = ParmTests\Dao\PeopleDaoObject::findId($new->getId());
        $this->assertNull($findAgain->getFirstName());
        $this->assertNull($findAgain->getLastName());
        $this->assertNull($findAgain->getArchived());
        $this->assertEquals($new->getId(),$findAgain->getId());
    }

    /**
     * @test
     */
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

    /**
     * @test
     */
    public function testJSONNewObject()
    {
        $new = new ParmTests\Dao\PeopleDaoObject();
        $new->setFirstName("James");
        $new->setLastName("Buchanan");
        $new->setZipcodeId(555);
        $new->setArchived(0);

        $this->assertEquals('a:11:{s:8:"peopleId";N;s:9:"firstName";s:5:"James";s:8:"lastName";s:8:"Buchanan";s:9:"zipcodeId";i:555;s:8:"archived";i:0;s:8:"verified";i:0;s:12:"testDataBlob";N;s:10:"createDate";N;s:14:"createDatetime";N;s:15:"createTimestamp";N;s:2:"id";N;}', serialize($new->toJSON()));

    }

    /**
     * @test
     */
    public function testJSON()
    {
        $buchananBirthplace = ParmTests\Dao\ZipcodesDaoObject::findId(555);
        $this->assertEquals('a:9:{s:9:"zipcodeId";s:3:"555";s:7:"zipcode";s:5:"17224";s:5:"state";s:2:"PA";s:9:"longitude";s:16:"-77.906230000000";s:8:"latitude";s:15:"39.957564000000";s:8:"archived";s:1:"0";s:4:"city";s:11:"Fort Loudon";s:9:"stateName";s:12:"Pennsylvania";s:2:"id";i:555;}', serialize($buchananBirthplace->toJSON()));
    }

    /**
     * @test
     */
    public function testJSONString()
    {
        $buchananBirthplace = ParmTests\Dao\ZipcodesDaoObject::findId(555);
        $this->assertEquals('{"zipcodeId":"555","zipcode":"17224","state":"PA","longitude":"-77.906230000000","latitude":"39.957564000000","archived":"0","city":"Fort Loudon","stateName":"Pennsylvania","id":555}', $buchananBirthplace->toJSONString());
    }

    /**
     * @test
     */
    public function testDuplicateAsNewObject()
    {
        $fredonia = ParmTests\Dao\ZipcodesDaoObject::findId(565);
        $delaware = $fredonia->duplicateAsNewObject();
        $this->assertTrue($delaware->isNewObject());
        $this->assertEquals($fredonia->getZipcode(), $delaware->getZipcode());
        $this->assertEquals($fredonia['city'], $delaware['city']);
        $delaware->setCity("Delaware Township");
        $delaware->save();

        $this->assertEquals(1777, $delaware->getId());

        $newDelaware = ParmTests\Dao\ZipcodesDaoObject::findId($delaware->getId());

        $this->assertEquals("16124", $newDelaware->getZipcode());
        $this->assertEquals("PA", $newDelaware->getState());
        $this->assertEquals("Delaware Township", $newDelaware['city']);

        $newDelaware->delete();
    }

    /**
     * @test
     */
    public function testIdAsPrimaryKeySave()
    {
        $new = new \ParmTests\Dao\PeopleZipcodesLinkDaoObject();
        $new->setPeopleId(9);
        $new->setZipcodesId(66);
        $new->setCreateDate(time());
        $new->save();

        $test = \ParmTests\Dao\PeopleZipcodesLinkDaoObject::findId($new->getId());
        $this->assertEquals($new, $test);

    }

    /**
     * @test
     */
    public function testIdAsPrimaryKeyUpdate()
    {
        $update = \ParmTests\Dao\PeopleZipcodesLinkDaoObject::findId(1);
        $update->setPeopleId(10);
        $update->save();

        $test = \ParmTests\Dao\PeopleZipcodesLinkDaoObject::findId(1);
        $this->assertEquals($update->getPeopleId(), $test->getPeopleId());


    }

    /**
     * @test
     */
    public function testIdAsPrimaryKeyDelete()
    {
        $new = new \ParmTests\Dao\PeopleZipcodesLinkDaoObject();
        $new->setPeopleId(5);
        $new->setZipcodesId(50);
        $new->setCreateDate(time());
        $new->save();

        $test = \ParmTests\Dao\PeopleZipcodesLinkDaoObject::findId(2);
        $this->assertEquals($new->getPeopleId(), $test->getPeopleId());

        $new->delete();

        $test = \ParmTests\Dao\PeopleZipcodesLinkDaoObject::findId(2);
        $this->assertNull($test);

        $new = new \ParmTests\Dao\PeopleZipcodesLinkDaoObject();
        $new->setPeopleId(3);
        $new->setZipcodesId(30);
        $new->setCreateDate(time());
        $new->save();

        $this->assertEquals(3, $new->getId());

    }

    /**
     * @test
     */
    public function testIdPrimaryKeyJSON()
    {
        $f = new \ParmTests\Dao\PeopleZipcodesLinkDaoFactory();
        $f->setSelectFields([\ParmTests\Dao\PeopleZipcodesLinkDaoFactory::PEOPLE_ID_COLUMN, \ParmTests\Dao\PeopleZipcodesLinkDaoFactory::ZIPCODES_ID_COLUMN]);
        $test = $f->getFirstObject();
        $this->assertEquals('a:3:{s:2:"id";i:1;s:8:"peopleId";s:2:"10";s:10:"zipcodesId";s:2:"66";}', serialize($test->toJson()));
    }

    /**
     * @test
     */
    public function testGetDateFieldValueNull()
    {
        $nullDateObject = \ParmTests\Dao\PeopleDaoObject::findId(6);
        $this->assertEquals("", $nullDateObject->getCreateDate('Y-m-d'));
    }

    /**
     * @test
     */
    public function testFindIdWithFactory()
    {
        $city = ParmTests\Dao\ZipcodesDaoObject::findId(653, new ParmTests\Dao\ZipcodesDaoFactory());
        $this->assertEquals('16421', $city->getZipcode());
        $this->assertEquals(false, $city->getArchived());
    }

    /**
     * @test
     */
    public function testFindIdWithFactoryAndConnection()
    {
        $connection = \Parm\Config::getConnection('parm_namespaced_tests');

        $city = ParmTests\Dao\ZipcodesDaoObject::findId(1684, new ParmTests\Dao\ZipcodesDaoFactory($connection));
        $this->assertEquals('16510', $city->getZipcode());
    }

    /**
     * @test
     * @expectedException \Parm\Exception\GetFieldValueException
     */
    public function testUninitializedValue()
    {
        $city = new ParmTests\Dao\ZipcodesDaoObject([
            'zipcode' => '99999',
            'state'=> 'PA'

        ]);

        $city->getCity();
    }

    /**
     * @test
     */
    public function testUuidObjectsColumn()
    {
        $addressUuid = \Ramsey\Uuid\Uuid::uuid4()->toString();

        $zf = new \ParmTests\Dao\ZipcodesDaoFactory();
        $jimThorpe = $zf->findFirst("where city = 'Jim Thorpe'");

        $wahnetah = new \ParmTests\Dao\AddressDaoObject();
        $wahnetah->setId($addressUuid);
        $wahnetah->setStreetAddress('432 Center St');
        $wahnetah->setZipcodeId($jimThorpe->getId());
        $wahnetah->setCreateDatetime(time());

        $this->assertEquals($addressUuid,$wahnetah->getId());
        $this->assertEquals('432 Center St',$wahnetah->getStreetAddress());
        $this->assertEquals(763,$wahnetah->getZipcodeId());

        $wahnetah->save();

        $this->assertEquals($addressUuid,$wahnetah->getId());
        $this->assertEquals('432 Center St',$wahnetah->getStreetAddress());
        $this->assertEquals(763,$wahnetah->getZipcodeId());

        $af = new \ParmTests\Dao\AddressDaoFactory();
        $foundWahnetah = $af->findId($addressUuid);
        $this->assertEquals($addressUuid,$foundWahnetah->getId());
        $this->assertEquals('432 Center St',$foundWahnetah->getStreetAddress());
        $this->assertEquals(763,$foundWahnetah->getZipcodeId());


        $landLineUuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $telephoneLandLine = new \ParmTests\Dao\TelephoneDaoObject();
        $telephoneLandLine->setId($landLineUuid);
        $telephoneLandLine->setPhoneNumber('5703253180');
        $telephoneLandLine->setAddressId($wahnetah->getId());
        $telephoneLandLine->setCreateDatetime(new DateTime());

        $this->assertEquals($landLineUuid,$telephoneLandLine->getId());
        $this->assertEquals('5703253180',$telephoneLandLine->getPhoneNumber());
        $this->assertEquals($addressUuid,$telephoneLandLine->getAddressId());

        $telephoneLandLine->save();

        $this->assertEquals($landLineUuid,$telephoneLandLine->getId());
        $this->assertEquals('5703253180',$telephoneLandLine->getPhoneNumber());
        $this->assertEquals($addressUuid,$telephoneLandLine->getAddressId());

        $telephoneTtyUuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $telephoneTty = new \ParmTests\Dao\TelephoneDaoObject();
        $telephoneTty->setTelephoneId($telephoneTtyUuid);
        $telephoneTty->setPhoneNumber('0');
        $telephoneTty->setAddressId($foundWahnetah->getId());
        $telephoneTty->setCreateDatetime(new DateTime());

        $this->assertEquals($telephoneTtyUuid,$telephoneTty->getId());
        $this->assertEquals('0',$telephoneTty->getPhoneNumber());
        $this->assertEquals($addressUuid,$telephoneTty->getAddressId());

        $telephoneTty->save();

        $this->assertEquals($telephoneTtyUuid,$telephoneTty->getId());
        $this->assertEquals('0',$telephoneTty->getPhoneNumber());
        $this->assertEquals($addressUuid,$telephoneTty->getAddressId());

        $telephoneTty->setPhoneNumber('8778892457');
        $telephoneTty->save();

        $this->assertEquals('8778892457',$telephoneTty->getPhoneNumber());

    }

}
