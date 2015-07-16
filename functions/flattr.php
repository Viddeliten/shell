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
		return NULL;
	}
	return 0;
}

function flattr_button_show($uid, $url, $title, $description, $button, $language)
{
	$script_id=password_generate(32);
	// echo "<script>
	// echo "<script id='fbwxhy2'>
	echo "<script id='$script_id'>
	(function(i){var f,s=document.getElementById(i);f=document.createElement('iframe');f.src='//api.flattr.com/button/view/?uid=".$uid."&title=".$title."&button=".$button."&description=".$description."&url='+encodeURIComponent('".$url."');f.title='Flattr';f.height=20;f.width=110;f.style.borderWidth=0;s.parentNode.insertBefore(f,s);})('$script_id');</script>";
}

function flattr_set_flattrID($user_id, $flattr_id)
{
	//If the new ID is different from current
	$current_id=flattr_get_flattrID($user_id);
	if(strcmp($flattr_id,$current_id))
	{
		//set it
		if($current_id===NULL)
			$sql="INSERT INTO ".PREFIX."flattr SET flattrID='".sql_safe($flattr_id)."', user_id='".sql_safe($user_id)."';";
		else
			$sql="UPDATE ".PREFIX."flattr SET flattrID='".sql_safe($flattr_id)."' WHERE user_id='".sql_safe($user_id)."';";
		
		// echo "<pre>".print_r($sql,1)."</pre>";

		if(mysql_query($sql))
			add_message("New Flattr ID set");
		else
			add_error("New Flattr ID could not be set: ".mysql_error());
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
?>