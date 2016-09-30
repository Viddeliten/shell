<?php
function meta_title_and_description()
{
	//Title
	$title=meta_get_title();
	
	$description=meta_get_description();
	
	echo '
	<meta name="description" content="'.$description.'">
	<meta name="title" content="'.$title.'">
	<title>'.$title.'</title>';
}

function meta_get_title()
{
	$title=SITE_NAME;
	if(isset($_GET['s']))
	{
		$title.=" | ".string_unslugify($_GET['s']);
	}
	else if(isset($_GET['p']))
	{
		$title.=" | ".string_unslugify($_GET['p']);
	}
	
	if(isset($_GET) && !empty($_GET))
	{
		foreach($_GET as $key => $val)
		{
			if($key!="p" && $key!="s")
			{
				$title.=" | ".string_unslugify($key);
				$title.=" ".string_unslugify($val);
			}
		}
	}
	return $title;
}

function meta_get_description()
{
	// meta_description
	if(defined('CUSTOM_PAGES_ARRAY'))
		$custom_pages=unserialize(CUSTOM_PAGES_ARRAY);
	if(isset($_GET['p']))
	{
		$this_page=array();
		foreach($custom_pages as $arr)
		{
			if(!strcmp($arr['slug'], $_GET['p']))
				$this_page=$arr;
		}
	}
	if(isset($_GET['s']))
	{
		$this_side=array();
		
		if(isset($this_page['subpages']))
		{
			foreach($this_page['subpages'] as $name => $arr)
			{
				if(is_array($arr))
				{
					if(!strcmp($arr['slug'], $_GET['s']))
						$this_side=$arr;
				}
			}
		}
	}
	else if(isset($this_page))
		$this_side=$this_page;
	
	if(isset($this_side['meta_description']))
		return $this_side['meta_description'];
	else if(isset($_GET['p']))
	{
		if(!strcmp($_GET['p'],"feedback"))
		{
			return sprintf(_("Page on %s where you can leave feedback"),SITE_NAME);
		}
		else if(!strcmp($_GET['p'],"news"))
		{
			return sprintf(_("News on site %s"),SITE_NAME);
		}
		else if(!strcmp($_GET['p'],"user") && isset($_GET['s']) && !strcmp($_GET['s'],"profile"))
		{
			if(isset($_GET['user']))
				$user=$_GET['user'];
			else if(isset($_SESSION[PREFIX.'user_id']))
				$user=$_SESSION[PREFIX.'user_id'];
			if(isset($_user))
				return sprintf(_("User %s on site %s. %s"),user_get_name($user), SITE_NAME, user_get_description($user));
		}
		else if(!strcmp($_GET['p'],"user") && isset($_GET['s']) && !strcmp($_GET['s'],"privmess"))
		{
			return sprintf(_("Private messages on site %s"),SITE_NAME);
		}
		// else if(!strcmp($_GET['p'],"usersettings"))
		else if(!strcmp($_GET['p'],"user") && isset($_GET['s']) && !strcmp($_GET['s'],"settings"))
		{
			return sprintf(_("User settings on site %s"),SITE_NAME);
		}
		else if(!strcmp($_GET['p'],"admin"))
		{
			return sprintf(_("Admin tools on site %s"),SITE_NAME);
		}
		else if(!strcmp($_GET['p'],"changelog"))
		{
			return sprintf(_("Change log on site %s"),SITE_NAME);
		}
	}
	
	return SELLING_TEXT;
}
?>