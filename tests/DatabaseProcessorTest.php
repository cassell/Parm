<?php

require dirname(__FILE__) . '/test.inc.php';

class DatabaseProcessorTest extends PHPUnit_Framework_TestCase
{
	public function passingNodeToConstructorTest()
	{
		$dp = new DatabaseProcessor('parm_tests');
		$dp->setSQL('select * from user');
		$result = $dp->query();
	}
}


?>
