<?php

function login_receive()
{	

	login_check();
	
	password_recovery_receive();
	
	if(isset($_GET['reg']) && isset($_POST['upsign']))
	{
		register_user();
	}
}


function password_recovery_receive()
{
	if(isset($_POST['lostpassword']) && isset($_POST['email']) && $_POST['email']!="") //Om man angett användarnamn så skickar vi ut ett lösenord.
	{
		//Skaffa sig e-postadress.
		$sql="SELECT id, email FROM ".PREFIX."user WHERE email='".sql_safe($_POST['email'])."';";
	
		if($ee=mysql_query($sql))
		{
			if($e=mysql_fetch_array($ee))
			{
				//generera en kod
				$password=password_generate(16);
							
				// $sql="UPDATE ".PREFIX."user
				// SET password='".md5($password)."'
				// WHERE email='".sql_safe($_POST['email'])."';";
				$sql="INSERT INTO ".PREFIX."user_reset_code SET 
					user='".$e['id']."',
					code='".sql_safe($password)."';";
				if(mysql_query($sql))
				{
					//Skicka ett email
					$to = $e['email'];
					$subject = "[".SITE_NAME."] - Password reset";
					$body = "Hi,\n\nWe have received a request to reset your password.
					\n\nVisit ".SITE_URL."/?lostpassword&amp;password_reset=$password to set your password.
					\n\nRegards,\nThe ".SITE_NAME." Team";
					$headers = 'From: '.CONTACT_EMAIL . "\r\n" .
    "Reply-To: ".CONTACT_EMAIL."\r\n" .
    'X-Mailer: PHP/' . phpversion();
					
					//Skicka mail!
					if (mail($to, $subject, $body, $headers))
					{
						add_message("Message successfully sent! :)");
					}
					else
					{
						add_error("Message delivery failed. Please send an email to ".CONTACT_EMAIL." for further assistance! errorcode 1726");
						// password=$password</p>");
					}
				}
				else
				{
					echo "<p>There was some kind of error...</p>";
				}
			}
			else
			{
				echo "<p>No user with that email...</p>";
			}
		}
		else
		{
			echo "<p>No user with that email...</p>";
		}
		
	}
}

/************************************************************************/
/* Kollar om användaren är inloggad, försöker logga ut eller loggar in	*/
/************************************************************************/
function login_check()
{
	if(isset($_GET['logout']))
	{
		logout();
	}
	else if(isset($_GET['inlog']) && isset($_POST['email']) && $_POST['email']!="") //Om användaren har sänt inloggningsinfo redan
	{
		echo "Login try...";
		//Hämta posten (om den finns) där email matchar den inmatade
		$_SESSION[PREFIX.'inloggad']=0;
		$login=false;
		$post_email=$_POST['email'];
		$post_email=addslashes($post_email);

		if($post=mysql_query("SELECT * FROM ".PREFIX."user WHERE email='".sql_safe($_POST['email'])."';"))
		{
			//Det finns en användare med den e-posten!
			$user=mysql_fetch_array($post);
			$login=true;
		}
		else echo "No user";
		
		//Om det inte fanns, eller lösenordet inte stämmer, eller om e-posten inte stämde (hur fabian nu det ska kunna hända)
		if(!$login || strcmp($user['password'],md5($_POST['password'])) || strcmp($user['email'],$_POST['email'])) //Om det var inkorrekt
		{
			//<-- Felaktig inloggning -->
			logout();
			echo "Incorrect info";
		}
		
		//Om det var korrekt
		if($login && !strcmp($user['password'],md5($_POST['password'])) && !strcmp($user['email'],$_POST['email']))
		{
			//<-- Korrekt inloggning. Hälsa användaren välkommen -->
			//<-- skapa en session med användarid't, så att användaren kan smurfa runt -->
			if($user['lastlogin']==NULL)
			{
				//medlemmen har aldrig loggat in förr
				//Ge medlemmen intro
				echo "<p>".FIRST_TIME_LOGIN_TEXT."</p>";
			}
			else
				echo "Login successfull";
				// echo add_message("Login successfull");
			
			$_SESSION[PREFIX.'user_id']=$user['id'];
			$_SESSION[PREFIX.'username']=$user['username'];
			$_SESSION[PREFIX.'password']=$_POST['password'];
			$_SESSION[PREFIX.'inloggad']=$user['level'];
			$_SESSION[PREFIX."HTTP_USER_AGENT"] = md5($_SERVER['HTTP_USER_AGENT']);
			setcookie("login",md5($_SESSION[PREFIX.'user_id']),time()+(60*15));

			//Uppdatera senast inloggning
			$sql="UPDATE ".PREFIX."user set lastlogin=now(), inactive=NULL WHERE id='".$user['id']."'";
			if(!mysql_query($sql))
				echo "<pre>".mysql_error()."</pre>";
		}
		echo "Login fail";
	}
	else
	{
		//Kolla om användaren är inloggad och allt stämmer
		if(isset($_SESSION[PREFIX.'inloggad']) && $_SESSION[PREFIX.'inloggad']>0)
		{
			if($uu=mysql_query("SELECT id, password, level FROM ".PREFIX."user WHERE id='".$_SESSION[PREFIX.'user_id']."';"))
			{
				if($u=mysql_fetch_array($uu))
				{
					if(md5($_SESSION['".PREFIX."password'])==$u['password'])
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
			
		if($_SESSION[PREFIX.'inloggad']<1)
			logout();
	}
	
	return 0;
}

function login_check_logged_in_mini()
{
	// echo "<br />Inloggad: ".$_SESSION["".PREFIX."inloggad"];
	if(isset($_SESSION[PREFIX.'inloggad']))
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
					$_SESSION["".PREFIX."inloggad"]=-6;
			}
			else
				$_SESSION["".PREFIX."inloggad"]=-7;
		}
		else
			$_SESSION["".PREFIX."inloggad"]=-8;
		
		// echo "<br />FEL: ".$_SESSION["".PREFIX."inloggad"];
		logout();
		return $_SESSION["".PREFIX."inloggad"];
	}
	else
		$_SESSION["".PREFIX."inloggad"]=-9;			
	
	return NULL;
}


