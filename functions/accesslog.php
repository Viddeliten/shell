<?php

/***
*	function accesslog_log
*	- Logs a row everytime it is called. 
*	@Parameters:
*		- table: string, could be reference to another table, allthough it works with anything
*		- table_id: int, id of referenced table. Can be handy if your getting statistics for other tables
***/
function accesslog_log($table, $table_id)
{
	$values['table']=$table;
	$values['table_id']=$table_id;
	$values['HTTP_REFERER']=(isset($_SERVER['HTTP_REFERER']) ? "'".$_SERVER['HTTP_REFERER']."'" : NULL);
	$user=login_get_user();
	if($user!=NULL)
		$values['user_id']=$user;
	
	$user_agent=user_get_browser();

	$values['IP']=accesslogg_get_ip($user_agent['version']);

	$values['browser']=$user_agent['name'];
	$values['os']=$user_agent['platform'];
	
	$print_now=TRUE;
	$generate_warning_on_fail=TRUE;

	sql_insert(PREFIX."access_log", $values, NULL, 131312, $print_now, $generate_warning_on_fail);
}

function accesslogg_get_ip($salt)
{
	return crypt ($_SERVER['REMOTE_ADDR'], $salt);	
}


function accesslog_get_views($table, $table_id, $unique_visitors=TRUE)
{
	return sql_get_single(	($unique_visitors ? "count(DISTINCT(IP))" :"count(IP)"), 
							PREFIX."access_log", 
							"`table`='".sql_safe($table)."' AND table_id=".sql_safe($table_id)
						);
}
?>