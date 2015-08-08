<?php


require_once("config.php"); 
require_once("functions/db_connect.php");
$connection=db_connect(db_host, db_name, db_user, db_pass);

$serialized_db=file_get_contents ( "serialized_db.txt");
if($serialized_db!==FALSE)
{
	$create=unserialize($serialized_db);
	
	//First, just create the db's
	for($i=0; $i<count($create); $i++)
	{
		echo PREFIX;
		$create[$i]['Create Table']=str_replace("CREATE TABLE IF NOT EXISTS `".$create[$i]['Table']."`","CREATE TABLE IF NOT EXISTS `".PREFIX.$create[$i]['Table']."`",$create[$i]['Create Table']);
		echo "<pre>".$create[$i]['Create Table']."</pre>";
		//create the table if it doesn't exist
		if($cr=mysql_query($create[$i]['Create Table']))
		{
			echo "<pre>".$create[$i]['Create Table']."</pre>";
			echo "<pre>".mysql_error()."</pre>";
		}
	}
	
	echo "<p>Creation process complete</p>";
	
	//Now comes the tricky parts:
	//check that all columns exists
	//Check that all columns are the same types and stuff
	
	//Look for differences:
	for($i=0; $i<count($create); $i++)
	{
		echo "<h2>".PREFIX.$create[$i]['Table']."</h2>";
		
		$shell_rows=explode("\n",$create[$i]['Create Table']);
		
		
		if($cc=mysql_query("show create table ".PREFIX.$create[$i]['Table']))
		{
			if($c=mysql_fetch_assoc($cc))
			{
				$current_rows=explode("\n",$c['Create Table']);

				//Remove first row of both, because it only contains create table, wich can't be different.
				array_shift ( $shell_rows );
				array_shift ( $current_rows );
				
				foreach($shell_rows as $key => $s)
				{
					$shell_rows[$key]=preg_replace("/AUTO_INCREMENT=\d/","", $s);
				}
				foreach($current_rows as $key => $s)
				{
					$current_rows[$key]=preg_replace("/AUTO_INCREMENT=\d/","", $s);
				}

				echo "shell_rows<pre>".print_r($shell_rows,1)."</pre>";
				echo "current_rows<pre>".print_r($current_rows,1)."</pre>";
				
				//For each of $shell rows, check that the row exists in $current row
				foreach($shell_rows as $s)
				{
					if(!in_array($s,$current_rows))
					{
						echo "<br />This does not exist: $s";
					}
				}
				
			}
		}
		
		
		
		
		//CREATE [TEMPORARY] TABLE [IF NOT EXISTS] tbl_name
		//Create temporary table
		/*
		echo "<h2>".PREFIX.$create[$i]['Table']."</h2>";
		$sql=str_replace("CREATE TABLE IF NOT EXISTS `", "CREATE TEMPORARY TABLE `temp_", $create[$i]['Create Table']);
		if(mysql_query($sql))
		{
			//Compare
			$sql="SELECT column_name,ordinal_position,data_type,column_type FROM
			(
				SELECT
					column_name,ordinal_position,
					data_type,column_type,COUNT(1) rowcount
				FROM information_schema.columns
				WHERE table_schema=DATABASE()
				AND table_name IN ('".PREFIX.$create[$i]['Table']."','temp_".PREFIX.$create[$i]['Table']."')
				GROUP BY
					column_name,ordinal_position,
					data_type,column_type
				HAVING COUNT(1)=1
			) A;";
			if($dd=mysql_query($sql))
			{
				while($d=mysql_fetch_assoc($dd))
				{
					echo "<pre>".print_r($d,1)."</pre>";
				}
			}
		} */
	}
}

db_close($connection);
?>