function register_user()
{
	if($_POST['name']!="" && $_POST['email']!="")
	{
		//Försök registrera denna användare.
		
		//Kolla först så att email-adressen inte redan finns
		$exists=false;
		$post_email=addslashes($_POST['email']);
		if($users=mysql_query("SELECT * FROM ".PREFIX."user WHERE email='$post_email';"))
		{
			while($u=mysql_fetch_array($users))
			{
				if(!strcasecmp($u['email'],$post_email))
					$exists=true;
			}
		}
		//Kolla så att användarnamnet inte innehåller konstiga tecken eller är SITE_NAME
/*			$username=str_replace("<","", $_POST['name']);
		$username=str_replace(">","", $username);
		$username=str_replace("_","", $username);
		$username=str_replace("'","", $username);
		$username=str_replace(",","", $username); */
		
		//Kolla så att strängen är alfanumerisk
		if (eregi_replace('[a-z0-9]', '', $_POST['name']) == '')
		{
			//Kolla sedan så användarnamnet inte är upptaget
			$existsname=false;
			if($users=mysql_query("SELECT * FROM ".PREFIX."user WHERE username='".sql_safe($_POST['name'])."';"))
			{
				while($u=mysql_fetch_array($users))
				{
					if(!strcasecmp($u['username'],$_POST['name']))
						$existsname=true;
				}
			}
			
			if($exists)
			{
				echo "<p class=\"error\">Email adress is allready registered</p>";
			}
			else if($existsname || !strcasecmp($_POST['name'],SITE_NAME))
			{
				echo "<p class=\"error\">Username is allready registered</p>";
			}
			else
			{
				//generera ett lösenord
				$password=password_generate(8);
//				echo "<br />DEBUG: password='$password'";
			
				//Skriv in info i databasen
				$sql="INSERT INTO ".PREFIX."user
				(username, email, password)
				VALUES ('".$_POST['name']."','$post_email','".md5($password)."');";
				$went_fine=mysql_query($sql);
				if($went_fine)
				{
					echo "<p>Registration went fine. You will soon recieve an email with your password!</p>";
					
					//Skicka ett email
					$to = $_POST['email'];
					$subject = "[".SITE_NAME."] - Welcome!";
					$body = "Hi,\n\nYou are receiving this message because your email adress was used to sign up at ".SITE_URL.". If this was not you, simply ignore this message.

Your password is: $password\n\nLog in at ".SITE_URL."?login and please change password as soon as possible due to security reasons!

Regards,\nThe ".SITE_NAME." Team";
					$headers = 'From: '.CONTACT_EMAIL . "\r\n" .
    'Reply-To: '.CONTACT_EMAIL . "\r\n" .
    'X-Mailer: PHP/' . phpversion();
					
					//Skicka mail! Det funkar inte i WAMP, men jag tror det beror på inställningar... kanske.
					if (mail($to, $subject, $body, $headers))
					{
						echo("<p>Message successfully sent!</p>");
					}
					else
					{
						echo("<p>Message delivery failed... password=$password</p>");
					}
					define('REGISTRATION_DONE');
				}
				else
				{
					echo "<p class=\"error\">There was a problem. Try again.</p>
					<pre>".mysql_error()."</pre>";
				}
			}
		}
		else
		{
			echo "<p class=\"error\">Only alphanumeric usernames are allowed!</p>";
		}
	}
}

