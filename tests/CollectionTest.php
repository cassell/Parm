<?php

require dirname(__FILE__) . '/test.inc.php';

class CollectionTest extends PHPUnit_Framework_TestCase
{

    public function testCollectionInstance()
    {
        $f = new \ParmTests\Dao\ZipcodesDaoFactory();
        $collection = new \Parm\Collection($f);

        $this->assertFalse(is_array($collection));

        $this->assertTrue($collection instanceof \Iterator);
        $this->assertTrue($collection instanceof \Parm\Rows);
        $this->assertTrue($collection instanceof \Parm\Collection);

    }

    public function testCollectionIteration()
    {
        $zipCodeTotal = 0;

        $f = new \ParmTests\Dao\ZipcodesDaoFactory();

        foreach (new \Parm\Collection($f) as $zipcode) {

            $zipCodeTotal += (int) $zipcode->getZipcode();
        }

        $this->assertEquals(29498849,$zipCodeTotal);

    }

	public function testCollectionCount()
	{
		$f = new \ParmTests\Dao\ZipcodesDaoFactory();
		$f->whereEquals(\ParmTests\Dao\ZipcodesDaoObject::CITY_COLUMN,"Erie");
		$this->assertEquals(9,$f->count());

		$f = new \ParmTests\Dao\ZipcodesDaoFactory();
		$f->whereEquals(\ParmTests\Dao\ZipcodesDaoObject::CITY_COLUMN,"Erie");
		$collection = $f->getCollection();
		$this->assertEquals(9,$collection->getCount());


	}

}
