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
		foreach($custom_pages as $name => $arr)
		{
			if(!strcmp($arr['slug'], $_GET['p']))
				$this_page=$arr;
		}
	}
	if(isset($_GET['s']))
	{
		$this_side=array();
		foreach($this_page as $name => $arr)
		{
			if(!strcmp($arr['slug'], $_GET['s']))
				$this_side=$arr;
		}
	}
	else
		$this_side=$this_page;
	
	if(isset($this_side['meta_description']))
		return $this_side['meta_description'];
	
	return "";
}
?>