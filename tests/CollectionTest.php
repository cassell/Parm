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

}
