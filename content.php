<?php

message_display_messages_and_errors();
$logged_in_level=login_check_logged_in_mini();

$custom_pages=unserialize(CUSTOM_PAGES_ARRAY);
$show_feedback=true;
if(isset($custom_pages["Feedback"]))
{
	if($custom_pages["Feedback"]['req_user_level']>0 && $custom_pages["Feedback"]['req_user_level']>$logged_in_level)
		$show_feedback=false;
}
$show_users=true;
if(isset($custom_pages["Users"]))
{
	if($custom_pages["Users"]['req_user_level']>0 && $custom_pages["Users"]['req_user_level']>$logged_in_level)
		$show_users=false;
}

if($logged_in_level>0)
	notice_display_notices($_SESSION[PREFIX.'user_id']);

if(isset($_GET['reg']))
{
	//Register form
	login_form_registration();
}
else if(isset($_GET['lostpassword']))
{
	login_password_recovery_display();
}
else if(isset($_REQUEST['p']))
{
	if(!strcmp($_REQUEST['p'],"oauth") && isset($_GET['s']))
	{
		$login_oauth=unserialize(LOGIN_OAUTH);
		if(isset($login_oauth[$_GET['s']]))
		{			
			if(!isset($_REQUEST['code']))
				header('Location: '.$login_oauth[$_GET['s']]["302_uri"], true, 302);
		}
	}


	if(custom_page_display() || isset($_GET['logout']))
	{
		//Do nothing else. =)
	}
	else if(!strcmp($_GET['p'],"flattr"))
	{
		//Display information page about Flattr
		flattr_display_information_page();
	}
	else if(!strcmp($_GET['p'],"add_comment"))
	{
		$type=$_GET['s'];
		
		switch($type)
		{
			case "user":
				user_display_profile($_GET['id']);
				break;
			case "comment":
				echo html_tag("div",comment_display_single($_GET['id'], NULL, FALSE),"comment");
				break;
			case "news":
				news_show(10, sprintf(_("News for %s"),SITE_NAME),1);
				break;
			case "feedback":
				if(!$show_feedback)
				{
					message_print_error(_("Nothing to see here..."));
					return 0;
				}
				$ff=feedback_get_list_specific($_GET['id']);
				feedback_list_print($ff);
				break;
			default:
				//try to find a custom function maybe?
				$_GET['p']=$type;
				
				if(!custom_page_display())
					echo html_tag("p",sprintf(_("Content: %s #%s"),sql_safe($type), sql_safe($_GET['id'])));
		}
		comment_form_show($_GET['id'], $type, _("Comment on this"));
	}
	else if(!strcmp($_GET['p'],"feedback"))
	{
		if(!$show_feedback)
			message_print_error(_("Nothing to see here..."));
		else if(isset($_REQUEST['s']) && !strcmp(strtolower($_REQUEST['s']),"all"))
			feedback_show_all();
		else
			feedback_show();
	}
	else if(!strcmp($_GET['p'],"news"))
	{
		news_show(10, sprintf(_("News for %s"),SITE_NAME),1);
	}
	else if(!strcmp($_GET['p'],"user") && isset($_GET['s']) && !strcmp($_GET['s'],"profile"))
	{
		if(!$show_users)
		{
			message_print_error(_("Nothing to see here..."));
			return 0;
		}
		
		if(isset($_GET['user']))
			$user=$_GET['user'];
		else if(isset($_SESSION[PREFIX.'user_id']))
			$user=$_SESSION[PREFIX.'user_id'];
		else
			echo "<p class=\"well message_box\">"._("Missing user id")."</p>";
		
		if(isset($user))
			user_display_profile($user);
	}
	else if(!strcmp($_GET['p'],"user") && isset($_GET['s']) && !strcmp($_GET['s'],"privmess"))
	{
		privmess_display();
	}
	// else if(!strcmp($_GET['p'],"usersettings"))
	else if(!strcmp($_GET['p'],"user") && isset($_GET['s']) && !strcmp($_GET['s'],"settings"))
	{
		user_display_settings();
	}
	else if(!strcmp($_GET['p'],"users") && $show_users)
	{
		if(isset($_GET['s']))
		{
			if(!strcmp($_GET['s'],"active"))
			{
				echo "<h1>"._("Active users")."</h1>";
				user_display_active_users(FALSE);
				return TRUE;
			}
		}
	}
	else if(!strcmp($_GET['p'],"admin"))
	{
		if(login_check_logged_in_mini()>1)
			admin_display_contents();
		else
			echo login_check_logged_in_mini();
	}
	else if(!strcmp($_GET['p'],"changelog"))
	{
		version_show_latest();
	}
	else if(!isset($_REQUEST['p']) || isset($_REQUEST['inlog']) || !strcmp($_REQUEST['p'],"oauth"))
	{
		content_start_page();
	}
	else 
		echo "<p class=\"well message_box\">"._("Unknown page")."</p>";
}
else if(isset($_GET['search']))
{
	search_display_results($_GET['search']);
}
else
{
	content_start_page();
}

function content_start_page()
{
	if(file_exists(CUSTOM_CONTENT_PATH."/start.php"))
	{
		include(CUSTOM_CONTENT_PATH."/start.php");
	}
	else
	{
		//Start page
		echo '<div class="start-container">
			<h1>'.SELLING_HEADLINE.'</h1>
			<p class="lead">'.SELLING_TEXT.'</p>
		  </div>';
	}
}

?>