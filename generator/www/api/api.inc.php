<?php

define('GENERATOR_FOLDER', dirname(dirname(dirname(__FILE__))) . '/');

include(GENERATOR_FOLDER.'generator.inc.php');

$resp = array();

function utf8EncodeArray($array)
{
    foreach($array as $key => $value)
    {
    	if(is_array($value))
    	{
    		$array[$key] = utf8EncodeArray($value);
    	}
    	else
    	{
    		$array[$key] = utf8_encode($value);
    	}
    }
       
    return $array;
}

function jsonEncode($array)
{
	return json_encode(utf8EncodeArray($array));
}

function returnResponse($a)
{
	echo jsonEncode($a);
	exit;
}

function returnErrors($errors)
{
	$resp['errors'] = $errors;
	returnResponse($resp);
}

function returnError($error)
{
	returnErrors(array($error));
}




?>