<?php

include('../api.inc.php');

$db = $generator->databases[$_GET['database']];
$table = $_GET['table'];

if($db != null)
{
	if($table != null)
	{
		$resp['code'] = $db->getExtendedObjectStub($table);
		returnResponse($resp);
	}
	else
	{
		returnError('Table not found.');
	}
	
}
else
{
	returnError('Database not found.');
}


?>