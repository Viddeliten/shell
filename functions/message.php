<?php

function message_display_messages_and_errors()
{
	if(defined('MESS'))
		echo "<div class=\"message_box well well-sm\">".MESS."</div>";
	for($i=0;defined('MESS'.$i);$i++)
	{
		echo "<div class=\"message_box well well-sm\">".constant('MESS'.$i)."</div>";
	}
		
	if(defined('ERROR'))
		echo "<div class=\"message_box error well well-sm\">".ERROR."</div>";
	for($i=0;defined('ERROR'.$i);$i++)
	{
		echo "<div class=\"message_box error well well-sm\">".constant('ERROR'.$i)."</div>";
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