function show_login()
{
	if(isset($_GET['reg'])) // && $_GET[reg]==true)
	{
		if(!isset($_POST['upsign']))
		{
			show_reg_form();
		}
		else
		{
			register_user();
		}
	}
	else if(isset($_GET['inlog']) && isset($_SESSION[PREFIX.'inloggad']) && $_SESSION[PREFIX.'inloggad']>0) //Om användaren har sänt inloggningsinfo redan
	{
			echo "<p>Welcome ".user_get_name($_SESSION[PREFIX.'user_id'])."!</p>";
	}
	else if(isset($_GET['inlog']))
	{
		echo "<p>You were not logged in</p>";
	}
	else if(!isset($_SESSION[PREFIX.'inloggad']) || $_SESSION[PREFIX.'inloggad']<1)
	{
		show_inlog_form();
	}
}

function show_reg_form()
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
			</div>
			<input type="submit" name="upsign" value="Sign me up!" class="btn btn-default">
		</form>';
	}
}

function password_recovery()
{
	if(isset($_GET['password_reset']))
		
	else
		show_password_recovery_form();
}

function show_password_recovery_form()
{
	echo "<h3>Recovering password</h3>
	
	<form action=\"?lostpassword\" method=\"post\">
	<p>Enter your email adress to recover your password: </p>
	<p><input type=\"text\" name=\"email\" class=\"form-control\" ></p>
	<p><input type=\"submit\" name=\"lostpassword\" value=\"Send me a new password!\" class=\"button\"></p>
	</form>";	
}

function show_inlog_form()
{
	echo "<div class=\"blocker\"></div>";
	echo "<h3>Log in</h3>

	<form action=\"?inlog\" method=\"post\">
	<p><label for=\"email\">Email:</label> <input type=\"text\" name=\"email\" class=\"form-control\" ></p>
	<p><label for=\"password\">Password:</label> <input type=\"password\" name=\"password\" class=\"form-control\" ></p>
	<p><input type=\"submit\" name=\"inlog\" value=\"Log me in!\"></p>
	</form>";
	
	//Glömt-lösenordet-länk
	echo "<p><a href=\"?lostpassword\">Forgot your password?</a></p>";
	
	//Länk till registrering här
	echo "<p><a href=\"?reg\">Get yourself signed up to the game absolutely free of charge!</a></p>";
}

function show_inlog_form_inrow()
{
	login_check_logged_in_mini();
	if(isset($_SESSION[PREFIX."username"]))
	{
		echo "<p>Inloggad som ".$_SESSION[PREFIX."username"]."</p>";
	}
	else
	{
		echo "<form class=\"form-inline navbar-right\" role=\"form\" action=\"?inlog\" method=\"post\">
		<input type=\"text\" name=\"email\" placeholder=\"Email\" class=\"form-control\" >
		<input type=\"password\" name=\"password\" placeholder=\"password\" class=\"form-control\" >
		<button type=\"submit\" name=\"inlog\" class=\"btn btn-primary\">Log in</button>
		-
		<a href=\"?reg\" class=\"btn btn-success\">Sign up</a>
		<a href=\"?lostpassword\" class=\"btn btn-default\">Recover password</a> </form>";
	}	
}

