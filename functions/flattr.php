<?php

function flattr_get_flattr_choice($user_id, $type)
{
	if($uu=mysql_query("SELECT flattrID, showFlattr FROM ".PREFIX."flattr WHERE user_id='".sql_safe($user_id)."';"))
	{
		if($u=mysql_fetch_array($uu))
		{
			if($u['flattrID']==NULL || $u['flattrID']=="")
				return NULL;
			else
			{
				$choices=unserialize($u['showFlattr']);
				// echo "<pre>".print_r($choices,1)."</pre>";
				if(in_array($type,$choices))
					return TRUE;
				else
					return FALSE;
			}
		}
	}
	return NULL;
}

function flattr_get_flattr_choices($user_id)
{
	if($uu=mysql_query("SELECT flattrID, showFlattr FROM ".PREFIX."flattr WHERE user_id='".sql_safe($user_id)."';"))
	{
		if($u=mysql_fetch_array($uu))
		{
			if($u['showFlattr']==NULL)
				return "NULL";
			return $u['showFlattr'];
		}
		return NULL;
	}
	return 0;
}

function flattr_get_flattrID($user_id)
{
	if($uu=mysql_query("SELECT flattrID, showFlattr FROM ".PREFIX."flattr WHERE user_id='".sql_safe($user_id)."';"))
	{
		if($u=mysql_fetch_array($uu))
		{
			return $u['flattrID'];
		}
	}
	return FALSE;
}

function flattr_get_button_code($user_id, $flattr_id, $type, $url, $title, $description)
{
	if(NULL!==$user_id && flattr_get_flattr_choice($user_id, $type))
		$flattrID=flattr_get_flattrID($user_id);
	else if($flattr_id!==NULL)
		$flattrID=$flattr_id;
	else
		$flattrID=NULL;
	
	if($flattrID)
	{
		return flattr_button_show($flattrID, $url, $title, $description, 'compact', 'en_GB', true);
	}
	return NULL;
}

function flattr_button_show($uid, $url, $title, $description, $button, $language, $return_code=false)
{
	$script_id=password_generate(32);
	// echo "<script>
	// echo "<script id='fbwxhy2'>
	$code="<script id='$script_id'>
	(function(i){var f,s=document.getElementById(i);f=document.createElement('iframe');f.src='//api.flattr.com/button/view/?uid=".$uid."&title=".$title."&button=".$button."&description=".sql_safe($description)."&url='+encodeURIComponent('".$url."');f.title='Flattr';f.height=20;f.width=110;f.style.borderWidth=0;s.parentNode.insertBefore(f,s);})('$script_id');</script>";
	if($return_code)
		return $code;
	else
		echo $code;
}

function flattr_set_flattrID($user_id, $flattr_id)
{
	//If the new ID is different from current
	$current_id=flattr_get_flattrID($user_id);
	if(strcmp($flattr_id,$current_id))
	{
		//set it
		if($current_id===FALSE)
			$sql="INSERT INTO ".PREFIX."flattr SET flattrID='".sql_safe($flattr_id)."', user_id='".sql_safe($user_id)."';";
		else
			$sql="UPDATE ".PREFIX."flattr SET flattrID='".sql_safe($flattr_id)."' WHERE user_id='".sql_safe($user_id)."';";
		
		message_try_mysql($sql,"214776", _("New Flattr ID set"));
	}
}
function flattr_set_flattr_choice($user_id, $flattr_choice)
{
	// echo "flattr_set_flattr_choice($user_id, $flattr_choice)";
	$current_choices=flattr_get_flattr_choices($user_id);
	$new_choices=serialize($flattr_choice);
	// echo "<pre>current_choices:".print_r($current_choices,1)."</pre>";
	if(strcmp($current_choices,$new_choices))
	{
		if(!$current_choices)
			$sql="INSERT INTO ".PREFIX."flattr SET showFlattr=\"".sql_safe($new_choices)."\", user_id=".sql_safe($user_id).";";
		else
			$sql="UPDATE ".PREFIX."flattr SET showFlattr=\"".sql_safe($new_choices)."\" WHERE user_id=".sql_safe($user_id).";";
		
		// echo "<pre>".print_r($sql,1)."</pre>";
		
		if(mysql_query($sql))
				add_message(_("New flattr choices set"));
			else
				add_error(sprintf(_("New flattr choices could not be set. Error: %s"),mysql_error()));
	}
}

function flattr_button_conditional($user_id, $type, $link, $title, $description)
{
	//Eventuell Flattr-knapp
	if($user_id!=NULL && flattr_get_flattr_choice($user_id, $type))
		$flattrID=flattr_get_flattrID($user_id);
	else
		$flattrID=NULL;
		
	// echo "<br />DEBUG 1252: $flattrID";
		
	if($flattrID)
	{
		//echo "<br />debug1758: flattr ".$c['user'];
		
		if($link!="")
		{
			flattr_button_show($flattrID, $link , $title, $description, 'compact', 'en_GB');
		}
		else
		{
			echo "<br />";
			echo "Flattr-code broken! Please tell admin!";
		}
	}
}
?>