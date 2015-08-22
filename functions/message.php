<?php

function message_display_messages_and_errors()
{
	if(defined('MESS'))
		message_print_message(MESS);
	for($i=0;defined('MESS'.$i);$i++)
	{
		message_print_message(constant('MESS'.$i));
	}
		
	if(defined('ERROR'))
		message_print_error(ERROR);
	for($i=0;defined('ERROR'.$i);$i++)
	{
		message_print_error(constant('ERROR'.$i));
	}
}

function message_print_message($message)
{
	echo "<div class=\"message_box well\">".$message."</div>";
}
function message_print_error($message)
{
	echo "<div class=\"message_box error well\">".$message."</div>";
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
function message_try_mysql($sql,$error_code, $success_message=NULL)
{
	if(mysql_query($sql))
	{
		if($success_message!=NULL)
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