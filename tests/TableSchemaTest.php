<?php

class TableSchemaTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testAutoIncrement()
    {
        $schemaGen = new \Parm\Generator\SchemaGenerator(\Parm\Config::getConnection('parm-global-tests'));

        $schemas = $schemaGen->getTableSchemas();

        $this->assertTrue($schemas['City']->isIdAutoIncremented());
        $this->assertTrue($schemas['Country-Nation']->isIdAutoIncremented());
        $this->assertFalse($schemas['Region']->isIdAutoIncremented());

        $schemaGen = new \Parm\Generator\SchemaGenerator(\Parm\Config::getConnection('parm_namespaced_tests'));
        $schemas = $schemaGen->getTableSchemas();

        $this->assertFalse($schemas['address']->isIdAutoIncremented());
        $this->assertTrue($schemas['people']->isIdAutoIncremented());
        $this->assertTrue($schemas['people_zipcodes_link']->isIdAutoIncremented());
        $this->assertFalse($schemas['telephone']->isIdAutoIncremented());
        $this->assertTrue($schemas['zipcodes']->isIdAutoIncremented());

    }
}