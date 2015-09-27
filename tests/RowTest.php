<?php



class RowTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testCamelCase()
    {
        $this->assertEquals('arabianCamel', Parm\Row::columnToCamelCase('arabian_camel'));

        $this->assertEquals('camulusFerus', Parm\Row::columnToCamelCase('camulus_ferus'));

    }
}
