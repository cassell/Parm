<?php

require_once('../generator.inc.php');

array_shift($argv);

$databaseArg = $argv[0];
$tableArg = $argv[1];
$actionArg = $argv[2];
$actionParamArg = $argv[3];

if($databaseArg)
{
	$database = null;
	foreach($generator->databases as $d)
	{
		if($d->databaseName == $databaseArg)
		{
			$database = $generator->databases[$db->databaseName];
		}
	}
}

if($database != null && $tableArg)
{
	if($actionArg == "extend" || $actionArg == "extended")
	{
		echo $database->getExtendedObjectStub($tableArg);
	}
}
else
{
	if($generator->generate())
	{
		echo "\n";
		echo "Generation Successful.";
		echo "\n";
	}
	else
	{
		echo "\n";
		echo "Generation Failed.";
		echo "\n";
	}
}

?>