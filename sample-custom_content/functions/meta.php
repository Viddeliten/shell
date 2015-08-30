<?php
function meta_title_and_description()
{
	//Title
	$title=meta_get_title();
	
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
?>