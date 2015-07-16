<?php

function user_receive()
{
	if(isset($_POST['user_update_settings']))
	{
		if(isset($_GET['user']))
		{
			if(user_get_admin($_SESSION[PREFIX."user_id"]) && user_exists($_GET['user']))
				$user_id=$_GET['user'];
		}
		else
			$user_id=$_SESSION[PREFIX."user_id"];
		
		if(!$user_id)
		{
			add_error("No valid user");
		}
		else
		{
			//Username
			if(isset($_POST['username']) && $_POST['username']!="")
			{
				user_set_name($user_id, $_POST['username']);
			}
			
			//Email
			if(isset($_POST['email']) && $_POST['email']!="")
			{
				user_set_email($user_id, $_POST['email']);
			}

			//password
			if(isset($_POST['password']) && $_POST['password']!="")
			{
				user_set_password($user_id, $_POST['password']);
			}
			
			//Flattr id
			if(isset($_POST['flattr_id']) && $_POST['flattr_id']!="")
			{
				flattr_set_flattrID($user_id, $_POST['flattr_id']);
			}
			//Flattr choice. Allways do this!
			flattr_set_flattr_choice($user_id, $_POST['flattr_choice']);
		}
	}
}

function user_get_name($id)
{
	$sql="SELECT username FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
//	echo "<br />$sql";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_array($hh))
			return $h['username'];
	return NULL;
}
function user_get_email($id)
{
	$sql="SELECT email FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
//	echo "<br />$sql";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_array($hh))
			return $h['email'];
	return NULL;
}
function user_get_password_hash($id)
{
	$sql="SELECT password FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
//	echo "<br />$sql";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_array($hh))
			return $h['password'];
	return NULL;
}

function user_exists($id)
{
	$sql="SELECT count(id) as nr FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
//	echo "<br />$sql";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_array($hh))
			return $h['nr'];
	return FALSE;
}

function user_get_admin($id)
{
	if($id==0)
		return 5;
		
	$sql="SELECT level FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_array($hh))
			return $h['level'];
	return NULL;
}

function user_get_link($user_id)
{
	return "<a href=\"".SITE_URL."?p=user&amp;user=".$user_id."\">".user_get_name($user_id)."</a>";
}

function user_display_settings()
{
	login_check_logged_in_mini();
	
	if(isset($_GET['user']))
	{
		if(user_get_admin($_SESSION[PREFIX."user_id"]) && user_exists($_GET['user']))
			$user_id=$_GET['user'];
	}
	else
		$user_id=$_SESSION[PREFIX."user_id"];
	
	if(!$user_id)
	{
		echo "<div class=\"message_box error well\">No valid user</div>";
	}
	else
	{
		echo "<h1>".sprintf(_("Settings for %s"), user_get_name($user_id))."</h1>";
		
		echo '<form method="post">';
		//Username
		echo '<div class="form-group">
			<label for="username_input">'._("Username").'</label>
			<input type="text" name="username" id="username_input" placeholder="'._("Username").'" class="form-control" value="'.user_get_name($user_id).'">
		</div>';
		//email
		echo '<div class="form-group">
			<label for="email_input">'._("Email").'</label>
			<input type="text" name="email" id="email_input" placeholder="'._("Email").'" class="form-control" value="'.user_get_email($user_id).'">
		</div>';
		//password
		echo '<div class="form-group">
			<label for="password_input">'._("Password").'</label>
			<input type="password" name="password" id="password_input" placeholder="'._("Password").'" class="form-control">
		</div>';
		//Flattr id
		echo '<div class="form-group">
			<label for="flattr_id_input">'._("Flattr id").'</label>
			<input type="text" name="flattr_id" id="flattr_id_input" placeholder="'._("Flattr id").'" class="form-control" value="'.flattr_get_flattrID($user_id).'">
		</div>';
		//Flattr choice
		// echo "<pre>".print_r(flattr_get_flattr_choice($user_id, "comment"),1)."</pre>";
		echo '<div class="checkbox">';
			echo '<label>
				<input type="checkbox" name="flattr_choice[]" value="comment"';
				if(flattr_get_flattr_choice($user_id, "comment"))
					echo ' checked';
				echo '>
				'.sprintf(_("Display Flattr-button on <strong>%s</strong>"),_("comments")).'
			  </label>';
		echo '</div>';
		echo '<div class="checkbox">';
			echo '<label>
				<input type="checkbox" name="flattr_choice[]" value="feedback"';
				if(flattr_get_flattr_choice($user_id, "feedback"))
					echo ' checked';
				echo '>
				'.sprintf(_("Display Flattr-button on <strong>%s</strong>"),_("feedbacks")).'
			  </label>';
		echo '</div>';
		
		//Save button
		echo '<input type="submit" class="btn btn-success" value="'._("Save").'" name="user_update_settings">';
		
		echo '</form>';
	}
}

