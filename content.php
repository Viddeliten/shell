<?php

message_display_messages_and_errors();
if(login_check_logged_in_mini()>0)
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
	if(custom_page_display())
	{
		//Do nothing else. =)
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
		if(isset($_REQUEST['s']) && !strcmp(strtolower($_REQUEST['s']),"all"))
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
	else if(!strcmp($_GET['p'],"users"))
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
	else 
		echo "<p class=\"well message_box\">"._("Unknown page")."</p>";
}
else
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