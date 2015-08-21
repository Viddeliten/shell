<?php

function message_display_messages_and_errors()
{
	if(defined('MESS'))
		echo "<div class=\"message_box well\">".MESS."</div>";
	for($i=0;defined('MESS'.$i);$i++)
	{
		echo "<div class=\"message_box well\">".constant('MESS'.$i)."</div>";
	}
		
	if(defined('ERROR'))
		echo "<div class=\"message_box error well\">".ERROR."</div>";
	for($i=0;defined('ERROR'.$i);$i++)
	{
		echo "<div class=\"message_box error well\">".constant('ERROR'.$i)."</div>";
	}
}

function add_error($error_mess)
{
	if(!defined('ERROR'))
		define('ERROR', $error_mess);
	else
	{
		for($i=0;defined('ERROR'.$i);$i++);
		define('ERROR'.$i, $error_mess);
	}
}
function message_try_mysql($sql,$error_code, $success_message)
{
	if(mysql_query($sql))
	{
		add_message($success_message);
		return TRUE;
	}
	else
	{
		add_error_mysql($error_code,$sql, mysql_error());
		return FALSE;
	}
}
function add_error_mysql($error_code,$sql, $mysql_error)
{
	add_error(sprintf(_("Error code %s<br />SQL: %s<br />ERROR: %s"),$error_code, $sql, $mysql_error));
}
function add_message($message)
{
	if(!defined('MESS'))
		define('MESS', $message);
	else
	{
		for($i=0;defined('MESS'.$i);$i++);
		define('MESS'.$i, $message);
	}
}

?>