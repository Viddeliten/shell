<?php

function accesslogg_log($table, $table_id)
{
	$values['table']=$table;
	$values['table_id']=$table_id;
	$values['HTTP_REFERER']=(isset($_SERVER['HTTP_REFERER']) ? "'".$_SERVER['HTTP_REFERER']."'" : NULL);
	$user=login_get_user();
	if($user!=NULL)
		$values['user_id']=$user;
	
	$user_agent=user_get_browser();
	preprint($user_agent,"user_agent");
	$values['IP']=accesslogg_get_ip($user_agent['version']);

	$values['browser']=$user_agent['name'];
	$values['os']=$user_agent['platform'];
	
	$print_now=TRUE;
	$generate_warning_on_fail=TRUE;
	preprint($values, "values");
	sql_insert(PREFIX."access_log", $values, NULL, 131312, $print_now, $generate_warning_on_fail);
}

function accesslogg_get_ip($salt)
{
	// preprint($_SERVER['REMOTE_ADDR']." - ".$_SERVER['HTTP_USER_AGENT'], "remote user agent");
	
	// preprint(user_get_browser(), "get_browser");
	
	return crypt ($_SERVER['REMOTE_ADDR'], $salt);	
}
?>