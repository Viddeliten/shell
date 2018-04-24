<?php

function login_receive()
{	
	login_check();
	
	if(isset($_GET['lostpassword']))
	{
		login_password_recovery_receive();
	}
	if(isset($_GET['reg']) && isset($_POST['upsign']))
	{
		if(login_captcha_check())
			user_register();
	}
}

function login_captcha_check()
{
	if(isset($_POST['g-recaptcha-response']))
		$response=json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".ReCaptcha_privatekey."&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']), true);
	
	if(!isset($response))
	{
		add_error(_("You do not appear to be human. Feeling ok?"));
		return FALSE;
	}
	else if(isset($response['error-codes'][0]) && !strcmp($response['error-codes'][0],'missing-input-response'))
	{
		//Human was a robot or forgot to check captcha
		add_error(_("Seems you forgot to check captcha. Hit 'back' in your browser and try again!"));
		return FALSE;
	}
	else
		return TRUE;
}

/************************************************************************/
/* Kollar om användaren är inloggad, försöker logga ut eller loggar in	*/
/************************************************************************/
function login_check()
{
	if(isset($_GET['logout']))
	{
		login_logout();
	}
	else if(isset($_GET['inlog']) && isset($_POST['username']) && $_POST['username']!="") //Om användaren har sänt inloggningsinfo redan
	{
		//Hämta posten (om den finns) där username matchar den inmatade
		$_SESSION[PREFIX.'inloggad']=0;
		$login=false;

		$sql="SELECT * FROM ".PREFIX."user WHERE username='".sql_safe($_POST['username'])."';";
		// echo "<br />DEBUG0922: $sql";
		if($post=mysql_query($sql))
		{
			//Det finns en användare med den e-posten!
			$user=mysql_fetch_array($post);
			$login=true;
		}
		else echo "No user";
		
		//Om det inte fanns, eller lösenordet inte stämmer, eller om e-posten inte stämde (hur fabian nu det ska kunna hända)
		if(!$login || strcmp($user['password'],crypt($_POST['password'], $user['id'].$user['email'])) || strcmp($user['username'],$_POST['username'])) //Om det var inkorrekt
		{
			//<-- Felaktig inloggning -->
			login_logout();
			add_error(_("Incorrect info"));
		}
		
		//Om det var korrekt
		// if($login && !strcmp($user['password'],md5($_POST['password'])) && !strcmp($user['email'],$_POST['email']))
		if($login && !strcmp($user['password'],crypt($_POST['password'], $user['id'].$user['email'])) && !strcmp($user['username'],$_POST['username']))
		{
			//<-- Korrekt inloggning. Hälsa användaren välkommen -->
			//<-- skapa en session med användarid't, så att användaren kan smurfa runt -->
			if($user['lastlogin']==NULL)
			{
				//medlemmen har aldrig loggat in förr
				//Ge medlemmen intro
				if(FIRST_TIME_LOGIN_TEXT!="")
					add_message(FIRST_TIME_LOGIN_TEXT);
				if(function_exists ( "login_custom_first_login" ))
					login_custom_first_login($user['id']);
			}
			else
				message_add_success_message(_("Login successfull"));
			
			$_SESSION[PREFIX.'user_id']=$user['id'];
			$_SESSION[PREFIX.'username']=$user['username'];
			$_SESSION[PREFIX.'password']=$_POST['password'];
			$_SESSION[PREFIX.'inloggad']=$user['level'];
			$_SESSION[PREFIX."HTTP_USER_AGENT"] = md5($_SERVER['HTTP_USER_AGENT']);
			setcookie("login",md5($_SESSION[PREFIX.'user_id']),time()+(60*15));
			
			//Uppdatera senast inloggning
			$sql="UPDATE ".PREFIX."user set lastlogin=now(), inactive=NULL WHERE id='".$user['id']."'";
			if(!mysql_query($sql))
				add_error(mysql_error());
		}
		else
			add_error(_("Login fail"));
	}
	else
	{
		//Kolla om användaren är inloggad och allt stämmer
		if(isset($_SESSION[PREFIX.'user_id']) && $_SESSION[PREFIX.'user_id']>0)
		{
			if(isset($_SESSION[PREFIX.'inloggad']) && $_SESSION[PREFIX.'inloggad']>0)
			{
				if($uu=mysql_query("SELECT id, password, level, email FROM ".PREFIX."user WHERE id='".$_SESSION[PREFIX.'user_id']."';"))
				{
					if($u=mysql_fetch_array($uu))
					{
						// if(md5($_SESSION['".PREFIX."password'])==$u['password'])
						if(crypt($_SESSION[PREFIX.'password'], $u['id'].$u['email'])==$u['password'])
						{
							if($_SESSION[PREFIX."HTTP_USER_AGENT"] ==md5($_SERVER['HTTP_USER_AGENT']))
							{
								$_SESSION[PREFIX.'inloggad']=$u['level'];
								setcookie("login",md5($_SESSION[PREFIX.'user_id']),time()+(60*15));
								mysql_query("UPDATE ".PREFIX."user set lastlogin='".date("YmdHis")."', active=1 WHERE id='".$u['id']."'");
								return $_SESSION[PREFIX.'inloggad'];
							}
							else
								$_SESSION[PREFIX.'inloggad']=-1;
						}
						else
							$_SESSION[PREFIX.'inloggad']=-2;
					}
					else
						$_SESSION[PREFIX.'inloggad']=-3;
				}
				else
					$_SESSION[PREFIX.'inloggad']=-4;
			}
			else
				$_SESSION[PREFIX.'inloggad']=-5;
		}
		else
			$_SESSION[PREFIX.'inloggad']=-6;
			
		if($_SESSION[PREFIX.'inloggad']<1)
		{
			login_logout();
		}
	}
	
	return 0;
}

