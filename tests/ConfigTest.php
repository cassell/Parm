<?php

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testDateStorageFormat()
    {
        \Parm\Config::setDateStorageFormat('Y-m-d');
        $this->assertEquals('Y-m-d',\Parm\Config::getDateStorageFormat());
    }

    /**
     * @test
     */
    public function testDatetimeStorageFormat()
    {
        \Parm\Config::setDatetimeStorageFormat('Y-m-d H:i:s');
        $this->assertEquals('Y-m-d H:i:s',\Parm\Config::getDatetimeStorageFormat());
    }

    /**
     * @test
     */
    public function testCaseSensitiveCollation()
    {
        \Parm\Config::setCaseSensitiveCollation('utf8_bin');
        $this->assertEquals('utf8_bin',\Parm\Config::getCaseSenstitiveCollation());
    }

    /**
     * @test
     */
    public function testAllConnections()
    {
        $this->assertGreaterThan(0,count(\Parm\Config::getAllConnections()));
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class,\Parm\Config::getAllConnections()['parm-global-tests']);
        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class,\Parm\Config::getAllConnections()['parm_namespaced_tests']);
    }

    /**
     * @test
     */
    public function testAddConnection()
    {
        \Parm\Config::addConnection('parm-global-tests', new Doctrine\DBAL\Connection([
            'dbname' => $GLOBALS['db_global_name'],
            'user' => $GLOBALS['db_global_username'],
            'password' => $GLOBALS['db_global_password'],
            'host' => $GLOBALS['db_global_host'],
            'driver' => 'pdo_mysql',
        ], new Doctrine\DBAL\Driver\PDOMySql\Driver(), null, null));
    }

    /**
     * @test
     */
    public function testSetupConnection()
    {
        \Parm\Config::setupConnection('parm-global-tests', $GLOBALS['db_global_name'], $GLOBALS['db_global_username'], $GLOBALS['db_global_password'], $GLOBALS['db_global_host']);
    }

    /**
     * @test
     * @expectedException \Parm\Exception\ErrorException
     */
    public function testAddConnectionWrongDriver()
    {
        \Parm\Config::addConnection('parm-global-tests', new Doctrine\DBAL\Connection([
            'dbname' => $GLOBALS['db_global_name'],
            'user' => $GLOBALS['db_global_username'],
            'password' => $GLOBALS['db_global_password'],
            'host' => $GLOBALS['db_global_host'],
            'driver' => 'pdo_mysql',
        ], new Doctrine\DBAL\Driver\PDOOracle\Driver(), null, null));
    }


    public function tearDown()
    {
        \Parm\Config::setDateStorageFormat('Y-m-d');
        \Parm\Config::setDatetimeStorageFormat('Y-m-d H:i:s');
        \Parm\Config::setCaseSensitiveCollation('utf8_bin');
    }
}
