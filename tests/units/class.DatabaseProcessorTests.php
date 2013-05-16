<?php

class DatabaseProcessorTests extends \Enhance\TestFixture
{
    public function getMySqlResult() 
    {
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->setSQL('SHOW TABLES');
		$result = $dp->query();
		
		\Enhance\Assert::areIdentical(get_class($result), "mysqli_result");
    }
	
	public function countNumberOfTables()
	{
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->setSQL('SHOW TABLES');
		$result = $dp->query();
		
		\Enhance\Assert::areIdentical($result->num_rows, 2);
	}
	
	function process()
	{
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->setSQL("select * from zipcodes where city = 'Herndon' order by state asc");
		
		$count = 0;
		
		$dp->process(function($obj) use (&$count)
		{
			$count++;
		});
				
		\Enhance\Assert::areIdentical($count, 6);	
	}
	
	
	
	public function getArray()
	{
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->setSQL("select * from zipcodes where city = 'Reston' order by state asc");
		
		$array = $dp->getArray();
		
		\Enhance\Assert::isTrue(is_array($array));
		\Enhance\Assert::areIdentical(count($array), 3);
		
	}
	
	function getJSON()
	{
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->setSQL("select * from zipcodes where city = 'Herndon' order by state asc");
		
		$jsonObjects = $dp->getJSON();
		
		Enhance\Assert::areIdentical(count($jsonObjects), 6);
		Enhance\Assert::areIdentical($jsonObjects[3]['stateName'], 'Virginia');
		
	}
	
	function getFirstField()
	{
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->setSQL("select state as state_abbreviation from zipcodes where city = 'Greenville' order by state asc");
		
		Enhance\Assert::areIdentical($dp->getFirstField("state_abbreviation"), 'AL');
	}
	
	function update()
	{
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->update("delete from people");
		
		\Enhance\Assert::isTrue(true);
	}
	
	
	// using unbuffered mysql queries
	function unbufferedProcess()
	{
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->setSQL("select * from zipcodes");
		
		$sumZipcodes = 0;
		
		$dp->unbufferedProcess(function($obj) use (&$sumZipcodes)
		{
			$sumZipcodes += $obj['zipcode'];
		});
				
		\Enhance\Assert::areIdentical($sumZipcodes, 1596092501);	
	}
	
	function outputJSONString()
	{
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->setSQL("select * from zipcodes where city = 'Herndon' order by state asc limit 3");
		
		ob_start();
		
		$dp->outputJSONString();
				
		$json = ob_get_clean();
				
		\Enhance\Assert::areIdentical('[{"zipcodeId":"10405","zipcode":"67739","state":"KS","longitude":"-100.786870000000","latitude":"39.893743000000","city":"Herndon","stateName":"Kansas"},{"zipcodeId":"11113","zipcode":"42236","state":"KY","longitude":"-87.599350000000","latitude":"36.705024000000","city":"Herndon","stateName":"Kentucky"},{"zipcodeId":"25190","zipcode":"17830","state":"PA","longitude":"-76.805130000000","latitude":"40.690647000000","city":"Herndon","stateName":"Pennsylvania"}]', $json);
	}
	
	
}
		
?>
