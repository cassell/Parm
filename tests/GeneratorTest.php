<?php

class GeneratorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testGenerationSuccessful()
    {
        $this->assertFileExists(dirname(__FILE__) . '/dao/namespaced/PeopleDaoObject.php');
        $this->assertFileExists(dirname(__FILE__) . '/dao/namespaced/PeopleDaoFactory.php');
        $this->assertFileExists(dirname(__FILE__) . '/dao/namespaced/ZipcodesDaoObject.php');
        $this->assertFileExists(dirname(__FILE__) . '/dao/namespaced/ZipcodesDaoFactory.php');

        $this->assertFileExists(dirname(__FILE__) . '/dao/global/CityDaoObject.php');
        $this->assertFileExists(dirname(__FILE__) . '/dao/global/CityDaoFactory.php');
        $this->assertFileExists(dirname(__FILE__) . '/dao/global/CountryNationDaoObject.php');
        $this->assertFileExists(dirname(__FILE__) . '/dao/global/CountryNationDaoFactory.php');
    }
}
