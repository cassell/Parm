<?php

include('../api.inc.php');

$db = $generator->databases[$_GET['database']];

if($db != null)
{
	$resp['databaseName'] = $_GET['database'];
	foreach($db->getTableNames() as $name)
	{
		$resp['tables'][] = array("tableName" => $name, "databaseName" => $_GET['database']);
	}
	returnResponse($resp);
}
else
{
	returnError('Database not found.');
}


?>