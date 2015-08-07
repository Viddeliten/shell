<?php 
function db_connect($db_host, $db_name, $db_user, $db_pass)
{
	$conn=mysql_connect($db_host, $db_user,$db_pass)
		or die("MySQL-servern är okontaktbar.");
	$databas=mysql_select_db($db_name)
		or die("Databasen $db_name fungerar inte.");
		
	mysql_set_charset('utf8',$conn);
	
	return $conn;
}

function db_close($conn)
{
	mysql_close($conn);
}

function sql_print_results($alldata)
{
	//Prints sql results in a table

	echo "<table class=\"small table table-striped\"><tr>";
	for($i=0;$i<mysql_num_fields($alldata);$i++)
	{
		echo "<th>".mysql_field_name($alldata,$i)."</th>";
	}
	echo "</tr>";

	while($rad=mysql_fetch_row($alldata))
	{
		echo "<tr>";
		for($i=0;$i<mysql_num_fields($alldata);$i++)
		{
			echo "<td>".$rad[$i]."</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
}
?>