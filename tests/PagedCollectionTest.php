<?php

class PagedCollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testCollectionInstance()
    {
        $f = new \ParmTests\Dao\ZipcodesDaoFactory();
        $collection = new \Parm\PagedCollection($f);

        $this->assertFalse(is_array($collection));

        $this->assertTrue($collection instanceof \Iterator);
        $this->assertTrue($collection instanceof \Parm\Rows);
        $this->assertTrue($collection instanceof \Parm\Collection);
        $this->assertTrue($collection instanceof \Parm\PagedCollection);

    }

    /**
     * @test
     */
    public function testCollectionIteration()
    {
        $zipCodeTotal = 0;

        $f = new \ParmTests\Dao\ZipcodesDaoFactory();

        foreach (new \Parm\PagedCollection($f) as $zipcode) {

            $zipCodeTotal += (int)$zipcode->getZipcode();
        }

        $this->assertEquals(29498849, $zipCodeTotal);

    }

    /**
     * @test
     */
    public function testCollectionPageSize10()
    {
        $zipCodeTotal = 0;

        $f = new \ParmTests\Dao\ZipcodesDaoFactory();

        foreach (new \Parm\PagedCollection($f, 10) as $zipcode) {

            $zipCodeTotal += (int)$zipcode->getZipcode();
        }

        $this->assertEquals(29498849, $zipCodeTotal);

    }

    /**
     * @test
     */
    public function testCollectionPageSize10IterateAgain()
    {
        $zipCodeTotal = 0;

        $f = new \ParmTests\Dao\ZipcodesDaoFactory();
        $collection = new \Parm\PagedCollection($f, 10);

        foreach ($collection as $zipcode) {

            $zipCodeTotal += (int)$zipcode->getZipcode();
        }

        foreach ($collection as $zipcode) {

            $zipCodeTotal += (int)$zipcode->getZipcode();
        }

        foreach ($collection as $zipcode) {

            $zipCodeTotal += (int)$zipcode->getZipcode();
        }

        $this->assertEquals(29498849 * 3, $zipCodeTotal);

    }

    /**
     * @test
     */
    public function testCollectionPageSize1()
    {
        $zipCodeTotal = 0;

        $f = new \ParmTests\Dao\ZipcodesDaoFactory();

        foreach (new \Parm\PagedCollection($f, 1) as $zipcode) {

            $zipCodeTotal += (int)$zipcode->getZipcode();
        }

        $this->assertEquals(29498849, $zipCodeTotal);

    }

    /**
     * @test
     */
    public function testCollectionPageSize0ThrowsException()
    {

        try {
            $zipCodeTotal = 0;

            $f = new \ParmTests\Dao\ZipcodesDaoFactory();

            foreach (new \Parm\PagedCollection($f, 0) as $zipcode) {

                $zipCodeTotal += (int)$zipcode->getZipcode();
            }
        } catch (\Exception $e) {
            // do nothing
        }

        $this->assertInstanceOf("\\Parm\\Exception\\ErrorException", $e);

    }

    /**
     * @test
     */
    public function testCollectionWithFactoryBindings()
    {
        $f = new \ParmTests\Dao\ZipcodesDaoFactory();
        $f->whereEquals(\ParmTests\Dao\ZipcodesDaoObject::CITY_COLUMN, "Scranton");

        $zipCodeTotal = 0;

        foreach (new \Parm\PagedCollection($f, 2) as $zipcode) {

            $zipCodeTotal += (int)$zipcode->getZipcode();
        }

        $this->assertEquals(111039, $zipCodeTotal);

    }

}
