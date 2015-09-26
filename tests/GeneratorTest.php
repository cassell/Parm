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

    /**
     * @test
     * @expectedException \Parm\Exception\ErrorException
     */
    public function testInvalidDirectory()
    {
        $generator = new Parm\Generator\DatabaseGenerator(\Parm\Config::getConnection('parm_namespaced_tests'),null);
    }


    /**
     * @test
     */
    public function testNamespaceGeneration()
    {
        $generator = new Parm\Generator\DatabaseGenerator(\Parm\Config::getConnection('parm_namespaced_tests'),dirname(__FILE__) . '/dao/namespaced','ParmTests\Dao');
        $generator->generate();
    }

    /**
     * @test
     */
    public function testGlobalGeneration()
    {
        $generator = new Parm\Generator\DatabaseGenerator(\Parm\Config::getConnection('parm-global-tests'),dirname(__FILE__) . '/dao/global');
        $generator->generate();
    }
}
