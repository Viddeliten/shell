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
else if(isset($_GET['p']))
{
	if(custom_page_display())
	{
		//Do nothing else. =)
	}
	else if(!strcmp($_GET['p'],"feedback"))
	{
		feedback_show();
	}
	else if(!strcmp($_GET['p'],"news"))
	{
		news_show();
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
	else if(!strcmp($_GET['p'],"admin"))
	{
		if(login_check()>1)
			admin_display_contents();
		else
			echo login_check();
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