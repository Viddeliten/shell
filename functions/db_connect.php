<?php 
function db_connect($db_host, $db_name, $db_user, $db_pass)
{
	$conn=mysql_connect($db_host, $db_user,$db_pass)
		or die("MySQL-servern $db_host är okontaktbar.");
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

function sql_get($sql, $array=false, $index_column=NULL)
{
	$return=array();
	if($aa=mysql_query($sql))
	{
		if($array)
		{
			while($a=mysql_fetch_array($aa))
			{
				$return[]=$a;
			}	
		}
		else
		{
			while($a=mysql_fetch_assoc($aa))
			{
				if($index_column!==NULL && isset($a[$index_column]))
					$return[$a[$index_column]]=$a;
				else
					$return[]=$a;
			}
		}
	}
	else
		html_tag("p",mysql_error(),"error");
	return $return;
}

/************************************************/
/*		Function:sql_get_tables					*/
/*		Gets all tables in current database		*/
/************************************************/
function sql_get_tables()
{
	$tables=sql_get("show tables;", true);
	$t=array();
	foreach($tables as $table)
	{
		$t[]=$table[0];
	}
	return $t;
}

/************************************************/
/*		Function:sql_get_columns				*/
/*		Gets columns in a table					*/
/************************************************/
function sql_get_columns($selected_table)
{
	$tables=sql_get("SHOW COLUMNS FROM ".sql_safe($selected_table).";", true);
	$t=array();
	foreach($tables as $table)
	{
		$t[]=$table[0];
	}
	return $t;
}

/********************************************************/
/*		Function:sql_update								*/
/*		updates single column in specified table		*/
/********************************************************/
function sql_update($table, $column, $new_data, $id, $print_now=FALSE, $generate_warning_on_fail=FALSE)
{
	$sql="UPDATE ".sql_safe($table)."
	SET ".sql_safe($column)."='".sql_safe($new_data)."' 
	WHERE id=".sql_safe($id).";";

	message_try_mysql($sql,"1051110", NULL, $print_now, $generate_warning_on_fail);
}

function sql_get_single($column, $table, $where)
{
	$sql="SELECT ".sql_safe($column)." FROM ".sql_safe($table)." WHERE ".$where.";";
	preprint($sql,"DEBUG1059");
	$r=sql_get($sql);
	if(isset($r[0][$column]))
		return $r[0][$column];
	return NULL;
}

function sql_get_single_from_id($table, $column, $id)
{
	$sql="SELECT ".sql_safe($column)." FROM ".sql_safe($table)." WHERE id=".sql_safe($id).";";
	$r=sql_get($sql);
	if(isset($r[0][$column]))
		return $r[0][$column];
	return NULL;
}
?>