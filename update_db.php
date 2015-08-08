<?php

//First, just create the db's
require_once("config.php"); 
require_once("functions/db_connect.php");
$connection=db_connect(db_host, db_name, db_user, db_pass);

$serialized_db=file_get_contents ( "serialized_db.txt");
if($serialized_db!==FALSE)
{
	$create=unserialize($serialized_db);
	for($i=0; $i<count($create); $i++)
	{
		echo PREFIX;
		$create[$i]['Create Table']=str_replace("CREATE TABLE IF NOT EXISTS `".$create[$i]['Table']."`","CREATE TABLE IF NOT EXISTS `".PREFIX.$create[$i]['Table']."`",$create[$i]['Create Table']);
		echo "<pre>".$create[$i]['Create Table']."</pre>";
		//create the table if it doesn't exist
		if(!mysql_query($create[$i]['Create Table']))
			echo "<pre>".mysql_error()."</pre>";
	}
}

db_close($connection);
?>