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
			flattr_set_flattr_choice($user_id, (isset($_POST['flattr_choice']) ? $_POST['flattr_choice'] : array()));
			
			user_set_custom_choices(login_get_user(), $_POST);
		}
	}
	else if(isset($_POST['profile_save']))
	{
        if(function_exists("user_profile_edit_receive"))
            $user_profile_edit_receive = user_profile_edit_receive();
        else
            $user_profile_edit_receive = TRUE;
        
		$sql="UPDATE ".PREFIX."user SET description='".sql_safe($_POST['description'])."' WHERE id=".sql_safe($_SESSION[PREFIX.'user_id']).";";
		if(mysql_query($sql))
        {
            if($user_profile_edit_receive)
                add_message(_("Profile updated"));
            else
                add_error(_("There was an error updating your things"));
        }
		else
			add_error(sprintf(_("Profile update fail<br />SQL: %s<br />ERROR: %s"),$sql,mysql_error()));
	}
	else if(isset($_POST['add_user_friend']))
	{
		user_friend_request(login_get_user(), $_POST['user_id']);
	}
	else if(isset($_POST['reject_user_friend']))
	{
		user_friend_reject(login_get_user(), $_POST['user_id']);
	}
}

function user_display_dropdown()
{
	$privmessnr=privmess_get_unread_nr($_SESSION[PREFIX.'user_id']);
	$badge_total=$privmessnr;
				
	$subpages=array();
    
	$name=_("Messages"); 
	if($privmessnr>0)
	{
		$name.= ' <span class="badge badge-info">'.$privmessnr.'</span>';
	}
	$subpages[$name]=array("slug" => "privmess");
    
	$subpages[_("Profile")]=array("slug" => "profile");
	$subpages[_("Settings")]=array("slug" => "settings");
	$subpages["dropdown-divider"]=array();
	$subpages[_("Log out")]=array("slug" => "?logout");
    
	$name=$_SESSION[PREFIX."username"]; 
	if($badge_total>0)
	{
		$name.= ' <span class="badge">'.$badge_total.'</span>';
	}
	echo (defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0") ? '<ul class="navbar-nav user-menu-dropdown">' : "");
        display_dropdown_menu($name, "user", $subpages);
    echo (defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0") ? '</ul>' : "");
}

function user_get_all($type, $limit=NULL, $order_by="RAND()")
{
	$sql="SELECT id FROM ".PREFIX."user";
	if(!strcmp($type,"active"))
		$sql.=" WHERE lastlogin IS NOT NULL AND inactive IS NULL";
	$sql.=" ORDER BY ".$order_by;
	if($limit!==NULL)
		$sql.=" LIMIT 0,".sql_safe($limit);
	$r=array();
	if($uu=mysql_query($sql))
	{
		while($u=mysql_fetch_assoc($uu))
		{
			$r[]=$u['id'];
		}
	}
	return $r;
}

function user_get_name($id)
{
	$sql="SELECT username FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
//	echo "<br />$sql";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['username'];
	return NULL;
}
function user_get_level($id)
{
	$sql="SELECT level FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['level'];
	return NULL;
}
function user_get_regdate($id)
{
	$sql="SELECT regdate FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['regdate'];
	return NULL;
}
function user_get_reputation($id)
{
	$sql="SELECT reputation FROM ".PREFIX."user WHERE id='".sql_safe($id)."';";
	//echo "<br />$sql";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['reputation'];
	return NULL;
}
function user_get_lastlogin($id)
{
	$sql="SELECT lastlogin FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['lastlogin'];
	return NULL;
}
function user_get_description($id)
{
	$sql="SELECT description FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['description'];
	return NULL;
}
function user_get_avatar_path($user_id, $width=60)
{
	//Check if user has an image
	if(file_exists("img/avatar/".$user_id.".png"))
		return "img/avatar/".$user_id.".png";
	else
		return "https://www.gravatar.com/avatar/".md5( strtolower( trim( user_get_email($user_id) ) ) )."?s=".$width;
		// return "img/no_avatar.png";
}
function user_get_email($id)
{
	$sql="SELECT email FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
//	echo "<br />$sql";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['email'];
	return NULL;
}
function user_get_password_hash($id)
{
	$sql="SELECT password FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
//	echo "<br />$sql";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['password'];
	return NULL;
}

function user_exists($id)
{
	$sql="SELECT count(id) as nr FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
//	echo "<br />$sql";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['nr'];
	return FALSE;
}

function user_get_admin($id)
{
	if($id===NULL)
		return -1;
	
	if($id==0)
		return 5;
		
	$sql="SELECT level FROM ".PREFIX."user WHERE id=".sql_safe($id).";";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['level'];
	return NULL;
}

function user_get_link_url($user_id)
{
	return SITE_URL."?p=user&amp;s=profile&amp;user=".$user_id;
}
function user_get_link($user_id)
{
	if($user_id==0)
		return SITE_NAME;
	else
		return "<a href=\"".user_get_link_url($user_id)."\">".user_get_name($user_id)."</a>";
}

function user_get_link_address($user_id)
{
	return user_get_link_url($user_id);
}

function user_get_id_from_username($username)
{
	$sql="SELECT id FROM ".PREFIX."user WHERE username='".sql_safe($username)."';";
	if($hh=mysql_query($sql))
		if($h=@mysql_fetch_assoc($hh))
			return $h['id'];
	return NULL;
}

function user_display_profile($user_id)
{
	echo '<h1>'.user_get_name($user_id).'</h1>';

	$user_description=user_get_description($user_id);
	$user_image=user_get_avatar_path($user_id, 180);
	
	if(login_check_logged_in_mini()>0 && isset($_POST['profile_edit']) && $user_id===$_SESSION[PREFIX.'user_id'])
	{
		//Show edit form
		
		//TODO: Image
		
		echo '<form method="post">
			<div class="form-group">
				<label for="description_text">'._("Profile text").'</label>
				<textarea class="form-control" id="description_text" name="description">'.$user_description.'</textarea>
			</div>
			<div class="form-group">
				<label for="avatar_change_div">'._("Profile image (avatar)").'</label>
				<div id="avatar_change_div">
					<p>To change your avatar, <a href="http://gravatar.com">go to Gravatar</a>, log in and upload desired picture!</p>
					<p>Current picture being used for '.user_get_email($user_id).' is:</p>
					<img class="avatar" src="'.$user_image.'">
				</div>
			</div>
            '.(function_exists("user_profile_edit_inputs") ? user_profile_edit_inputs() : "").'
			<input type="submit" class="btn btn-success" name="profile_save" value="'._("Save").'">
		</form>';
	}
	else
	{
		echo '
		<div class="row profile">
			<div class="col-md-2">
				<img class="avatar" src="'.$user_image.'">
			</div>
			<div class="col-md-10">
				<p>'.$user_description.'</p>
			</div>
		</div>';
		if(login_check_logged_in_mini()>0 && $user_id==$_SESSION[PREFIX.'user_id'])
		{
			//edit button
			echo '
			<div class="row">
				<div class="center">
					<form method="post">
						<input type="submit" class="btn btn-default" name="profile_edit" value="'._("Edit profile").'">
					</form>
				</div>
			</div>
			';
		}
		else if(login_check_logged_in_mini()>0)
			echo user_friend_get_request_button($user_id);
		
		if (function_exists ( 'user_profile_custom_content' ))
		{
			user_profile_custom_content($user_id);
		}
		
		echo '<div class="col-lg-12">';
		comments_show_comments_and_replies($user_id, "user");
		echo "</div>";
	}
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
		if(defined('CUSTOM_SETTINGS'))
		{
			$custom_settings=unserialize(CUSTOM_SETTINGS);
			if(isset($custom_settings['flattr']))
			{
				foreach($custom_settings['flattr'] as $custom_flattr_choice => $translation)
					user_setting_flattr_display($user_id, $custom_flattr_choice, $translation);
			}
			
			$site_specific_user_settings=user_get_custom_setting_globals();

			foreach($site_specific_user_settings as $cs => $val)
			{
				echo '<strong>'.string_unslugify($cs).'</strong>';
				foreach($val as $cc => $v)
				{
					echo '<div class="checkbox">';
						echo '<label>
							<input type="checkbox" name="'.$cs.'[]" value="'.$cc.'"';
							if(user_get_setting($user_id, array($cs => $cc)))
								echo ' checked';
							echo '>
							'.$v.'
						  </label>';
					echo '</div>';
				}
			}
		}

		//Save button
		echo '<input type="submit" class="btn btn-success" value="'._("Save").'" name="user_update_settings">';
		
		echo '</form>';
	}
}

function user_get_setting($user_id, $type_arr)
{
	$choices=user_get_settings($user_id);

	foreach($type_arr as $key => $val)
	{
		if(isset($choices[$key]))
		{
			if(in_array($val, $choices[$key]))
				return TRUE;
		}
	}
	return FALSE;
}

function user_get_settings($user_id)
{
	if($uu=mysql_query("SELECT settings FROM ".PREFIX."user_setting WHERE user_id='".sql_safe($user_id)."';"))
	{
		if($u=mysql_fetch_assoc($uu))
		{
			$choices=unserialize($u['settings']);
			return $choices;
		}
	}
	return NULL;	
}

function user_setting_flattr_display($user_id, $value, $translation)
{
	echo '<div class="checkbox">';
		echo '<label>
			<input type="checkbox" name="flattr_choice[]" value="'.$value.'"';
			if(flattr_get_flattr_choice($user_id, $value))
				echo ' checked="checked"';
			echo '>
			'.sprintf(_("Display Flattr-button on <strong>%s</strong>"),$translation).'
		  </label>';
	echo '</div>';
}

function user_register()
{
	if($_POST['name']!="" && $_POST['email']!="")
	{
		//Försök registrera denna användare.
		
		//Kolla så att användarnamnet inte innehåller konstiga tecken eller är SITE_NAME
		//Kolla så att strängen är alfanumerisk
		if (ctype_alnum($_POST['name']))
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
				//Skriv in info i databasen
				$went_fine=user_insert($_POST['name'], $_POST['email']);

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
					define('REGISTRATION_DONE',1);
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

function user_get_custom_setting_globals()
{
	$site_specific_user_settings=array();
	require_once(CUSTOM_CONTENT_PATH."/globals.php");
	
	if(defined('CUSTOM_SETTINGS'))
	{
		$custom_settings=unserialize(CUSTOM_SETTINGS);
		
		foreach($custom_settings as $cs => $val)
		{
			if(strcmp($cs,"flattr"))
			{
				foreach($val as $cc => $v)
				{
					$site_specific_user_settings[string_slugify($cs)][$cc]=$v;
				}
			}
		}
	}
	return $site_specific_user_settings;
}

function user_set_custom_choices($user_id, $post)
{
	//First get what choices outside of flattr there is
	$custom_settings=user_get_custom_setting_globals();
	
	//Save the choices user has made
	$save_settings=array();
	foreach($custom_settings as $cs => $s)
	{
		if(isset($post[$cs]))
		{
			foreach($s as $choice => $val)
			{
				if(in_array($choice, $post[$cs]))
					$save_settings[$cs][]=$choice;
			}
		}
	}

	//write to db
	$current_choices=user_get_settings($user_id);
	$current_choices_serialized=serialize($current_choices);
	$new_choices=serialize($save_settings);

	if(strcmp($current_choices_serialized,$new_choices))
	{
		if($current_choices === NULL)
			$sql="INSERT INTO ".PREFIX."user_setting SET settings=\"".sql_safe($new_choices)."\", user_id=".sql_safe($user_id).";";
		else
			$sql="UPDATE ".PREFIX."user_setting SET settings=\"".sql_safe($new_choices)."\" WHERE user_id=".sql_safe($user_id).";";

		$error_code="1039615";
		$success_message=_("Settings saved");
		message_try_mysql($sql,	$error_code, $success_message,
			FALSE, //Print now
			TRUE //generate_warning_on_fail
			);
	}
}

function user_name_exists($username)
{
	if($users=mysql_query("SELECT * FROM ".PREFIX."user WHERE username='".sql_safe($username)."';"))
	{
		while($u=mysql_fetch_assoc($users))
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
		while($u=mysql_fetch_assoc($users))
		{
			if(!strcasecmp($u['email'],$email))
				return true;
		}
	}
	return false;
}

function user_display_active_users($include_reputation=TRUE) 
{
	//ta emot sortering
	if(isset($_GET['sortby']))
	{
		if(!strcmp($_GET['sortby'],"name"))
		{
			$sort="username";
		}
		else if(!strcmp($_GET['sortby'],"registered"))
		{
			$sort="regdate";
		}
		else if(!strcmp($_GET['sortby'],"lastlogin"))
		{
			$sort="lastlogin";
		}
		else if(!strcmp($_GET['sortby'],"reputation"))
		{
			$sort="reputation";
		}
	}
	if(!isset($sort))
	{
		$sort="reputation";
	}
	
	//Sort order
	if(isset($_GET['sortorder']))
	{
		if(!strcmp($_GET['sortorder'],"asc"))
			$sort_order="asc";
		else
			$sort_order="desc";
	}
	if(!isset($sort_order))
	{
		$sort_order="desc";
	}
	
	if($sort_order=="asc")
		$other_sort_order="desc";
	else
		$other_sort_order="asc";
	
	//Visa användarna
	$sql="SELECT
		id, username, regdate, lastlogin, reputation 
		FROM ".PREFIX."user
		WHERE inactive IS NULL 
		AND lastlogin IS NOT NULL 
		AND blocked IS NULL
		ORDER BY ".$sort." ".$sort_order.";";
	$users=mysql_query($sql);
	echo "<table class=\"table table-striped\">";
	//Rubriker
	echo "<tr>".
			"<th></th>".
			"<th><a href=\"".add_get_to_URL("sortorder", ( strcmp($sort,"username") ? $sort_order : $other_sort_order ), add_get_to_URL("sortby", "name"))."\">"._("Name")."</a></th>"; 
	if($include_reputation)
	echo "<th><a href=\"".add_get_to_URL("sortorder", ( strcmp($sort,"reputation") ? $sort_order : $other_sort_order ), add_get_to_URL("sortby", "reputation"))."\">"._("Reputation points")."</a></th>";
	echo "<th><a href=\"".add_get_to_URL("sortorder", ( strcmp($sort,"regdate") ? $sort_order : $other_sort_order ), add_get_to_URL("sortby", "registered"))."\">"._("Registered")."</a></th>".
		 "<th><a href=\"".add_get_to_URL("sortorder", ( strcmp($sort,"lastlogin") ? $sort_order : $other_sort_order ), add_get_to_URL("sortby", "lastlogin"))."\">"._("Last logged in")."</a></th>".
	"</tr>";
	
	for($i=1;$u=mysql_fetch_assoc($users);$i++)
	{
		echo "<tr>".
				"<td>$i</td>".
			"<td>".user_get_link($u['id'])."</td>";
		if($include_reputation)
			echo "<td>$u[reputation]</td>";
		echo "<td>".date("Y-m-d H:i",strtotime($u['regdate']))."</td>".
			 "<td>".date("Y-m-d H:i",strtotime($u['lastlogin']))."</td>".
			"</tr>";
	}
	echo "</table>";
}

function user_display_friends()
{
	$friends=user_friend_get_accepted(login_get_user(),$_GET['sortby'],$_GET['order']);

	if(empty($friends))
	{
		echo '<p>'._("Found no current friends").'</p>';
	}
	else
	{
		$url['name']=add_get_to_URL("order",
								    (isset($_GET['sortby']) && $_GET['sortby']=="name" && isset($_GET['order']) && $_GET['order']=='ASC' ? 'DESC' : 'ASC'),
									add_get_to_URL("sortby", "name"));
		$url['accepted']=add_get_to_URL("order",
									(isset($_GET['sortby']) && $_GET['sortby']=="accepted" && isset($_GET['order']) && $_GET['order']=='ASC' ? 'DESC' : 'ASC'),
									add_get_to_URL("sortby", "accepted"));
		$url['lastlogin']=add_get_to_URL("order",
									(isset($_GET['sortby']) && $_GET['sortby']=="lastlogin" && isset($_GET['order']) && $_GET['order']=='ASC' ? 'DESC' : 'ASC'),
									add_get_to_URL("sortby", "lastlogin"));
		
		echo '<table class="table table-striped">
		<tr>
			<th><a href="'.$url['name'].'">'._("Name").'</a></th>
			<th><a href="'.$url['accepted'].'">'._("Friends since").'</a></th>
			<th><a href="'.$url['lastlogin'].'">'._("Last logged in").'</a></th>
		</tr>';
		foreach($friends as $friend)
		{
			echo '<tr>
				<td>'.user_get_link($friend['friend_id']).'</td>
				<td>'.$friend['accepted'].'</td>
				<td>'.$friend['lastlogin'].'</td>
			</tr>';
		}
		echo '</table>';
	}
}

function user_friend_reject($active_user, $rejected_user)
{
	$sql="UPDATE ".PREFIX." user_friend SET status='REJECTED' WHERE user=".sql_safe($active_user)." AND requested_by=".sql_safe($rejected_user).";";
	message_try_mysql($sql,6071814, _("Friend request rejected"));
}

function user_friend_request($requested_by, $user_id)
{
	$req=user_friend_get($user_id, $requested_by);
	//If $user_id has asked to add friend, accept it
	if(!empty($req) && $req['requested_by']==$user_id && (!strcmp($req['status'],'DESIRED') || !strcmp($req['status'],'FORBIDDEN')))
	{
		$sql="UPDATE ".PREFIX." user_friend SET status='ACCEPTED' WHERE id=".sql_safe($req['id']).";";
		if(message_try_mysql($sql,6071814, _("Friend request accepted"))!==FALSE)
			return true;
	}
	else if(empty($req))
	{
		$sql="INSERT INTO ".PREFIX."user_friend SET requested_by=".sql_safe($requested_by).", user=".sql_safe($user_id).";";
		if(message_try_mysql($sql,6171728, _("Friend request sent"))!==FALSE)
			return true;
	}
	message_add_error(_("Befriending unsuccessful"));
	return false;
}

function user_friend_get_accepted($user_id, $sortby, $order)
{
	
	$sql="SELECT 
		user_friend.*, 
		IF(user_friend.requested_by=".sql_safe($user_id).",user_friend.user,user_friend.requested_by) as friend_id,
		fh.timestamp as accepted,
		user.username as name,
		user.lastlogin
	FROM user_friend
	INNER JOIN user ON user.id=IF(user_friend.requested_by=".sql_safe($user_id).",user_friend.user,user_friend.requested_by)
	LEFT JOIN (SELECT MAX(id) as id, user_friend_id FROM user_friend_history GROUP BY user_friend_id) fh2 ON fh2.user_friend_id=user_friend.id
	LEFT JOIN user_friend_history fh ON fh.user_friend_id=user_friend.id AND fh.id=fh2.id
	WHERE (user_friend.requested_by=".sql_safe($user_id)." OR user_friend.user=".sql_safe($user_id).")
	AND user_friend.status='ACCEPTED'
	".($sortby!="" ? "ORDER BY ".sql_safe($sortby)." ".sql_safe($order) : "").";";

	$friends=array();
	if($ff=mysql_query($sql))
	{
		while($f=mysql_fetch_assoc($ff))
		{
			$friends[]=$f;
		}
	}
	return $friends;
}

function user_friend_get($user1, $user2)
{
	$sql="SELECT user_friend.*, fh.timestamp as update_time
	FROM ".PREFIX."user_friend user_friend
	LEFT JOIN (	SELECT MAX(id) as id, user_friend_id 
				FROM ".PREFIX."user_friend_history user_friend_history 
				GROUP BY user_friend_id) fh2 
		ON fh2.user_friend_id=user_friend.id
	LEFT JOIN ".PREFIX."user_friend_history fh ON fh.user_friend_id=user_friend.id AND fh.id=fh2.id
	WHERE (user_friend.requested_by=".sql_safe($user1)." AND user_friend.user=".sql_safe($user2).")
	OR (user_friend.requested_by=".sql_safe($user2)." AND user_friend.user=".sql_safe($user1).");";
	
	$ff=mysql_query($sql);
	while($s=mysql_fetch_assoc($ff))
	{
		$f=$s;
		if($f['status']=="ACCEPTED")
		{
			return $f;
		}
		else if($f['status']=="REJECTED")
		{
			if($f['requested_by']==$user1)
				$f['status']='FORBIDDEN';
			return $f;
		}
		else if($f['status']=="NEW" && $f['requested_by']==$user1)
		{
			$f['status']="DESIRED";
			return $f;
		}
		else if(!strcmp($f['status'],"NEW") && $f['requested_by']==$user2)
		{
			$f['status']="PENDING";
			return $f;
		}
	}
	return array();
}

function user_friend_get_request_button($user_id)
{
	//Check user is logged in
	if(login_check_logged_in_mini()<1)
		return FALSE;
	
	$logged_in_user=login_get_user();
	
	//Check that it isn't logged in user
	if($logged_in_user==$user_id)
		return FALSE;


	//Check current friendship status
	$current_friendship=user_friend_get($user_id, $logged_in_user);
		
	if(isset($current_friendship['status']) && $current_friendship['status']=="PENDING")
	{
		return "<p><i>".sprintf(_("Friendship requested %s"),date("Y-m-d H:i",strtotime($current_friendship['request_time'])))."</i></p>";
	}
	if(isset($current_friendship['status']) && $current_friendship['status']=="ACCEPTED")
	{
		return "<p><i>".sprintf(_("Friendship accepted %s"),date("Y-m-d H:i",strtotime($current_friendship['update_time'])))."</i></p>";
	}
	
	if(isset($current_friendship['status']) && ($current_friendship['status']=="FORBIDDEN" || $current_friendship['status']=="REJECTED"))
	{
		$return="<p><i>".sprintf(_("Friendship rejected %s"),date("Y-m-d H:i",strtotime($current_friendship['update_time'])))."</i></p>";
		if($current_friendship['status']=="FORBIDDEN")
		{
			$return.='<form method="post">
				<input type="hidden" value="'.$user_id.'" name="user_id">
				<input type="submit" class="btn success" value="'._("Add as friend").'" name="add_user_friend">';
		}
	}
	else
	{
		if(isset($current_friendship['status']) && $current_friendship['status']=="DESIRED")
			$button_text=_("Accept friend request");
		else
			$button_text=_("Add as friend")." +";
		$return='<form method="post">
			<input type="hidden" value="'.$user_id.'" name="user_id">
			<input type="submit" class="btn success" value="'.$button_text.'" name="add_user_friend">';
		if(isset($current_friendship['status']) && $current_friendship['status']=="DESIRED")
			$return.='<input type="submit" class="btn error" value="'._("Reject friend request").'" name="reject_user_friend">';
		$return.='</form>';
	}
	return $return;
}

function user_friend_get_requests($user_id)
{
	$sql="SELECT * FROM user_friend WHERE user=".sql_safe($user_id)." AND status='NEW';";
	$return=array();
	if($rr=mysql_query($sql))
	{
		while($r=mysql_fetch_assoc($rr))
		{
			$return[]=$r;
		}
	}
	return $return;
}

/***
*	function user_get_browser
*	https://gist.github.com/james2doyle/5774516
***/
function user_get_browser()
{
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$bname = 'Unknown';
	$platform = 'Unknown';
	$version= "";
	// First get the platform?
	if (preg_match('/linux/i', $u_agent)) {
		$platform = 'linux';
	} elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
		$platform = 'mac';
	} elseif (preg_match('/windows|win32/i', $u_agent)) {
		$platform = 'windows';
	}
	// Next get the name of the useragent yes seperately and for good reason
	$ub="";
	if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
		$bname = 'Internet Explorer';
		$ub = "MSIE";
	} elseif(preg_match('/Firefox/i',$u_agent)) {
		$bname = 'Mozilla Firefox';
		$ub = "Firefox";
	} elseif(preg_match('/Chrome/i',$u_agent)) {
		$bname = 'Google Chrome';
		$ub = "Chrome";
	} elseif(preg_match('/Safari/i',$u_agent)) {
		$bname = 'Apple Safari';
		$ub = "Safari";
	} elseif(preg_match('/Opera/i',$u_agent)) {
		$bname = 'Opera';
		$ub = "Opera";
	} elseif(preg_match('/Netscape/i',$u_agent)) {
		$bname = 'Netscape';
		$ub = "Netscape";
	}
	// finally get the correct version number
	$known = array('Version', $ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
	if (!preg_match_all($pattern, $u_agent, $matches)) {
	// we have no matching number just continue
	}
	// see how many we have
	$i = count($matches['browser']);
	if ($i != 1) {
	//we will have two since we are not using 'other' argument yet
	//see if version is before or after the name
	if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
	$version= $matches['version'][0];
	} else {
	$version= $matches['version'][1];
	}
	} else {
	$version= $matches['version'][0];
	}
	// check if we have a number
	if ($version==null || $version=="") {$version="?";}
	return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'    => $pattern
		);
}

function user_insert($username, $email)
{
	//generera ett lösenord
	$password=password_generate(32);
	
	$db=new db_class();
	
	//Check for available username
	$username_found=FALSE;
	$existing_user=user_get_id_from_username($username);
	if($existing_user===NULL)
		$username_found=TRUE;
	else
	{
		for($i=1; $i<100; $i++)
		{
			$existing_user=user_get_id_from_username($username."_".$i);
			if($existing_user===NULL)
			{
				$username.="_".$i;
				$username_found=TRUE;
                break;
			}
		}
	}
	
	if($db->insert_from_array(PREFIX."user", array(	"username"	=>	$username,
														"email"		=>	$email,
														"password"	=>	$password
                                                )
                            )
    ) {
		$user_id=$db->insert_id;
        if($db->update_from_array(PREFIX."user", array("password"	=>	crypt($password, $user_id.$email)), $user_id))
            return $user_id;
        else
        {
            add_error($db->error);
        }
    }
	return FALSE;
}



?>