function user_register()
{
	if($_POST['name']!="" && $_POST['email']!="")
	{
		//Försök registrera denna användare.
		
		//Kolla så att användarnamnet inte innehåller konstiga tecken eller är SITE_NAME
		//Kolla så att strängen är alfanumerisk
		if (eregi_replace('[a-z0-9]', '', $_POST['name']) == '')
		{
			if(user_email_exists($_POST['email']))
			{
				add_error("Email address is already registered");
			}
			else if(user_name_exists($_POST['name']) || !strcasecmp($_POST['name'],SITE_NAME))
			{
				add_error("User name is already registered");
			}
			else
			{
				//generera ett lösenord
				$password=password_generate(32);
				//Skriv in info i databasen
				$sql="INSERT INTO ".PREFIX."user
				(username, email, password)
				VALUES ('".$_POST['name']."','".$_POST['email']."','".md5($password)."');";
				$went_fine=mysql_query($sql);

				if($went_fine)
				{
					add_message("Registration went fine. You will soon recieve an email with further instructions!");
					
					$password=login_create_reset_code($_POST['email']);
					//Skicka ett email
					$to = $_POST['email'];
					$subject = "[".SITE_NAME."] - Welcome!";
					$body = "Hi,\n\nYour new account at ".SITE_NAME." has been created. Please visit the following link to set your password.

".SITE_URL."/?lostpassword&password_reset=$password

Regards,\nThe ".SITE_NAME." Team";
					$headers = 'From: '.CONTACT_EMAIL . "\r\n" .
    'Reply-To: '.CONTACT_EMAIL . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
					
					//Skicka mail! Det funkar inte i WAMP, men jag tror det beror på inställningar... kanske.
					if (mail($to, $subject, $body, $headers))
					{
						add_message("Message successfully sent!");
					}
					else
					{
						add_error("Message delivery failed.");
					}
					define('REGISTRATION_DONE');
				}
				else
				{
					add_error("There was a problem. Try again.
					<pre>".mysql_error()."</pre>");
				}
			}
		}
		else
		{
			add_error("Only alphanumeric usernames are allowed!");
		}
	}
}


function user_set_name($user_id, $new_username)
{
	//If the new username is different from current
	if(strcmp($new_username,user_get_name($user_id)))
	{
		//check that no other user has it
		if(!user_name_exists($new_username))
		{
			//set it
			$sql="UPDATE ".PREFIX."user SET username='".sql_safe($new_username)."' WHERE id=".sql_safe($user_id).";";
			if(mysql_query($sql))
				add_message("New user name set");
			else
				add_error("User name could not be set: ".mysql_error());
		}
		else
			add_error("User name '$new_username' is already in use.");
	}
}

function user_set_email($user_id, $new_email)
{
	//If the new username is different from current
	if(strcmp($new_email,user_get_email($user_id)))
	{
		//check that no other user has it
		if(!user_email_exists($new_email))
		{
			//set it
			$sql="UPDATE ".PREFIX."user SET email='".sql_safe($new_email)."' WHERE id=".sql_safe($user_id).";";
			if(mysql_query($sql))
				add_message("New email set");
			else
				add_error("Email could not be set: ".mysql_error());
		}
		else
			add_error("Email '$new_email' is already in use.");
	}
}
function user_set_password($user_id, $new_password)
{
	$crypt_pass=crypt($new_password, $user_id.user_get_email($user_id));
	//If the new password is different from current
	if(strcmp($crypt_pass,user_get_password_hash($user_id)))
	{
		//set it
		$sql="UPDATE ".PREFIX."user SET password='".sql_safe($crypt_pass)."' WHERE id=".sql_safe($user_id).";";
		if(mysql_query($sql))
			add_message("New password set");
		else
			add_error("New password could not be set: ".mysql_error());
	}
}

function user_name_exists($username)
{
	if($users=mysql_query("SELECT * FROM ".PREFIX."user WHERE username='".sql_safe($username)."';"))
	{
		while($u=mysql_fetch_array($users))
		{
			if(!strcasecmp($u['username'],$username))
				return true;
		}
	}
	return false;
}

function user_email_exists($email)
{
	$sql="SELECT * FROM ".PREFIX."user WHERE email='".sql_safe($email)."';";
	if($users=mysql_query($sql))
	{
		while($u=mysql_fetch_array($users))
		{
			if(!strcasecmp($u['email'],$email))
				return true;
		}
	}
	return false;
}

?>