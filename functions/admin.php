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
		else if(!strcmp($_GET['s'],"mess"))
		{
			admin_display_messages();
		}
		else if(!strcmp($_GET['s'],"spam"))
		{
			spam_admin_list();
		}
		else if(!strcmp($_GET['s'],"individual_spam_score"))
		{
			spam_show_individual_calculation();
		}
		else
			echo "<div class=\"message_box error well\">"._("Unknown admin page")."</div>";
	}
	else
		echo "<p>"._("No page selected")."</p>";
}

/*	Displays a dropdown in main menu if an admin is logged in	*/
function admin_menu_dropdown($return_html=FALSE)
{
    ob_start();
    
	//For custom admin pages
	$custom_pages=unserialize(CUSTOM_PAGES_ARRAY);
	
	//Get custom admin pages
	foreach($custom_pages as $name => $content)
	{
		if(!strcmp($content['slug'],"admin"))
		{
			$custom_admin_pages=$content;
			$custom_admin_name=$name;
		}
	}
	
	$logged_in=login_check_logged_in_mini();
	if($logged_in>1)
	{
		//Admin dropdown menu
        $subpages=array();
        $subpages[_("Users")] = array('req_user_level'   => 2, 'slug'    => 'users');
        $subpages[_("Version")] = array('req_user_level'   => 2, 'slug'    => 'version');
        $subpages[_("Site news")] = array('req_user_level'   => 2, 'slug'    => 'news');
        $subpages[_("Messages")] = array('req_user_level'   => 2, 'slug'    => 'mess');
        $subpages[_("Spam")] = array('req_user_level'   => 2, 'slug'    => 'spam');
        
        if(isset($custom_admin_pages['subpages']) && !empty($custom_admin_pages['subpages']))
		{
            $subpages=array_merge($subpages, $custom_admin_pages['subpages']);
		}

        display_dropdown_menu((isset($custom_admin_name) ? $custom_admin_name : _("Admin tools")), "admin", $subpages);
	}
    
    $contents = ob_get_contents();
	ob_end_clean();
	
	if($return_html)
		return $contents;
	else
		echo $contents;

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
	echo news_form();
}

function admin_display_messages()
{
	// usermessage_receive();
	
	echo "<h2>"._("Message administration")."</h2>";
	
	usermessage_admin_show_selecter_form();
	//Hämta alla typer av meddelanden som finns
	
	
	if(isset($_POST['edit_message']) && isset($_POST['event']) && $_POST['event']!="")
	{
		usermessage_admin_show_editer_form($_POST['event']);
	}
	else if(isset($_POST['add_message']))
	{
		echo '<h2>'._("New message").'</h2>';
		usermessage_admin_show_form($_POST);
	}
}

?>