<?php

display_messages_and_errors();

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
	if(!strcmp($_GET['p'],"feedback"))
	{
		feedback_show();
	}
	else if(!strcmp($_GET['p'],"usersettings"))
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
	else if(!custom_page_display())
		echo "<p class=\"well message_box\">"._("Unknown page")."</p>";
}
else
{
	if(file_exists(CUSTOM_CONTENT_PATH."/index.php"))
	{
		include(CUSTOM_CONTENT_PATH."/index.php");
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