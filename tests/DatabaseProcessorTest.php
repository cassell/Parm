<?php

class DatabaseProcessorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testPassingStringToConstructor()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL('select * from zipcodes');
        $this->assertGreaterThan(0,count($dp->getArray()));
    }

    /**
     * @test
     */
    public function testPassingConnectionToConstructor()
    {
        $dp = new Parm\DatabaseProcessor(new Doctrine\DBAL\Connection([
            'dbname' => $GLOBALS['db_namespaced_name'],
            'user' => $GLOBALS['db_namespaced_username'],
            'password' => $GLOBALS['db_namespaced_password'],
            'host' => $GLOBALS['db_namespaced_host'],
            'driver' => 'pdo_mysql',
        ], new Doctrine\DBAL\Driver\PDOMySql\Driver(), null, null));
        $dp->setSQL('select * from zipcodes');
        $this->assertGreaterThan(0,count($dp->getArray()));
    }

    /**
     * @test
     */
    public function testProcess()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'Erie'");

        $count = 0;

        $dp->process(function ($obj) use (&$count) {
            $count++;
        });

        $this->assertEquals(9, $count);

    }

    /**
     * @test
     */
    public function testGetArray()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'Scranton'");

        $array = $dp->getArray();

        $this->assertTrue(is_array($array));
        $this->assertEquals(6, count($array));
    }

    /**
     * @test
     */
    public function testGetArrayReturnsEmptyArray()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'Pittsburgh'");

        $array = $dp->getArray();

        $this->assertTrue(is_array($array));
        $this->assertEquals(0, count($array));
    }

    /**
     * @test
     */
    public function testGetJSON()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'State College'");
        $jsonObjects = $dp->getJSON();

        $this->assertTrue(is_array($jsonObjects));
        $this->assertEquals(count($jsonObjects), 2);
        $this->assertEquals("Pennsylvania", $jsonObjects[1]['stateName']);

    }

    /**
     * @test
     */
    public function testGetFirstField()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select zipcode from zipcodes where city = 'Scranton' order by zipcode asc");

        $this->assertEquals("18503", $dp->getFirstField("zipcode"));
    }

    /**
     * @test
     */
    public function testOutputJSONString()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("SELECT * FROM `zipcodes` WHERE `city` LIKE '%Freedom%';");

        ob_start();

        $dp->outputJSONString();

        $json = ob_get_clean();

        $this->assertEquals('[{"zipcodeId":"446","zipcode":"16637","state":"PA","longitude":"-78.433010000000","latitude":"40.340680000000","archived":"0","city":"East Freedom","stateName":"Pennsylvania"},{"zipcodeId":"567","zipcode":"15042","state":"PA","longitude":"-80.232080000000","latitude":"40.682566000000","archived":"0","city":"Freedom","stateName":"Pennsylvania"},{"zipcodeId":"1099","zipcode":"17349","state":"PA","longitude":"-76.681120000000","latitude":"39.753369000000","archived":"0","city":"New Freedom","stateName":"Pennsylvania"}]', $json);
    }

    /**
     * @test
     */
    public function testConvertTimezone()
    {
        if (array_key_exists("mysql_timezones_loaded", $GLOBALS) && $GLOBALS['mysql_timezones_loaded'] == 1) {
            $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
            $this->assertEquals(new \DateTime("2013-12-31 23:59:59"), $dp->convertTimezone("2014-01-01 02:59:59", "US/Eastern", "US/Pacific"));

            $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
            $this->assertEquals(new \DateTime("2005-08-09 11:55:21"), $dp->convertTimezone("1123581321", "US/Pacific", "US/Central"));

            $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
            $this->assertEquals(new \DateTime("2005-08-09 11:55:21"), $dp->convertTimezone(1123581321.5, "US/Pacific", "US/Central"));

            $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
            $this->assertEquals(new \DateTime("2005-08-09 14:55:21"), $dp->convertTimezone("1123581321", "US/Eastern", "Europe/London"));

        }
    }

}
