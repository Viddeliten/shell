<?php

function admin_display_contents()
{
	if(isset($_GET['s']))
	{
		if(!strcmp($_GET['s'],"users"))
		{
			admin_display_users();
		}
		else if(!strcmp($_GET['s'],"version"))
		{
			admin_display_version();
		}
		else if(!strcmp($_GET['s'],"news"))
		{
			admin_display_news();
		}
		else
			echo "<p>"._("Unknown admin page")."</p>";
	}
	else
		echo "<p>"._("No page selected")."</p>";
}

/*	Displays a dropdown in main menu if an admin is logged in	*/
function admin_menu_dropdown()
{
	//For custom admin pages
	$custom_pages=unserialize(CUSTOM_PAGES_ARRAY);
	
	$logged_in=login_check_logged_in_mini();
	if($logged_in>1)
	{
		//Admin dropdown menu
		echo '<li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'._("Admin tools").'<span class="caret"></span></a>
          <ul class="dropdown-menu" role="menu">
            <li><a href="'.SITE_URL.'/?p=admin&amp;s=users">'._("Users").'</a></li>
            <li><a href="'.SITE_URL.'/?p=admin&amp;s=version">'._("Version").'</a></li>
            <li><a href="'.SITE_URL.'/?p=admin&amp;s=news">'._("Publish site news").'</a></li>';

		//Custom admin pages
		foreach($custom_pages as $name => $content)
		{
			if(!strcmp($content['slug'],"admin"))
			{
				if(isset($content['subpages']) && !empty($content['subpages']))
				{
					foreach($content['subpages'] as $s_name => $s_content)
					{
						echo '<li ><a href="'.SITE_URL.'/?p='.$content['slug'].'&amp;s='.$s_content['slug'].'" >'.$s_name.'</a></li>';
					}
				}
			}
		}
		
			
		echo '
          </ul>
        </li>';
	}
}

function admin_display_users()
{
	echo "<h1>Users</h1>";
	//Show active users
	$sql="SELECT * FROM ".PREFIX."user ORDER BY lastlogin DESC;";
	if($uu=mysql_query($sql))
	{
		echo '<table class="table">';
		echo "<tr>
			<th>username</th>
			<th>regdate</th>
			<th>lastlogin</th>
			<th>email</th>
			<th>level</th>
			<th>inactive</th>
		</tr>";
		
		while($u=mysql_fetch_array($uu)){
			echo "<tr>
			<td>".$u['username']."</td>
			<td>".$u['regdate']."</td>
			<td>".$u['lastlogin']."</td>
			<td>".$u['email']."</td>
			<td>".$u['level']."</td>
			<td>".$u['inactive']."</td>
		</tr>";
		}
		echo "</table>";
	}
	
	//Add user
}
function admin_display_version()
{
	version_display_settings();
}

function admin_display_news()
{
	//Form for news input
	news_form();
}

?>