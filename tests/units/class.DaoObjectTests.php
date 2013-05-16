<?php

require_once(TESTS_CONFIG_PATH."../dao/class.PeopleDaoObject.php");
require_once(TESTS_CONFIG_PATH."../dao/class.ZipcodesDaoObject.php");

class DaoObjectTests extends \Enhance\TestFixture
{
	static function findId()
	{
		$f = new ZipcodesDaoFactory();
		$beverlyHills = ZipcodesDaoObject::findId(1968);
		
		\Enhance\Assert::areIdentical('90210',$beverlyHills->getZipcode());
	}
	
	function cloneNewObject()
	{
		$f = new ZipcodesDaoFactory();
		$beverlyHills = ZipcodesDaoObject::findId(1968);
		
		$notBeverlyHills = $beverlyHills->cloneNewObject();
		
		\Enhance\Assert::areIdentical('90210',$notBeverlyHills->getZipcode());
	}
	
//	function save()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	function delete()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	function toJSON()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	function setFieldValue()
//	{
//		\Enhance\Assert::inconclusive();
//	}
//	
//	function setDatetimeFieldValue()
//	{
//		\Enhance\Assert::inconclusive();
//	}
	
}
		
?>
