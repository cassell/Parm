<?php



class RowsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testRowsIteration()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'Erie'");

        $zipCodeTotal = 0;

        foreach ($dp->getRows() as $row) {
            $zipCodeTotal += (int)$row['zipcode'];
        }

        $this->assertEquals(148551, $zipCodeTotal);

    }

    /**
     * @test
     */
    public function testIterateTwice()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'Erie'");

        $zipCodeTotal = 0;

        $rows = $dp->getRows();

        foreach ($rows as $row) {
            $zipCodeTotal += (int)$row['zipcode'];
        }

        foreach ($rows as $row) {
            $zipCodeTotal += (int)$row['zipcode'];
        }

        $this->assertEquals(148551 * 2, $zipCodeTotal);

    }

    /**
     * @test
     */
    public function testKey()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'Erie'");

        $zipCodeTotal = 0;

        $rows = $dp->getRows();

        $this->assertEquals(0, $rows->key());

        foreach ($rows as $row) {
            // do nothing
        }

        $this->assertEquals(9, $rows->key());

    }

    /**
     * @test
     */
    public function testIterateNTimes()
    {
        $dp = new Parm\DatabaseProcessor('parm_namespaced_tests');
        $dp->setSQL("select * from zipcodes where city = 'Erie'");

        $zipCodeTotal = 0;

        $rows = $dp->getRows();

        for ($i = 0; $i < 100; $i++) {
            foreach ($rows as $row) {
                $zipCodeTotal += (int)$row['zipcode'];
            }

        }

        $this->assertEquals(148551 * 100, $zipCodeTotal);

    }

}