function login_get_user()
{
	if(login_check_logged_in_mini()>0)
		return $_SESSION[PREFIX.'user_id'];
	return FALSE;
}

function login_check_logged_in_mini()
{
	// echo "<br />Inloggad: ".$_SESSION["".PREFIX."inloggad"];
	if(isset($_SESSION[PREFIX.'inloggad']) && isset($_SESSION[PREFIX.'user_id']))
	{
		if($_SESSION["".PREFIX."inloggad"]>0)
		{
			if($_SESSION[PREFIX.'user_id']!="")
			{
				if (md5($_SERVER['HTTP_USER_AGENT']) == $_SESSION[PREFIX."HTTP_USER_AGENT"])
				{
						//RÃ¤tt inloggad. GÃ¶r ingenting
						return $_SESSION[PREFIX."inloggad"];
				}
				else
					$_SESSION[PREFIX."inloggad"]=-6;
			}
			else
				$_SESSION[PREFIX."inloggad"]=-7;
		}
		else
			$_SESSION["".PREFIX."inloggad"]=-8;
		
		echo "<br />FEL: ".$_SESSION[PREFIX."inloggad"];
		login_logout();
		return $_SESSION[PREFIX."inloggad"];
	}
	else
		$_SESSION[PREFIX."inloggad"]=-9;			
	
	return NULL;
}
function login_logout()
{
	//Uppdatera senast inloggning
	if(isset($_SESSION[PREFIX.'user_id']))
	{
		mysql_query("UPDATE ".PREFIX."user set lastlogin='".date("YmdHis")."', active=1 WHERE id='".$_SESSION[PREFIX.'user_id']."'");

		//Do not destroy session
		unset($_SESSION[PREFIX.'user_id']);
		unset($_SESSION[PREFIX.'username']);
		unset($_SESSION[PREFIX.'password']);
		unset($_SESSION[PREFIX.'inloggad']);
		unset($_SESSION[PREFIX.'HTTP_USER_AGENT']);
			
		add_message("You are now logged out");
	}
}

function login_form_login_in_navbar()
{
	login_check_logged_in_mini();
	if(isset($_SESSION[PREFIX."username"]))
	{
		//User dropdown menu
		echo '<li class="dropdown">';
			user_display_dropdown();
        echo '</li>';
	}
	else
	{
		echo "<li>";
			login_form_login_inline();
		echo "</li>";
	}	
}

function login_form_login_inline()
{
	echo "<form class=\"form-inline navbar-right\" role=\"form\" action=\"?inlog\" method=\"post\">
		<input type=\"text\" name=\"username\" placeholder=\"". _("Username") ."\" class=\"form-control\" >
		<input type=\"password\" name=\"password\" placeholder=\"". _("password") ."\" class=\"form-control\" >
		<button type=\"submit\" name=\"inlog\" class=\"btn btn-primary\">". _("Log in") ."</button>
		-
		<a href=\"?reg\" class=\"btn btn-success\">". _("Sign up") ."</a>
		<a href=\"?lostpassword\">". _("Recover password") ."</a> </form>";
}