function logout_show()
{
	if(isset($_SESSION[PREFIX.'inloggad']) && $_SESSION[PREFIX.'inloggad']>0)
	{
		if(isset($_GET[logout]))
		{
			//Uppdatera senast inloggning
			mysql_query("UPDATE ".PREFIX."user set lastlogin='".date("YmdHis")."', active=1 WHERE id='".$_SESSION[PREFIX.'user_id']."'");

			session_unset();
			session_destroy();
			echo "<p>You are now logged out</p>";
			echo "<p><a href=\"?\">Log in</a></p>";
		}
		else
		{
			echo "<p><a href=\"?logout\">Log out</a></p>";
		}
	}
	else
	{
		echo "<p class=\"message_box\">You are now logged out.</p>";
	}
}

function logout()
{
	//Uppdatera senast inloggning
	if(isset($_SESSION[PREFIX.'user_id']))
	{
		mysql_query("UPDATE ".PREFIX."user set lastlogin='".date("YmdHis")."', active=1 WHERE id='".$_SESSION[PREFIX.'user_id']."'");

		session_unset();
		session_destroy();
	}
}

function F_customer_login_password_set($code)
{
	//Sätta nya uppgifter
	echo "<h1>Nya användaruppgifter</h1>";
	
	// $changed=0;
	
	// echo "<br />$code";
	
	//Kolla att koden är giltig
	$sql="SELECT reset_code.customer, reset_code.code, reset_code.added, reset_code.used, customer.org_nr
	FROM  reset_code 
	INNER JOIN customer ON customer.id=reset_code.customer
	WHERE 
	added>'".date("YmdHis",strtotime("-48 hours"))."' 
	AND used IS NULL
	AND reset_code.code='".md5($code)."' 
	ORDER BY added DESC, reset_code.id DESC
	LIMIT 0,1;";
	// echo "<br />DEBUG2038: <pre>$sql</pre>";
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
				$sql="SELECT id FROM customer 
				WHERE id!=".sql_safe($c['customer'])."
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
					$sql="UPDATE customer 
					SET username='".sql_safe($_POST['username'])."',
					password='".crypt($_POST['password'],$c['customer'].$c['org_nr'])."'
					WHERE id=".sql_safe($c['customer']).";";
					// echo "<br />DEBUG1303: $sql";
					if(mysql_query($sql))
					{
						echo "<p>Dina uppgifter har nu uppdaterats.</p>";
						//Gör koden använd också
						$sql="UPDATE reset_code 
							SET used='".date("YmdHis")."'
							WHERE code='".md5($code)."'";
						// echo "<br />DEBUG0834: $sql";
						mysql_query($sql);
						define('passwordreset',"");
						echo "<p><a href=\"".SITE_URL_SEC."/minasidor\">Logga in</a></p>";
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
				$sql="SELECT id, username FROM customer WHERE id=".sql_safe($c['customer']).";";
				// echo "<br />DEBUG1317: $sql";
				if($kk=mysql_query($sql))
				{
					if($k=mysql_fetch_array($kk))
					{
						echo "<p>Ange önskade användaruppgifter nedan.</p>";
						echo "<form method=\"post\" class=\"form-horizontal\">
							<div class=\"control-group\">
								<label class=\"control-label\" for=\"customerid\">Kundnummer: </label>
								<div class=\"controls\">
									<input type=\"hidden\" name=\"customerid\" value=\"".$k['id']."\" readonly>
									<input type=\"hidden\" name=\"code\" value=\"".$code."\" readonly>
									<input type=\"text\" name=\"customerid_txt\" value=\"".$k['id']."\" readonly>
								</div>
							</div>
							<div class=\"control-group\">
								<label for=\"username\" class=\"control-label\">Användarnamn: </label>
								<div class=\"controls\">
									<input type=\"text\" name=\"username\" value=\"".$k['username']."\">
								</div>
							</div>
							<div class=\"control-group\">
								<label for=\"password\" class=\"control-label\">Lösenord: </label>
								<div class=\"controls\">
									<input type=\"password\" name=\"password\">
								</div>
							</div>
							<div class=\"control-group\">
								<label for=\"userinfosubmitbutton\" class=\"control-label\"></label>
								<div class=\"controls\">
									<input type=\"submit\" value=\"Spara\" class=\"button\" id=\"userinfosubmitbutton\">
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
		<p><a href=\"".SITE_URL_SEC."/minasidor/?get_password\">Begär uppgifter på nytt</a></p>";
	}
}


?>