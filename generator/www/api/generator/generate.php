<?php

include('../api.inc.php');

if($_GET['database'] != null)
{
	foreach($generator->databases as $db)
	{
		if($db->databaseName != $_GET['database'])
		{
			unset($generator->databases[$db->databaseName]);
		}
		else
		{
			$resp['databaseName'] = $db->databaseName;
		}
	}
}

if(!$generator->generate())
{
	returnError('Error: ' . $generator->getErrorMessage());
}
else
{
	$resp['success'] = 1;
	returnResponse($resp);
}


?>œ