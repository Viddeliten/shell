<?php

function display_custom_pages_menu()
{
	$custom_pages=unserialize(CUSTOM_PAGES_ARRAY);
	$logged_in_level=login_check_logged_in_mini();
	
	// echo "<pre>".print_r($custom_pages,1)."</pre>";
	
	foreach($custom_pages as $name => $content)
	{
		if(!isset($content['req_user_level']) || $content['req_user_level']<1 || $logged_in_level>=$content['req_user_level'])
		{
			if(!isset($content['subpages']) || empty($content['subpages']))
			{
				echo '<li ><a href="'.SITE_URL.'/?p='.$content['slug'].'" >'.$name.'</a></li>';
			}
			else
			{
				echo '<li class="dropdown">
					  <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'.$name.'<span class="caret"></span></a>
					  <ul class="dropdown-menu" role="menu">';
					  foreach($content['subpages'] as $s_name => $s_content)
					  {
							if(!isset($s_content['req_user_level']) || $s_content['req_user_level']<1 || $logged_in_level>=$s_content['req_user_level'])
								echo '<li ><a href="'.SITE_URL.'/?p='.$content['slug'].'&amp;s='.$s_content['slug'].'" >'.$s_name.'</a></li>';
					  }
				echo '</ul>
					</li>';
			}
		}
	}
}

?>