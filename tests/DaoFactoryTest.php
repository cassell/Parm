<?php



class DaoFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testFindId()
    {
        $f = new ParmTests\Dao\ZipcodesDaoFactory();
        $sharon = $f->findId(1445);
        $this->assertEquals('16146', $sharon->getZipcode());
    }

    /**
     * @test
     */
    public function testFind()
    {
        $f = new ParmTests\Dao\ZipcodesDaoFactory();
        $objects = $f->find("where zipcode_id = 1445");
        $sharon = reset($objects);
        $this->assertEquals('16146', $sharon->getZipcode());
    }

    /**
     * @test
     */
    public function testFindThrowException()
    {
        $exceptionCaught = false;
        try {
            $f = new ParmTests\Dao\ZipcodesDaoFactory();
            $f->addBinding(new \Parm\Binding\EqualsBinding("zipcode_id", 1445));
            $sharon = $f->find("where zipcode_id = 1445");
        } catch (\Parm\Exception\ErrorException $e) {
            $exceptionCaught = true;
        }

        $this->assertTrue($exceptionCaught);
    }

    /**
     * @test
     */
    public function testFindAll()
    {
        $f = new ParmTests\Dao\ZipcodesDaoFactory();
        $allZipcodes = $f->findAll();
        $this->assertEquals('1776', count($allZipcodes));
    }

    /**
     * @test
     */
    public function testFirstObject()
    {
        $stein = new ParmTests\Dao\PeopleDaoObject();
        $stein->setFirstName("Gertrude");
        $stein->setLastName("Stein");
        $stein->setCreateDate(time());
        $stein->setCreateDatetime(time());
        $stein->setZipcodeId(72);
        $stein->setArchived(false);
        $stein->save();

        $steinId = $stein->getId();

        $f = new ParmTests\Dao\PeopleDaoFactory();
        $f->addBinding(new \Parm\Binding\EqualsBinding("people_id", $steinId));
        $steinClone = $f->getFirstObject();
        $this->assertEquals($stein->toJSON(), $steinClone->toJSON());
    }

    /**
     * @test
     */
    public function testWhereEquals()
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

    /**
     * @test
     */
    public function testDelete()
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

    /**
     * @test
     */
    public function testSelectClause()
    {
        $f = new ParmTests\Dao\PeopleDaoFactory();
        $this->assertEquals("SELECT people.people_id,people.first_name,people.last_name,people.zipcode_id,people.archived,people.verified,people.test_data_blob,people.create_date,people.create_datetime,people.create_timestamp", $f->getSelectClause());
    }

    /**
     * @test
     */
    public function testSetSelectFields()
    {
        $f = new ParmTests\Dao\PeopleDaoFactory();
        $f->setSelectFields("first_name", "last_name");
        $this->assertEquals("SELECT people.people_id,people.first_name,people.last_name", $f->getSelectClause());
    }

    /**
     * @test
     */
    public function testSingleSelectFields()
    {
        $f = new ParmTests\Dao\PeopleDaoFactory();
        $f->setSelectFields("last_name");
        $this->assertEquals("SELECT people.people_id,people.last_name", $f->getSelectClause());
    }

    /**
     * @test
     */
    public function testAddSelectField()
    {
        $f = new ParmTests\Dao\PeopleDaoFactory();
        $f->setSelectFields(array("first_name"));
        $f->addSelectField("last_name");
        $this->assertEquals("SELECT people.people_id,people.first_name,people.last_name", $f->getSelectClause());
    }

    /**
     * @test
     */
    public function testSetSelectFieldNullThrowsException()
    {
        $exceptionCaught = false;
        try {
            $f = new ParmTests\Dao\PeopleDaoFactory();
            $f->setSelectFields(null);
        } catch (\Parm\Exception\ErrorException $e) {
            $exceptionCaught = true;
        }

        $this->assertTrue($exceptionCaught);
    }

    /**
     * @test
     */
    public function testSelectDashedColumns()
    {
        $f = new CountryNationDaoFactory();
        $countries = $f->getObjects();

        $this->assertEquals(239, count($countries));

    }

    /**
     * @test
     */
    public function testWorkingWithBindings()
    {
        $f = new \ParmTests\Dao\ZipcodesDaoFactory();
        $f->whereEquals(\ParmTests\Dao\ZipcodesDaoObject::CITY_COLUMN, "Erie");
        $f->addBinding("latitude > 42.1");

        $zipCodeTotal = 0;

        foreach ($f->getObjects() as $zipcode) {

            $zipCodeTotal += (int)$zipcode->getZipcode();
        }

        $this->assertEquals(99028, $zipCodeTotal);

    }

    /**
     * @test
     */
    public function testPageProcess()
    {
        $f = new \ParmTests\Dao\ZipcodesDaoFactory();
        $f->whereEquals(\ParmTests\Dao\ZipcodesDaoObject::CITY_COLUMN, "Erie");
        $f->addBinding("latitude > 42.1");

        $zipCodeTotal = 0;

        $f->pagedProcess(2, function ($zipcode) use (&$zipCodeTotal) {

            $zipCodeTotal += (int)$zipcode->getZipcode();
        });

        $this->assertEquals(99028, $zipCodeTotal);

    }

    /**
     * @test
     */
    public function testOrderBy()
    {
        $f = new CountryNationDaoFactory();
        $f->orderBy("Region", "asc");
        $this->assertEquals(CountryNationDaoObject::findId(12), $f->getFirstObject());

        $f = new CountryNationDaoFactory();
        $f->orderBy("Region");
        $this->assertEquals(CountryNationDaoObject::findId(12), $f->getFirstObject());

        $f = new CountryNationDaoFactory();
        $f->orderBy("Region", "desc");
        $f->orderBy("Name", "asc");
        $this->assertEquals(CountryNationDaoObject::findId(16), $f->getFirstObject());

        $f = new CountryNationDaoFactory();
        $f->orderBy("Region", "asc");
        $f->orderBy("Name", "DESC");
        $this->assertEquals(CountryNationDaoObject::findId(188), $f->getFirstObject());

        $f = new CountryNationDaoFactory();
        $f->orderBy("Region asc, Name desc");
        $this->assertEquals(CountryNationDaoObject::findId(188), $f->getFirstObject());


    }


}
