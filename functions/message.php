<?php

function message_display_messages_and_errors()
{
	if(defined('SUCCESS_MESS'))
		message_print_success_message(SUCCESS_MESS);
	for($i=0;defined('SUCCESS_MESS'.$i);$i++)
	{
		message_print_success_message(constant('SUCCESS_MESS'.$i));
	}

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

function message_print_message($message, $return=false)
{
	$message="<div class=\"message_box well\">".$message."</div>";
	if($return)
		return $message;
	else
		echo $message;
}
function message_success_message($message)
{
    return "<div class=\"message_box success well\">".$message."</div>";
}
function message_print_success($message)
{
	message_print_success_message($message);
}
function message_print_success_message($message)
{
	echo message_success_message($message);
}

function message_error_message($message)
{
    return "<div class=\"message_box error well\">".$message."</div>";
}
function message_warning_message($message)
{
    return "<div class=\"message_box warning well\">".$message."</div>";
}
function message_message_box($type, $message)
{
    return "<div class=\"message_box ".$type." well\">".$message."</div>";
}
function message_print_error($message)
{
	echo message_error_message($message);
}

function message_add_error($error_mess)
{
	add_error($error_mess);
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
function message_try_mysql($sql,$error_code, $success_message=NULL, $print_now=FALSE, $generate_warning_on_fail=FALSE, $return_message_text=FALSE)
{
	if(@mysql_query($sql))
	{
		if($success_message!=NULL)
		{
            if($return_message_text)
                return $success_message;
            
			if($print_now)
				message_print_success_message($success_message);
			else
				message_add_success_message($success_message);
		}
		$insert_id=mysql_insert_id();
		$affected_rows=mysql_affected_rows();
		return ($insert_id ? $insert_id : $affected_rows);
	}
	else
	{
        $error_message=sprintf(_("Error code %s<br />SQL: %s<br />ERROR: %s"),$error_code, $sql, mysql_error());
        if($return_message_text)
            $return=$error_message;

        if($print_now)
            message_print_error($error_message);
        else if(!$return_message_text)
            add_error_mysql($error_code ,$sql, mysql_error());
        
		if($generate_warning_on_fail)
			message_trigger_warning($error_code, $sql, mysql_error());
			// trigger_error (sprintf(_("Error code %s	SQL: %s	ERROR: %s"),$error_code, $sql, mysql_error()));
        if($return_message_text)
               return $return;
		return FALSE;
	}
}
function message_trigger_warning($error_code, $sql, $mysql_error)
{
	trigger_error (sprintf(_("Error code %s	SQL: %s	ERROR: %s"),$error_code, $sql, $mysql_error), E_USER_WARNING);
}
function add_error_mysql($error_code,$sql, $mysql_error)
{
	add_error(sprintf(_("Error code %s<br />SQL: %s<br />ERROR: %s"),$error_code, $sql, $mysql_error));
}
function message_add_message($message, $headline=NULL)
{
	add_message($message, ($headline!=NULL ? $headline : NULL));
}
function add_message($message_body, $headline=NULL)
{
	if($headline!=NULL)
		$message=html_tag("h2", $headline).html_tag("p",$message_body);
	else
		$message=$message_body;
	
	if(!defined('MESS'))
		define('MESS', $message);
	else
	{
		for($i=0;defined('MESS'.$i);$i++);
		define('MESS'.$i, $message);
	}
}
function message_add_error_message($message)
{
	add_error($message);
}
function message_add_success_message($message)
{
	if(!defined('SUCCESS_MESS'))
		define('SUCCESS_MESS', $message);
	else
	{
		for($i=0;defined('SUCCESS_MESS'.$i);$i++);
		define('SUCCESS_MESS'.$i, $message);
	}
}

function message_progress_bar($percent, $max_decimals=2)
{
	$percent_display=round($percent,2);
	if($percent_display=round($percent,0))
		$percent_display=round($percent,0);

	return '<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100" style="min-width: 2em; width: '.$percent.'%;">
    '.$percent_display.'%
  </div>
</div>';
}
?>