function login_form_password_recovery_require_link()
{
	if(!isset($_POST['lostpassword']))
	{
		echo "<h3>Recovering password</h3>
		
		<form action=\"?lostpassword\" method=\"post\">
		<p>Enter your email adress to recover your password: </p>
		<p><input type=\"text\" name=\"email\" class=\"form-control\" ></p>
		<p><input type=\"submit\" name=\"lostpassword\" value=\"Send me a new password!\" class=\"button\"></p>
		</form>";	
	}
}

function login_form_registration()
{
	if(!defined('REGISTRATION_DONE'))
	{
		echo '<h1>Registration</h1>

		<form action="?reg" method="post">
			<div class="form-group">
				<label for="signup_name">Username:</label>
				<input type="text" name="name" id="signup_name" class="form-control">
			</div>
			<div class="form-group">
				<label for="signup_email">Email:</label> 
				<input type="text" name="email" id="signup_email" class="form-control">
			</div>';

		require_once('functions/recaptchalib.php');
		echo '<div class="g-recaptcha" data-sitekey="'.ReCaptcha_publickey.'"></div>';

		echo '
			<input type="submit" name="upsign" value="Sign me up!" class="btn btn-default">
		</form>';
	}
}

function login_password_recovery_receive()
{
	if(isset($_POST['lostpassword']) && isset($_POST['email']) && $_POST['email']!="") //Om man angett användarnamn så skickar vi ut ett lösenord.
	{
		$password=login_create_reset_code($_POST['email']);
		if($password!=NULL)
		{
			//Skicka ett email
			$to = $_POST['email'];
			$subject = sprintf(gettext("[%s] - Password reset"),SITE_NAME);
			$body = sprintf(_("Hi").",\n\n"._("We have received a request to reset your password.")."
			\n\n"._("Visit %s/?lostpassword&password_reset=%s to set your password.")."
			\n\n"._("Regards,")."\n"._("The %s Team"), SITE_URL, $password, SITE_NAME);
			$headers = 'From: '.CONTACT_EMAIL . "\r\n" .
"Reply-To: ".CONTACT_EMAIL."\r\n" .
'X-Mailer: PHP/' . phpversion();
			
			//Skicka mail!
			if (mail($to, $subject, $body, $headers))
			{
				add_message(_("Message successfully sent!"));
			}
			else
			{
				add_error(sprintf(_("Message delivery failed. Please send an email to %s for further assistance! errorcode %d"),CONTACT_EMAIL, 1726));
				// password=$password</p>");
			}
		}		
	}
}
function login_password_recovery_display()
{
	if(isset($_GET['password_reset']))
		login_form_password_recovery_set($_GET['password_reset']);
	else
		login_form_password_recovery_require_link();
}

function login_form_password_recovery_set($code)
{
	//Sätta nya uppgifter
	echo "<h1>Nya användaruppgifter</h1>";
	
	//Kolla att koden är giltig
	$sql="SELECT rc.user, rc.code, rc.added, rc.used, user.email
	FROM  ".PREFIX."user_reset_code rc 
	INNER JOIN ".PREFIX."user user ON user.id=rc.user
	WHERE 
	added>'".date("YmdHis",strtotime("-48 hours"))."' 
	AND used IS NULL
	AND rc.code='".md5($code)."' 
	ORDER BY added DESC, rc.id DESC
	LIMIT 0,1;";
	if($cc=mysql_query($sql))
	{
		if($c=mysql_fetch_array($cc))
		{
			$correct_link=1;
			$changed=0;
			//Länken är aktiv och grejs
			if(isset($_POST['username']) && isset($_POST['password']))
			{
				//Kolla så att det inte finns någon annan med samma användarnamn redan
				$sql="SELECT id FROM ".PREFIX."user 
				WHERE id!=".sql_safe($c['user'])."
				AND username='".sql_safe($_POST['username'])."';";
				// echo "<br />DEBUG1302: $sql";
				mysql_query($sql);
				if(mysql_affected_rows()>0)
				{
					//Nej nej, det går inte för sig!
					echo "<p class=\"error\">Användarnamnet är inte tillgängligt</p>";
				}
				else
				{
					//Sätt de nya uppgifterna
					$sql="UPDATE ".PREFIX."user 
					SET username='".sql_safe($_POST['username'])."',
					password='".crypt($_POST['password'],$c['user'].$c['email'])."'
					WHERE id=".sql_safe($c['user']).";";
					// echo "<br />DEBUG1303: $sql";
					if(mysql_query($sql))
					{
						echo "<p>Dina uppgifter har nu uppdaterats.</p>";
						//Gör koden använd också
						$sql="UPDATE ".PREFIX."user_reset_code 
							SET used='".date("YmdHis")."'
							WHERE code='".md5($code)."' AND used IS NULL;";
						// echo "<br />DEBUG0834: $sql";
						mysql_query($sql);
						define('passwordreset',"");
						echo "<p><a href=\"".SITE_URL."/?inlog\">Logga in</a></p>";
					}
					else
					{
						echo "<br />SQL-fel!!!";
					}
				}
			}
			else
			{
				//hämta kundens nuvarande uppgifter
				$sql="SELECT id, username FROM ".PREFIX."user WHERE id=".sql_safe($c['user']).";";
				// echo "<br />DEBUG1317: $sql";
				if($kk=mysql_query($sql))
				{
					if($k=mysql_fetch_array($kk))
					{
						echo "<p>Ange önskade användaruppgifter nedan.</p>";
						echo "<form method=\"post\" class=\"form-horizontal\">
							<div class=\"control-group\">
								<label class=\"control-label\" for=\"customeridinput\">Kundnummer: </label>
								<div class=\"controls\">
									<input type=\"hidden\" name=\"customerid\" id=\"customeridinput\" value=\"".$k['id']."\" readonly>
									<input type=\"hidden\" name=\"code\" value=\"".$code."\" readonly>
									<input type=\"text\" name=\"customerid_txt\" value=\"".$k['id']."\" readonly class=\"form-control\">
								</div>
							</div>
							<div class=\"control-group\">
								<label for=\"username\" class=\"control-label\">Användarnamn: </label>
								<div class=\"controls\">
									<input type=\"text\" name=\"username\" value=\"".$k['username']."\" class=\"form-control\">
								</div>
							</div>
							<div class=\"control-group\">
								<label for=\"password\" class=\"control-label\">Lösenord: </label>
								<div class=\"controls\">
									<input type=\"password\" name=\"password\" class=\"form-control\">
								</div>
							</div>
							<div class=\"control-group\">
								<label for=\"userinfosubmitbutton\" class=\"control-label\"></label>
								<div class=\"controls\">
									<input type=\"submit\" value=\"Spara\" class=\"button btn\" id=\"userinfosubmitbutton\">
								</div>
							</div>
						</form>";
					}
				}
			}
		}
		else
			echo "<br />Error5281713";
	}
	
	if(!isset($correct_link))
	{
		echo "<p>Länken är inte giltig. Endast den senaste länken som skickats ut kan användas. Länken är giltig i 48 timmar.</p>
		<p><a href=\"".SITE_URL."/?lostpassword\">Begär uppgifter på nytt</a></p>";
	}
}

function login_create_reset_code($email)
{
	//Skaffa sig id.
	$sql="SELECT id, email FROM ".PREFIX."user WHERE email='".sql_safe($email)."';";

	if($ee=mysql_query($sql))
	{
		if($e=mysql_fetch_array($ee))
		{
			//generera en kod
			$password=password_generate(16);
						
			$sql="INSERT INTO ".PREFIX."user_reset_code SET 
				user='".$e['id']."',
				code='".md5($password)."';";
			if(mysql_query($sql))
				return $password;
			add_error("Reset was impossible ".mysql_error());
		}
		else
			add_error("The email address was not found in the system.");
	}
	else
		add_error("DB error: ".mysql_error());
	return NULL;
}

function login_display_link($a_text="", $return_html=FALSE)
{
    ob_start();
	login_check_logged_in_mini();
	if(isset($_SESSION[PREFIX."username"]))
	{
		//User dropdown menu
		user_display_dropdown();
	}
	else
	{
		// echo '<a href="#main_login_form" onclick="toggleshow(\'main_login_form\');">'._("Log in").'</a>';
		// echo '<a href="#main_login_form" onclick="$( \'#main_login_form\' ).slideDown( \'normal\');">'._("Log in").'</a>';
		echo '<a class="hidden-lg hidden-md hidden-sm" href="#" onclick="$( \'#main_login_form\' ).slideDown( \'normal\');" '.$a_text.'>'._("Log in").'</a>'; //Just on small (xs) devices
		echo '<a class="hidden-xs" href="#" onclick="$( \'#main_login_form\' ).slideDown( \'normal\');" '.'>'._("Log in").'</a>'; //Not on small (xs) devices
	}
    
    $contents = ob_get_contents();
	ob_end_clean();
	
	if(!$return_html)
		echo $contents;
	else
		return $contents;
}

?>