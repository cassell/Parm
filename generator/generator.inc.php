<?php

if(defined("SQLICIOUS_THIS_IS_PRODUCTION"))
{
	exit;
}

require_once(str_replace("/generator","",dirname(__FILE__))."/sqlicious.inc.php");
require_once(SQLICIOUS_INCLUDE_PATH.'/generator/lib/class.SQLiciousGenerator.php');

if(defined("SQLICIOUS_CONFIG_GLOBAL") && array_key_exists(SQLICIOUS_CONFIG_GLOBAL, $GLOBALS))
{
	$generator = new SQLiciousGenerator();
	
	foreach($GLOBALS[SQLICIOUS_CONFIG_GLOBAL]->getDatabases() as $name => $config)
	{
		$db = new SQLiciousGeneratorDatabase($config);
		
		$generator->addDatabase($db);
	}
	
}
else
{
	echo "Database Configuration Not Found";
	exit;
}


?>