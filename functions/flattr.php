<?php

function flattr_button_site($flattr_info_url=NULL)
{
	if($flattr_info_url==NULL)
		$flattr_info_url=SITE_URL."/flattr";
	return html_link($flattr_info_url, _("This site uses Flattr"), "flattr_button"); //'<a href="#" class="flattr_button">This site uses Flattr</a>';
}

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

function flattr_button_show($uid, $url, $title, $description, $button, $language, $static=TRUE, $return_code=FALSE)
{
	$script_id=password_generate(32);
	// echo "<script>
	// echo "<script id='fbwxhy2'>
    
    //Remove all tags from descriptions
	$description=string_remove_tags($description);
	string_replace_urls_with_word($description, _("link"));
	$params="?uid=".$uid."&title=".$title."&description=".sql_safe($description);
	if($static)
	{
		$params.="&url=".$url;
		$code='<a href="https://flattr.com/submit/auto'.$params.'"><img src="'.SITE_URL.'/img/flattr-this.png" alt="'._("Flattr this").'"></a>';
	}
	else
	{
		$params.="&button=".$button;
		$params.="&url='+encodeURIComponent('".$url."');";
		$code="<script id='$script_id'>
					(function(i){
						var f,
							s=document.getElementById(i);
						f=document.createElement('iframe');
						f.src='//api.flattr.com/button/view/".$params."
						f.title='Flattr';
						f.height=20;
						f.width=110;
						f.style.borderWidth=0;
						s.parentNode.insertBefore(f,s);
					})
					('$script_id');
				</script>";
	}
	$code=html_tag("span",$code,"flattr-button", FALSE, NULL, FALSE);
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

//Eventuell Flattr-knapp
function flattr_button_conditional($user_id, $type, $url, $title, $description, $static_button=TRUE, $return_html=FALSE)
{
	ob_start();
	if($user_id!=NULL && flattr_get_flattr_choice($user_id, $type))
		$flattrID=flattr_get_flattrID($user_id);
	else
		$flattrID=NULL;
		
	// echo "<br />DEBUG 1252: $flattrID";
		
	if($flattrID)
	{
		//echo "<br />debug1758: flattr ".$c['user'];
		
		if($url!="")
		{
			flattr_button_show($flattrID, $url , $title, $description, 'compact', 'en_GB', $static_button);
		}
		else
		{
			echo "<br />";
			echo "Flattr-code broken! Please tell admin!";
		}
	}
	
	$content = ob_get_contents();
	ob_end_clean();
	if($return_html)
		return $content;
	else
		echo $content;
}

function flattr_display_information_page()
{
	$content=array();
	
	$content[]=html_tag("h1", sprintf(_("Flattr on %s"), SITE_NAME));
	$content[]=html_tag("p", sprintf(_("%s has Flattr support, which means you can pay creators on the site with flat-rate micropayments!
	With Flattr, you pay a <strong>set amount</strong> each month, and that amount get divided to all the people you support.
	So you can support an unlimited amount of creators without increasing your cost. To be honest, it's pretty awesome!"), SITE_NAME));

	// $content[]=html_link("https://flattr.com/about", _("Read more about Flattr"));
	$content[]=html_action_button("https://flattr.com/about", _("Read more about Flattr"), NULL, "primary", FALSE);

	echo html_rows(1,3,$content);
    
    if(function_exists("flattr_custom_info_page"))
        echo html_row(1,1,flattr_custom_info_page());
}
?>