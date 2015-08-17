<?php

// define('REL_STR', "((plusones*3)
	// +((".date("YmdHis")."-IFNULL(accepted,".date("YmdHis")."))/86400)
	// +((".date("YmdHis")."-IFNULL(created,".date("YmdHis")."))/86400)
	// -((".date("YmdHis")."-IFNULL(resolved,".date("YmdHis")."))/86400)
	// -((".date("YmdHis")."-IFNULL(not_implemented,".date("YmdHis")."))/86400))
	// +(4-size)");
	
define('REL_STR',
"plusones
+IFNULL((TIMESTAMPDIFF(DAY,created,CURDATE())/365),0)
+IFNULL((TIMESTAMPDIFF(DAY,accepted,CURDATE())/365),0)
-IFNULL((TIMESTAMPDIFF(DAY,resolved,CURDATE())/365),0)
-IFNULL((TIMESTAMPDIFF(DAY,not_implemented,CURDATE())/365),0)
+(comments/10)
+(1-size/4)*3"
);

define('ORDER_STR', "IF(not_implemented,1,0) ASC, IF(resolved,resolved,30000101000000) DESC, IF(accepted,1,0) DESC, rel DESC");

function feedback_recieve()
{
	
	if(isset($_POST['postfeedback']) && $_POST['text']!="")
	{
		// if(login_check_logged_in_mini()<1)
		// {
			// require_once('functions/recaptchalib.php');
			// $resp = recaptcha_check_answer (ReCaptcha_privatekey,
							// $_SERVER["REMOTE_ADDR"],
							// $_POST["recaptcha_challenge_field"],
							// $_POST["recaptcha_response_field"]);
		// }
		
		if(isset($_POST['g-recaptcha-response']))
			$captcha=$_POST['g-recaptcha-response'];

		if (login_check_logged_in_mini()<1 && !$captcha)
		{
			// What happens when the CAPTCHA was entered incorrectly
			die ("NO!!! The reCAPTCHA wasn't entered correctly. Go back and try it again." .
			 "(reCAPTCHA said: " . $_POST['g-recaptcha-response'] . ")");
		}
		else
		{
			if(login_check_logged_in_mini()<1)
				$response=json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".ReCaptcha_privatekey."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']), true);
			if(login_check_logged_in_mini()<1 && $response['success'] == false)
			{
			  echo '<h2>'._("You do not appear to be human. Feeling ok?").'</h2>';
			}
			else
			{
				// Your code here to handle a successful verification
				if(login_check_logged_in_mini()>0)
				{
					$user="'".sql_safe($_SESSION[PREFIX.'user_id'])."'";
				}
				else
				{
					$user='NULL';
					
				}
				$IP=$_SERVER['REMOTE_ADDR'];
				
				$sql="INSERT INTO ".PREFIX."feedback SET
				subject='".sql_safe($_POST['subject'])."',
				text='".sql_safe($_POST['text'])."',
				user=".$user.",
				IP='".sql_safe($IP)."';";
				// echo "<br />DEBUG 2133: $sql";
				mysql_query($sql);
				$id=mysql_insert_id();
				define('MESS', "<p><strong>You have submitted the following message.</strong></p>
				<h3>".sql_safe($_POST['subject'])."</h3>
				<p>".sql_safe($_POST['text'])."</p>
				<p><a href=\"?p=feedback&amp;id=$id\">[Permalink]</a></p>
				<p><strong>Thankyou for your input!</strong></p>");

				if(isset($_POST['nick']))
				{
					$sql="UPDATE ".PREFIX."feedback SET nick='".sql_safe($_POST['nick'])."'
					WHERE id=$id;";
					mysql_query($sql);
				}
				if(isset($_POST['email']))
				{
					$sql="UPDATE ".PREFIX."feedback SET email='".sql_safe($_POST['email'])."'
					WHERE id=$id;";
					mysql_query($sql);
				}
				if(isset($_POST['url']))
				{
					$sql="UPDATE ".PREFIX."feedback SET url='".sql_safe($_POST['url'])."'
					WHERE id=$id;";
					mysql_query($sql);
				}
				if(isset($_POST['flattrID']))
				{
					$sql="UPDATE ".PREFIX."feedback SET flattrID='".sql_safe($_POST['flattrID'])."'
					WHERE id=$id;";
					mysql_query($sql);
				}
			}
		}
	}
		
	if(login_check_logged_in_mini()>0)
	{
		if(isset($_POST['feedback_plusone']))
		{
			// echo "<br />DEBUG1938: plusone on ".$_POST['id'];
			//Kolla så att man inte försöker plussa sina egna
			if($_SESSION[PREFIX.'user_id']==feedback_get_user($_POST['id']))
			{
				define("ERROR", "You cannot +1 on your own feedback, allthough we are sure it is nice.");
			}
			else
			{
				//echo "<br />DEBUG: ".$_SESSION[SESSION_user_id]."!=".feedback_get_user($_GET['id']);
				//Kolla om denna user redan har plussat denna
				$sql="SELECT * FROM ".PREFIX."plusone WHERE typ='feedback' AND user=".$_SESSION[PREFIX.'user_id']." AND plus_for=".sql_safe($_POST['id']).";";
				// echo "<br />DEBUG1639: $sql";
				mysql_query($sql);
				$nr_previous=mysql_affected_rows();
				// echo "<br />\$nr_previous=$nr_previous";
				if($nr_previous<1)
				{
					$sql="INSERT INTO ".PREFIX."plusone SET 
						typ='feedback', 
						user=".$_SESSION[PREFIX.'user_id'].",
						plus_for=".sql_safe($_POST['id']).";";
					// echo "<br />DEBUG2014: $sql";
					mysql_query($sql);
					
					add_message("Thankyou for putting emphasis on this suggestion!");
					feedback_count_plusone();
				}
				else
				{		
					add_message("Thank you. You already put emphasis on this suggestion!");
				}
				$sql="UPDATE ".PREFIX."feedback SET resolved=NULL WHERE id=".sql_safe($_POST['id']).";";
				// echo "<br />DEBUG: $sql";
				mysql_query($sql);
			}
		}
		
		if(isset($_POST['feedback_accept']))
		{	
			if($_SESSION[SESSION_user_logged_in]>=3)
			{
				$sql="UPDATE ".PREFIX."feedback SET accepted='".date("YmdHis")."', not_implemented=NULL WHERE id=".sql_safe($_POST['id']).";";
				if(mysql_query($sql))
				{
					define('MESS', "Task id ".$_POST['id']." accepted"); // ($sql)");
				}
				else
					define('ERROR', "Something went wron accepting the task");
			}
			else
				define('ERROR', "You are not logged in as an admin");
		}
		
		if(isset($_POST['feedback_unaccept']))
		{	
			if($_SESSION[SESSION_user_logged_in]>=5)
			{
				$sql="UPDATE ".PREFIX."feedback SET accepted=NULL WHERE id=".sql_safe($_POST['id']).";";
				if(mysql_query($sql))
				{
					define('MESS', "Task id ".$_POST['id']." unaccepted"); // ($sql)");
				}
				else
					define('ERROR', "Something went wron unaccepting the task");
			}
			else
				define('ERROR', "You are nowt logged in as an admin");
		}
		
		if(isset($_POST['feedback_resolve']))
		{	
			if($_SESSION[SESSION_user_logged_in]>=5)
			{
				$sql="UPDATE ".PREFIX."feedback SET resolved='".date("YmdHis")."' WHERE id=".sql_safe($_POST['id']).";";
				if(mysql_query($sql))
				{
					$sql="DELETE FROM ".PREFIX."plusone WHERE typ='feedback' AND plus_for=".sql_safe($_POST['id']).";";
					// echo "<br />DEBUG1320: $sql";
					if(mysql_query($sql))
					{
						feedback_count_plusone();
						// echo "<br />DEBUG1309: version_add_to_upcomping_version(".$_POST['id'].", 'feedback');";
						version_add_to_upcomping_version($_POST['id'], 'feedback');
						add_message("Task id ".$_POST['id']." resolved "); // ($sql)");
					}
				}
				else
					define('ERROR', "Something went wrong resolving the task");
			}
			else
				define('ERROR', "You are newt logged in as an admin");
		}
		
		if(isset($_POST['feedback_size_change']))
		{
			$id=$_POST['id'];
			if($_SESSION[PREFIX.'user_id']==feedback_get_user($id) || $_SESSION[PREFIX."inloggad"]>=3)
			{
				if($_POST['feedback_size_change']=="bugfix")
				{
					$size=1;
				}
				else if($_POST['feedback_size_change']=="small improvement")
				{
					$size=2;
				}
				else if($_POST['feedback_size_change']=="Big change")
				{
					$size=3;
				}
				if(isset($size))
				{
					$sql="UPDATE ".PREFIX."feedback SET size=".$size." WHERE id=".sql_safe($_POST['id']).";";
					// echo "<br /><br /><br /><br />".$sql;
					mysql_query($sql);
				}
			}
		}
	}
}

function feedback_show()
{
	echo '<div class="row">
		<div class="col-lg-8">';
	echo '<h1>'._("Feedback").'</h1>
			<p>'._("Suggestions for improvements, bufixes and ideas!").'</p>';

	if(isset($_GET['id']))
	{
		//Om vi ska visa en specifik feedback, så gör vi det här.
		$ff=feedback_get_list_specific($_GET['id']);
		feedback_list_print($ff);
	}
	else
	{
		if(isset($_GET['search']))
		{
			//Visa sökresultat
			echo "<h2>"._("Search results")."</h2>";
			$ff=feedback_search($_GET['search'], 0, 10);
			feedback_list_print($ff);
		}
		else
		{
			$ongoing=feedback_get_nr_ongoing();
			if($ongoing>0)
			{
				//Visa accepterade
				echo "<h2>"._("Ongoing")."</h2>";
				feedback_display_accepted(3);
			}
			else
			{
				//Visa några föreslagna
				feedback_display_list(-1, 3, _("Suggested"), 2);
			}
			
			//Visa några okategoriserade SOM länkar! Bara rubriker!
			feedback_display_list(0, 5, _("Uncategorized"), 2);
			//Visa några bugfixar SOM länkar! Bara rubriker!
			feedback_display_list(1, 5, _("Reported bugs"), 2);
			//Visa några required SOM länkar! Bara rubriker!
			feedback_display_list(2, 5, _("Required"), 2);
			//Visa några små SOM länkar! Bara rubriker!
			feedback_display_list(3, 5, _("Small improvements"), 2);
			//Visa några bugfixar SOM länkar! Bara rubriker!
			feedback_display_list(4, 5, _("Big changes"), 2);
			//Visa några lösta
			feedback_display_list_resolved(10, _("Resolved"), 2);
			feedback_display_list_not_implemented(5, _("Will not be done"), 2);
		}
	}
	echo '</div>
	<div class="col-lg-4">';
		//Visa sökformulär
		feedback_search_show();
		//Visa inmatningsformulär
		feedback_form_show();
		//Visa användarens feedbacks
		if(isset($_SESSION[PREFIX.'user_id']))
		{
			echo '<div class="row">';
			echo '<div class="col-lg-12 well">';
			feedback_list_user_feedback($_SESSION[PREFIX.'user_id'], "Your suggestions",3);
			echo '</div>';
			echo '</div>';
		}
	echo '</div></div>';
}

function feedback_search_show()
{
	?>
	<div class="row">
	<div class="col-lg-12 well">
	<h3><?php echo _("Search existing feedbacks"); ?></h3> 
	<form action="?p=feedback" class="form-inline">
		<input type="hidden" name="p" value="feedback">
		<div class="form-group">
		<input type="text" name="search" value="<?php if(isset($_GET['search'])) echo $_GET['search']; ?>" class="form-control">
		<input type="submit" value="<?php echo _("Search"); ?>" class="btn btn-default">
		</div>
	</form>
	</div>
	</div>
	<?php
}

function feedback_form_show()
{
	echo '<div class="row">
		<div class="col-lg-12 well">';
	echo "<h2>"._("Add feedback")."</h2>
	<form method=\"post\">
		";
	if(login_check_logged_in_mini()<1)
	{
		//Man kanske vill ange namn, e-post, hemsida och Flattr-id om man inte Ã¤r inloggad
		echo "<div class=\"form-group\"><label for=\"nick\">"._("Name").":</label> <input type=\"text\" name=\"nick\" class=\"form-control\"></div>";
		echo "<div class=\"form-group\"><label for=\"email\">"._("Email").":</label> <input type=\"text\" name=\"email\" class=\"form-control\"></div>";
		echo "<div class=\"form-group\"><label for=\"url\">"._("Website").":</label> <input type=\"text\" name=\"url\" class=\"form-control\"></div>";
		echo "<div class=\"form-group\"><label for=\"flattrID\">"._("Flattr ID").":</label> <input type=\"text\" name=\"flattrID\" class=\"form-control\"></div>";
	}
	echo "<div class=\"form-group\"><label for=\"subject\">"._("Subject").":</label> <input type=\"text\" name=\"subject\" class=\"form-control\"></div>";
	echo "<div class=\"form-group\"><label for=\"text\">"._("Your Feedback").":</label><textarea name=\"text\" class=\"form-control\"></textarea></div>";
	//Om man inte Ã¤r inloggad mÃ¥ste man ange captcha
	if(login_check_logged_in_mini()<1)
	{
		require_once('functions/recaptchalib.php');
		// echo recaptcha_get_html(ReCaptcha_publickey);
		echo '<div class="g-recaptcha" data-sitekey="'.ReCaptcha_publickey.'"></div>';
		// echo "<p>Log in to get rid of the need of captchas...</p>";
	}
	echo "<div class=\"form-group\"><input type=\"submit\" name=\"postfeedback\" value=\""._("Tell us!")."\" class=\"form-control\"></div>
	</form>";
	echo '</div>
	</div>';
}

function feedback_search($search_str, $from, $to)
{
	//hämtar sökresultat
	$str="%".sql_safe(str_replace(" ","%",$search_str))."%";
	$sql="SELECT  id, user, resolved, accepted, not_implemented, created, text, subject, plusones, nick, email, url, flattrID,
	".REL_STR." as rel
	FROM ".PREFIX."feedback
	WHERE (`text` LIKE '%$str%'	OR `subject` LIKE '%$str%')
	AND is_spam<1
	ORDER BY ".ORDER_STR."
	LIMIT ".sql_safe($from).",".sql_safe($to).";";
	//echo "<br />DEBUG: $sql";
	
	return mysql_query($sql);
}

function feedback_get_list_resolved($from, $to)
{
	//Visar de 20 mest "upptummade" feedback-texterna
	$sql="SELECT  id, user, resolved, accepted, not_implemented, created, subject, text, subject, plusones, nick, email, url, flattrID
	FROM ".PREFIX."feedback
	WHERE resolved IS NOT NULL
	AND is_spam<1
	AND merged_with IS NULL
	ORDER BY resolved DESC
	LIMIT ".sql_safe($from).",".sql_safe($to).";";

	//echo "<br />DEBUG: $sql";
	
	return mysql_query($sql);
}

function feedback_get_list_relevant($from, $to)
{
	//Formel= plusones + dagar sedan accepterad
	//ta inte med resolvade
	//Visar de 20 mest "upptummade" feedback-texterna
	$sql="SELECT *,
	".REL_STR." as rel
	FROM ".PREFIX."feedback
	WHERE resolved IS NULL
	AND is_spam<1
	AND merged_with IS NULL
	AND not_implemented IS NULL
	ORDER BY ".ORDER_STR."
	LIMIT ".sql_safe($from).",".sql_safe($to).";";
	// echo "<br />DEBUG: $sql";
	
	return mysql_query($sql);
}

function feedback_get_list_specific($id)
{
	//Formel= plusones + dagar sedan accepterad
	//ta inte med resolvade
	//Visar de 20 mest "upptummade" feedback-texterna
	$sql="SELECT id, user, resolved, accepted, not_implemented, created, text, subject, plusones, nick, email, url, flattrID
	FROM ".PREFIX."feedback
	WHERE id=".sql_safe($id).";";
	//echo "<br />DEBUG: $sql";
	
	return mysql_query($sql);
}

function feedback_get_list_random($nr, $resolved)
{
	//Visar 20 random feedback-texter
	if($resolved==0)
		$sql="SELECT *
		FROM ".PREFIX."feedback
		WHERE resolved IS NULL
		AND is_spam<1
		ORDER BY RAND()
		LIMIT 0,".sql_safe($nr).";";
	if($resolved==1)
		$sql="SELECT *
		FROM ".PREFIX."feedback
		WHERE resolved IS NOT NULL
		AND is_spam<1
		ORDER BY RAND()
		LIMIT 0,".sql_safe($nr).";";
	if($resolved==2)
		$sql="SELECT *
		FROM ".PREFIX."feedback
		WHERE is_spam<1
		ORDER BY RAND()
		LIMIT 0,".sql_safe($nr).";";
	//echo "<br />DEBUG: $sql";
	
	return mysql_query($sql);
}

function feedback_get_user($id)
{
	$sql="SELECT user
	FROM ".PREFIX."feedback
	WHERE id=".sql_safe($id).";";
	if($ff=mysql_query($sql))
	{
		if($f=mysql_fetch_array($ff))
		{
			return $f['user'];
		}
	}
	return NULL;
}
function feedback_get_size($id)
{
	$sql="SELECT size
	FROM ".PREFIX."feedback
	WHERE id=".sql_safe($id).";";
	// echo $sql;
	if($ff=mysql_query($sql))
	{
		if($f=mysql_fetch_array($ff))
		{
			return $f['size'];
		}
		
	}
	return NULL;
}
function feedback_get_is_accepted($id)
{
	$sql="SELECT accepted
	FROM ".PREFIX."feedback
	WHERE id=".sql_safe($id).";";
	// echo $sql;
	if($ff=mysql_query($sql))
	{
		if($f=mysql_fetch_array($ff))
		{
			return $f['accepted'];
		}
		
	}
	return NULL;
}

function feedback_get_is_not_implemented($id)
{
	$sql="SELECT not_implemented
	FROM ".PREFIX."feedback
	WHERE id=".sql_safe($id).";";
	// echo $sql;
	if($ff=mysql_query($sql))
	{
		if($f=mysql_fetch_array($ff))
		{
			return $f['not_implemented'];
		}
		
	}
	return NULL;
}
function feedback_get_is_resolved($id)
{
	$sql="SELECT resolved
	FROM ".PREFIX."feedback
	WHERE id=".sql_safe($id).";";
	// echo $sql;
	if($ff=mysql_query($sql))
	{
		if($f=mysql_fetch_array($ff))
		{
			return $f['resolved'];
		}
		
	}
	return NULL;
}

function feedback_list_print($data)
{
	$inloggad=login_check_logged_in_mini();
	
	while($d=mysql_fetch_array($data)) 
	{
		echo "<div class=\"panel feedback panel-default\" id=\"feedback_big_".$d['id']."\">";
			echo '<div class="panel-heading ';
				if($d['not_implemented']!=NULL)
				{
					//will not be implemented
					echo "feedback_not_implemented\">";
					// echo "<div class=\"comment\">";
				}
				else if($d['resolved']!=NULL)
				{
					//färdigt
					echo "feedback_resolved\">";
				}
				else if($d['accepted']!=NULL)
				{
					//accepterat
					echo "feedback_accepted\">";
					// echo "<div class=\"comment\">";
				}
				else
				{
					//"Ny"
					echo "\">";
					// echo "<div class=\"comment\">";
				}
				echo "<div class=\"author\">";
					//Info om vem som la upp denna
					feedback_display_author_text($d['user'], $d['nick'], $d['url'], $d['id'], $d['created']);
					
				echo "</div><!-- author -->";	

				if($d['subject']!="")
					$headline=$d['subject'];
				else
					$headline="Feedback #".$d['id'];
				echo "<h3><a href=\"".feedback_get_url($d['id'])."\">".$headline."</a></h3>";
			echo '</div>';
			echo '<div class="panel-body">';	
				//Visa själva Feedbacken!
				feedback_display_body($d['id']);
				
			echo '</div><!-- panel-body -->';
			//Visa status och sådär
			feedback_display_bottom($d['id'], "feedback_big_".$d['id']);
			
			//Bottom with comments
			echo '<div class="panel-footer">';
				comments_show_comments_and_replies($d['id'], "feedback");
			echo '</div><!-- panel-footer -->';
		echo "</div><!-- panel feedback panel-default -->";					
	}
}

function feedback_status_show($id, $accepted=NULL, $resolved=NULL, $inloggad=NULL, $div_id, $parent_div=NULL, $before_text="", $after_text="")
{
	if($parent_div==NULL)
		$parent_div=$div_id;
	
	if($accepted==NULL)
		$accepted=feedback_get_is_accepted($id);
	if($resolved==NULL)
		$resolved=feedback_get_is_resolved($id);
	if($inloggad==NULL)
		$inloggad=login_check_logged_in_mini();
	
	$not_implemented=feedback_get_is_not_implemented($id);
	
	if($inloggad>1 || $accepted || $resolved || $not_implemented)
		echo $before_text;

	echo "<div id=\"".$div_id."\">";
		
	echo '<form method="post" class="form-inline">';
	echo "<input type=\"hidden\" name=\"id\" value=\"".$id."\">";
	//Skriv först ut status.
	echo '<p>';
	// echo '<p>';
	if($not_implemented!=NULL)
		echo "[".sprintf(_("Marked not implemented %s"),date("y-m-d",strtotime($not_implemented)))."]";
	else if($resolved!=NULL)
		echo "[".sprintf(_("Resolved %s"),date("y-m-d",strtotime($resolved)))."]";
	else if($accepted!=NULL)
		echo "[".sprintf(_("Accepted %s"),date("y-m-d",strtotime($accepted)))."]";
	echo '</p>';

	//Visa admin-knappar
	if($inloggad>=3) //Man behöver inte vara superadmin för att göra bedömning om saker ska göras.
	{
		// echo '<div class="row">
		// <div class="col-lg-12">
		echo '<div class="form-group">';
		//Button for unresolve
		if($resolved!=NULL)
			echo "<button class=\"form-control\" id=\"unresolve_".$id."\" onclick=\"feedback_operation('unresolve',".$id.", '".$parent_div."'); return false;\">"._("Unresolve")."</button>";
		//acceptknapp
		if($accepted==NULL && $resolved==NULL)
			echo "<button class=\"form-control btn-success\" id=\"feedback_accept_".$id."\" onclick=\"feedback_operation('feedback_accept',".$id.", '".$parent_div."'); return false;\">"._("Accept this task")."</button>";
	}
	
	if($inloggad>=5) //...men för att bestämma att saker inte ska göras, eller att de är klara
	{
		if($resolved==NULL)
		{
 			echo "<button class=\"form-control btn-primary\" id=\"feedback_resolve_".$id."\" onclick=\"feedback_operation('feedback_resolve',".$id.", '".$parent_div."'); return false;\">"._("Done")."</button>";
			if($accepted!=NULL)
				echo "<button class=\"form-control\" id=\"feedback_unaccept_".$id."\" onclick=\"feedback_operation('feedback_unaccept',".$id.", '".$parent_div."'); return false;\">"._("Unaccept")."</button>";
			if($not_implemented==NULL)
				echo "<button class=\"form-control btn-danger\" id=\"not_implemented_".$id."\" onclick=\"feedback_operation('not_implemented',".$id.", '".$parent_div."'); return false;\">"._("Will not be implemented")."</button>";
		}
	}
	if($inloggad>=3)
		echo "</div>";
		// echo "</div>";
		// echo "</div>";
	echo "</form>";
	
	
	echo "</div>";
	
	if($inloggad>1 || $accepted || $resolved || $not_implemented)
		echo $after_text;

}
function feedback_display_size_buttons($id, $div_id="", $before_text="", $after_text="")
{
	// echo "<p>feedback_display_size_buttons</p>";
	
	
	// if(isset($_SESSION[PREFIX.'user_id']) && ($_SESSION[PREFIX.'user_id']==feedback_get_user($id) || $_SESSION[PREFIX."inloggad"]>=3))
	if(login_check_logged_in_mini()>1)
		$is_show=true;
	else
		$is_show=false;
	
	if($is_show)
		echo $before_text;
	// else
		// echo "noshow";
	
	// echo "=)";
	
	if($div_id=="")
		$div_id="feedback_size_buttons_".$id;
	echo "<div id=\"".$div_id."\">";

	if($is_show)
	{
		echo '<form class="form-inline">
			<div class="form-group">';

				echo "<h4>"._("Size").":</h4>";
				$size=feedback_get_size($id);
				if($size==1)
					echo "<strong>["._("Bugfix")."]</strong> ";
				else
					echo "<input type=\"submit\" id=\"bug_".$id."\" class=\"form-control\" onclick=\"feedback_operation('bugfix',".$id.", '".$div_id."'); return false;\" value=\""._("Bugfix")."\">";
				if($size==2)
					echo "<strong>["._("Required")."]</strong> ";
				else
					echo "<button class=\"form-control\" id=\"bug_".$id."\" onclick=\"feedback_operation('required',".$id.", '".$div_id."'); return false;\">"._("Required")."</button>";
				if($size==3)
					echo "<strong>["._("Small improvement")."]</strong> ";
				else
					echo "<button class=\"form-control\" id=\"bug_".$id."\" onclick=\"feedback_operation('small_improvement',".$id.", '".$div_id."'); return false;\">"._("Small improvement")."</button>";
				if($size==4)
					echo "<strong>["._("Big change")."]</strong> ";
				else
					echo "<button class=\"form-control\" id=\"bug_".$id."\" onclick=\"feedback_operation('big_change',".$id.", '".$div_id."'); return false;\">"._("Big change")."</button>";
		echo "</div>";
		echo "</form>";
	}
	echo "</div>";
	
	if($is_show)
		echo $after_text;
}
//räkna alla flaggor 
function feedback_count_plusone()
{
	//Man får ju börja med att sätta allt till noll..
	mysql_query("UPDATE ".PREFIX."feedback SET plusones=0;");
	
	$sql="SELECT ".PREFIX."plusone.plus_for,
	 count(".PREFIX."plusone.id) as plus
	 FROM ".PREFIX."plusone
	 WHERE typ='feedback'
	 GROUP BY ".PREFIX."plusone.plus_for";
	// echo "<br />DEBUG2309: $sql";
	if($ff=mysql_query($sql))
	{
		while($f=mysql_fetch_array($ff))
		{
			$id_to_add=$f['plus_for'];
			mysql_query("UPDATE ".PREFIX."feedback SET plusones=".$f['plus']." WHERE id=".$id_to_add.";");
			
			//Kolla om denna har föräldrar för isf ska det sättas på huvudföräldern också.
			$id_to_add=NULL;
			do
			{
				$parent_done=1;
				$sql="SELECT merged_with FROM ".PREFIX."feedback WHERE id=".$id_to_add.";";
				if($pp=mysql_query($sql))
				{
					if($p=mysql_fetch_array($pp))
					{
						$parent_done=1;
						$id_to_add=$p['merged_with'];
					}
				}
			}while(!$parent_done);
			if($id_to_add!==NULL)
				mysql_query("UPDATE ".PREFIX."feedback SET plusones=plusones+".$f['plus']." WHERE id=".$id_to_add.";");
		}
	}
}

function feedback_get_text($id)
{
	$sql="SELECT text
	FROM ".PREFIX."feedback
	WHERE id=".sql_safe($id).";";
	//echo "<br />DEBUG: $sql";
	if($ff=mysql_query($sql))
	{
		if($f=mysql_fetch_array($ff))
		{
			return $f['text'];
		}
		else
			return NULL;
	}
	else
		return NULL;
}

function feedback_show_latest_short($antal=3, $length=150, $headline_size=2)
{
	// id 	subject 	text 	user 	nick 	email 	url 	flattrID 	created 	plusones 	comments 	accepted Admin har tänkt att detta ska ske	resolved Admin tycker att detta är 
	$sql="SELECT id, subject, user, nick, email, url, flattrID, created, SUBSTRING(`text`, 1, ".sql_safe( $length).") AS texten 
	FROM ".PREFIX."feedback 
	WHERE is_spam<1
	ORDER BY created DESC 
	LIMIT 0,".sql_safe($antal).";";
	// echo "<br />DEBUG1323: $sql";
	if($ff=mysql_query($sql)) //Hämta bara de senaste
	{
		echo "<ul class=\"wdgtlist feedbacks\">";
		$first=1;
		while($f = mysql_fetch_array($ff))
		{
			$link=SITE_URL."?p=feedback&amp;id=".$f['id'];
			
			if($first)
			{
				echo "<li class=\"first\">";
				$first=0;
			}
			else
			{
				echo "<li>";
			}
			echo "<h".$headline_size."><a href=\"$link\">".$f['subject']."</a></h".$headline_size.">";

			echo "<div class=\"comment_head\">";
				//Skriv ut info om när kommentaren skrevs och av vem
				if($f['user']!=NULL)
				{
					//Kolla om vi har en avatar
					$sql="SELECT img_thumb FROM ".PREFIX."userimage WHERE user='".sql_safe($f['user'])."';";
					if($ii=mysql_query($sql))
					{
						if($im=mysql_fetch_array($ii))
						{	
							if($im['img_thumb']!=NULL)
							{
								if(file_exists(USER_IMG_URL.$im['img_thumb']))
									echo "<div class=\"left_avatar left\"><img src=\"".USER_IMG_URL.$im['img_thumb']."\" /></div>" ;
								else
								{
									$sql="UPDATE ".PREFIX."userimage SET img_thumb=NULL WHERE user='".sql_safe($f['user'])."';";
									mysql_query($sql);
									$im['img_thumb']=NULL;
								}
							}
						}
					}
						
					if(!isset($im) || $im['img_thumb']==NULL)
					{
						echo "<div class=\"left_avatar\"><img src=\"http://www.gravatar.com/avatar/".md5( strtolower( trim( user_get_email($f['user']) ) ) )."?s=60\" /></div>" ;
					}
					// echo "<div class=\"date\">Posted by <a href=\"?p=user&amp;user=".$f['user']."\"><strong>".user_get_name($f['user'])."</strong></a> at ";
				}
				else if($f['nick']!=NULL)
				{
					//Kolla om vi har en gravatar
					if($f['email']!=NULL)
					{
						echo "<img class=\"left_avatar\"  src=\"http://www.gravatar.com/avatar/".md5( strtolower( trim( $f['email'] ) ) )."?s=60\" />" ;
					}

					// if($f['url']!=NULL)
						// echo "<div class=\"date\">Posted by <a href=\"".$f['url']."\">".$f['nick']."</a> at ";
					// else
						// echo "<div class=\"date\">Posted by <strong>".$f['nick']."</strong> at ";
				}
				// else
					// echo "<div class=\"date\">"._("Posted at ");
				
				echo "<div class=\"date\">";
					
				// echo "<a href=\"$link\">".date("Y-m-d H:i:s",strtotime($f['created']))."</a>";
				feedback_display_author_text($f['user'], $f['nick'], $f['url'], $f['id'], $f['created']);
							
				//Eventuell Flattr-knapp
				if($f['user']!=NULL && flattr_get_flattr_choice($f['user'],"feedback"))
					$flattrID=flattr_get_flattrID($f['user']);
				else if($f['flattrID']!=NULL)
					$flattrID=$f['flattrID'];
				else
					$flattrID=NULL;
				$text=str_replace("\n","<br />",$f['texten']);
				$text=str_replace("<br /><br />","<br />",$text);	
				if($flattrID)
				{
					echo "sadsad<br />";
					if($f['subject']!=NULL && $f['subject']!="")
						flattr_button_show($flattrID, $link , $f['subject']." - feedback on ".SITE_URL, $text, 'compact', 'en_GB');
					else
						flattr_button_show($flattrID, $link , "Feedback ".$f['id']." - feedback on ".SITE_URL, $text, 'compact', 'en_GB');
				}
			echo "</div>";
				
			// echo "<br />DEBUG 1252: $flattrID";
			
			echo "</div>";
			echo "<div class=\"comment_body\">";
				//Skriv ut texten
				echo "<p>$text<a href=\"$link\">[...]</a></p>";
			echo "</div>";
			echo "<div class=\"clearer\"></div></li>";
		}
		echo "</ul>";
	}
}

function feedback_display_specific_headline($id, $parent_name=NULL, $expanded=FALSE, $display_user=TRUE)
{
	if(strcmp($parent_name,"feedback_line_".$id))
		$div_id=$parent_name."feedback_line_".$id;
	else
		$div_id=$parent_name;

	if($parent_name==NULL)
		$target_div=$div_id;
	else
		$target_div=$parent_name;
	
	// echo "<p>$div_id : $target_div : $parent_name</p>";
	
	$sql="SELECT id, user, resolved, accepted, not_implemented, created, text, subject, plusones, nick, email, url, flattrID
	FROM ".PREFIX."feedback
	WHERE id=".sql_safe($id).";";
	// echo $sql;
	if($data=mysql_query($sql))
	{
		if($d=mysql_fetch_array($data)) 
		{
			if($d['not_implemented']!=NULL)
				$extra_class="feedback_not_implemented";
			else if($d['resolved']!=NULL)
				$extra_class="feedback_resolved";
			else if($d['accepted']!=NULL)
				$extra_class="feedback_accepted";
			else
				$extra_class="new";
			
			if($expanded)
				$click_operation="colapse";
			else
				$click_operation="expand";			
				
			echo "<div class=\"feedback_list_line\" id=\"".$div_id."\">
				<div class=\"row $extra_class\">
					<div class=\"col-sm-8 feedback_headline\">";
						if($display_user)
							echo "<a href=\"#\" onclick=\"feedback_operation('".$click_operation."', ".$id.", '".$div_id."', '".$target_div."'); return false;\">";
						else
							echo '<a href="'.SITE_URL.'/?p=feedback&id='.$id.'">';
						echo "<strong>";
						if($d['subject']!="")
							echo $d['subject'];
						else
							echo substr($d['text'],0,128);
						echo "</strong>";
						echo "</a>
					</div>";
					if($display_user)
					{
						echo "<div class=\"col-sm-2 feedback_author\">
							".feedback_get_author_link($id)."
						</div>";
					}
					echo "<div class=\"col-sm-2 small smalldate feedback_time\">
						<a href=\"".SITE_URL."?p=feedback&amp;id=".$id."\">".date("Y-m-d H:i" , strtotime($d['created']))."</a>
					</div>
				</div>";
				
				//Display body
				if($expanded)
				{
					feedback_display_body($id);
					//Visa status och sådär
					feedback_display_bottom($id, $target_div);
					
					//Bottom with comments
					// echo '<div class="panel-footer">';
						comments_show_comments_and_replies($id, "feedback");
					// echo '</div><!-- panel-footer884 -->';
				}
			echo "</div>";
		}
	}
}


function feedback_display_accepted($nr)
{
	$sql="SELECT *,  
	".REL_STR." as rel
	FROM ".PREFIX."feedback 
	WHERE is_spam<1
	AND accepted IS NOT NULL
	AND resolved IS NULL
	AND merged_with IS NULL
	ORDER BY ".ORDER_STR."
	LIMIT ".sql_safe($nr).";";
	// echo "<pre>".print_r($sql,1)."</pre>";
	if($ff=mysql_query($sql))
	{
		feedback_list_print($ff);
		// while($f=mysql_fetch_array($ff))
		// {
			// feedback_display_specific($f['id']);
		// }
	}
}

function feedback_get_nr_ongoing()
{
	$sql="SELECT * FROM ".PREFIX."feedback 
	WHERE is_spam<1
	AND accepted IS NOT NULL
	AND resolved IS NULL
	AND merged_with IS NULL";
	mysql_query($sql);
	return mysql_affected_rows();
}

//Visa några nya SOM länkar! Bara rubriker!
function feedback_display_list($size, $nr, $headline, $headlinesize)
{
	
	$sql="SELECT id, ".REL_STR." as rel
	FROM ".PREFIX."feedback 
	WHERE is_spam<1
	";
	if($size!=-1)
		$sql.="AND size=".sql_safe($size);
	$sql.="
	AND resolved IS NULL
	AND not_implemented IS NULL
	-- AND merged_with IS NULL
	ORDER BY ".ORDER_STR."
	LIMIT ".sql_safe($nr).";";
	feedback_display_headline_list($sql, $headline, $headlinesize);
	
}

function feedback_display_list_resolved($nr, $headline, $headlinesize)
{
	$sql="SELECT id
	FROM ".PREFIX."feedback 
	WHERE is_spam<1
	AND resolved IS NOT NULL
	AND merged_with IS NULL
	ORDER BY ".ORDER_STR."
	LIMIT ".sql_safe($nr).";";
	feedback_display_headline_list($sql, $headline, $headlinesize);
	// if($ff=mysql_query($sql))
	// {
		// if(mysql_affected_rows()>0)
			// echo "<h".$headlinesize.">".$headline."</h".$headlinesize.">";
		// echo "<div class=\"row\">";
		// while($f=mysql_fetch_array($ff))
		// {
			// feedback_display_specific_headline($f['id']); //, "resolved");
		// }
		// echo "</div>";
	// }
}

function feedback_display_headline_list($sql, $headline, $headlinesize, $display_user=TRUE)
{
	if($ff=mysql_query($sql))
	{
		if(mysql_affected_rows()>0)
			echo "<h".$headlinesize.">".$headline."</h".$headlinesize.">";
		echo "<div class=\"row\">";
			echo "<div class=\"col-lg-12\">";
			
				echo '<ul class="list-group">';
					while($f=mysql_fetch_array($ff))
					{
						echo '<li class="list-group-item">';
							feedback_display_specific_headline($f['id'], NULL, FALSE, $display_user); //, $parent);
						echo '</li>';
					}
				echo '</ul>';
			echo "</div>";
		echo "</div>";
	}
}

function feedback_display_list_not_implemented($nr, $headline, $headlinesize)
{
	$sql="SELECT id
	FROM ".PREFIX."feedback 
	WHERE is_spam<1
	AND not_implemented IS NOT NULL
	AND merged_with IS NULL
	ORDER BY ".ORDER_STR."
	LIMIT ".sql_safe($nr).";";
	feedback_display_headline_list($sql, $headline, $headlinesize);
}

function feedback_list_user_feedback($user_id, $headline, $headlinesize)
{
	$sql="SELECT id, ".REL_STR." as rel FROM ".PREFIX."feedback 
	WHERE user=".sql_safe($user_id)." ORDER BY ".ORDER_STR.";";
	feedback_display_headline_list($sql, $headline, $headlinesize, FALSE);
}

function feedback_display_merge_form($id, $div_id="", $before_text="", $after_text="")
{
	// echo "<p>feedback_display_merge_form</p>";
	$m_id=feedback_is_merged($id);
	
	if(isset($_SESSION[PREFIX.'user_id']) && isset($_SESSION[PREFIX."inloggad"]) && $_SESSION[PREFIX."inloggad"]>=3)
	{
		echo $before_text;
		if($div_id=="")
			$div_id="feedback_merge_form_".$id;
		echo "<div id=\"".$div_id."\">";
		echo "<form>";
			// Kolla om feedbacken är mergad
			if($m_id!==NULL)
			{
				echo sprintf(_("Merged with %s"),feedback_get_link($m_id));
				echo "<button id=\"feedback_merge_button_".$id."\" onclick=\"feedback_operation('unmerge',".$id.", '".$div_id."'); return false;\">"._("Detach")."</button>";
			}
			else
			{
				// Annars, skriv ut formulär för att merga
				echo "
					"._("Merge with").": ".feedback_get_droplist($id, "feedback_merge_droplist_".$id);
					echo "<button id=\"feedback_merge_button_".$id."\" onclick=\"feedback_operation('merge',".$id.", '".$div_id."', 'feedback_merge_droplist_".$id."'); return false;\">"._("Merge")."</button>
				";
			}
		echo "</form>";
		echo "</div>";
		echo $after_text;
	}
	else if($m_id!==NULL)
	{
		echo $before_text;
		echo "<div id=\"".$div_id."\">";
		echo "Merged with ".feedback_get_link($m_id);
		echo "</div>";
		echo $after_text;
	}
}

function feedback_is_merged($id)
{
	$sql="SELECT merged_with FROM ".PREFIX."feedback WHERE id=".sql_safe($id).";";
	if($ff=mysql_query($sql))
	{
		if($f=mysql_fetch_array($ff))
		{
			return $f['merged_with'];
		}
	}
	return NULL;
}

function feedback_get_attached_feedbacks($id)
{
	$sql="SELECT id,
	".REL_STR."	as rel
	FROM ".PREFIX."feedback 
	WHERE merged_with=".sql_safe($id)."
	ORDER BY ".ORDER_STR.";";
	// echo $sql;
	if($ff=mysql_query($sql))
	{
		if(mysql_affected_rows()>0)
		{
			while($f=mysql_fetch_array($ff))
			{
				$r[]=$f['id'];
			}
			return $r;
		}
	}
	return NULL;
}

function feedback_get_link($id)
{
	$title=feedback_get_title($id);
	if($title==NULL)
		$str="Feedback #$id";
	else
		$str=$title;
	return "<a href=\"".feedback_get_url($id)."\">$str</a>";
}

function feedback_get_title($id)
{
	$sql="SELECT subject, text FROM ".PREFIX."feedback WHERE id=".sql_safe($id).";";
	// echo "<br />DEBUG1753 $sql";
	if($ff=mysql_query($sql))
	{
		if($f=mysql_fetch_array($ff))
		{
			if($f['subject']!=NULL)
				$str=$f['subject'];
			else
				$str=substr($f['text'], 0, 128);
			// echo "STR: $str";
			return $str;
		}
	}
	return NULL;
}
function feedback_get_url($id)
{
	return SITE_URL."?p=feedback&amp;id=".sql_safe($id);
}

function feedback_get_droplist($exclude_id, $droplist_id)
{
	if($rel=feedback_get_list_relevant(0, 500))
	{
		$r_str="<select id=\"".$droplist_id."\">";
		while($r=mysql_fetch_array($rel))
		{
			if($r['id']!=$exclude_id)
			{
				if($r['subject']!=NULL)
					$str=$r['subject'];
				else
					$str=substr($r['text'], 0, 64);
				$r_str.="<option value=\"".$r['id']."\">".$str."</option>";
			}
		}
		$r_str.="</select>";
		return $r_str;
	}
	
	return NULL;
}

function feedback_set_accepted($id)
{
	//Accept the feedback
	$sql="UPDATE ".PREFIX."feedback SET not_implemented=NULL, accepted='".date("YmdHis")."', resolved=NULL WHERE id=".sql_safe($id).";";
	mysql_query($sql);
	
	//Set parent to accepted
	$sql="SELECT merged_with FROM ".PREFIX."feedback WHERE id=".sql_safe($id).";";
	if($ff=mysql_query($sql))
	{
		if($f=mysql_fetch_array($ff))
		{
			if($f['merged_with']!=NULL)
			{
				feedback_set_accepted($f['merged_with']);
			}
		}
	}
}
function feedback_set_unaccepted($id)
{
	//Unaccept the feedback
	$sql="UPDATE ".PREFIX."feedback SET accepted=NULL WHERE id=".sql_safe($id).";";
	mysql_query($sql);
	
	//Set children that is not done to unaccepted
	$sql="SELECT id FROM  ".PREFIX."feedback WHERE merged_with=".sql_safe($id)." AND resolved IS NULL;";
	// echo $sql;
	if($ff=mysql_query($sql))
	{
		while($f=mysql_fetch_array($ff))
		{
			feedback_set_unaccepted($f['id']);
		}
	}
}

function feedback_set_resolved($id)
{
	//Resolve the feedback
	$sql="UPDATE ".PREFIX."feedback SET not_implemented=NULL, resolved='".date("YmdHis")."' WHERE id=".sql_safe($id).";";
	mysql_query($sql);
	
	//Resolve any children
	//Find children
	$sql="SELECT id FROM  ".PREFIX."feedback WHERE merged_with=".sql_safe($id)." AND not_implemented IS NULL;";
	if($ff=mysql_query($sql))
	{
		while($f=mysql_fetch_array($ff))
		{
			feedback_set_resolved($f['id']);
		}
	}
}

function feedback_set_not_implemented($id)
{
	//Resolve the feedback
	$sql="UPDATE ".PREFIX."feedback SET not_implemented='".date("YmdHis")."', resolved=NULL, accepted=NULL WHERE id=".sql_safe($id).";";
	mysql_query($sql);
	
	//Resolve any children
	//Find children
	$sql="SELECT id, resolved, accepted FROM  ".PREFIX."feedback WHERE merged_with=".sql_safe($id)." AND resolved IS NULL AND accepted IS NULL;";
	if($ff=mysql_query($sql))
	{
		while($f=mysql_fetch_array($ff))
		{
			feedback_set_not_implemented($f['id']);
		}
	}
}

function feedback_set_unresolved($id)
{
	$sql="UPDATE ".PREFIX."feedback SET resolved=NULL WHERE id=".sql_safe($id).";";
	mysql_query($sql);
	
	//Set parent to unresolved
	$sql="SELECT merged_with FROM ".PREFIX."feedback WHERE id=".sql_safe($id).";";
	if($ff=mysql_query($sql))
	{
		if($f=mysql_fetch_array($ff))
		{
			if($f['merged_with']!=NULL)
			{
				feedback_set_unresolved($f['merged_with']);
			}
		}
	}
}
function feedback_display_bottom($feedback_id, $parent_div_id)
{
	echo '<ul class="list-group">';
					feedback_status_show($feedback_id, NULL, NULL, NULL, "feedback_status_".$feedback_id, $parent_div_id, '<li class="list-group-item">','</li>');
					feedback_display_size_buttons($feedback_id, "", '<li class="list-group-item">','</li>');
					feedback_display_merge_form($feedback_id, "", '<li class="list-group-item">','</li>');
				
			
			//Attached
				$attached=feedback_get_attached_feedbacks($feedback_id);
					if($attached)
					{
						echo '<li class="list-group-item">';
						echo "<h3>"._("Attached feedbacks")."</h3>";
							echo '<ul class="list-group">';
								foreach($attached as $a)
								{
									echo '<li class="list-group-item">';
										// echo 'Attached';
										feedback_display_specific_headline($a, $parent_div_id);
									echo '</li>';
								}
							echo '</ul>';
						echo '</li>';
					}
			echo '</ul>';
}

function feedback_display_body($id, $hidden=FALSE)
{
	if($hidden)
		$hide_str="style=\"display: none;\"";
	else
		$hide_str="";
		
	//Shows everything but headline, username and time
	$shown=0;
	$sql="SELECT id, text, user, flattrID, plusones FROM ".PREFIX."feedback WHERE id=".sql_safe($id).";";
	if($dd=mysql_query($sql))
	{
		if($d=mysql_fetch_array($dd))
		{
			echo '<div class="row">';
			echo "<div id=\"feedback_body_".$id."\" ".$hide_str." class=\"feedback_body col-lg-12\">";
				//Text
				$text_body=sql_safe(str_replace("\r\n","<br />",str_replace("\r\n\r\n","</p><p>",$d['text'])));
				echo "<div class=\"col-lg-9 feedback_text\"><p>".$text_body."</p>";
				echo "</div>";
				
				//Side thing with buttons
				echo "<div class=\"col-lg-3\">";
					echo "<div class=\"col-lg-12\" id=\"feedback_".$id."_flattr\">";
						//Eventuellt Flattr-knapp
						// echo "<p>Eventuellt Flattr-knapp</p>";
						if($d['user']!=NULL)
						{
							if(flattr_get_flattr_choice($d['user'],"feedback"))
							{
								flattr_button_show(flattr_get_flattrID($d['user']), SITE_URL."?p=feedback&amp;id=".$id , feedback_get_title($id)." - a feedback post on ".SITE_NAME, $d['text'], 'compact', 'en_GB');
							}
						}
						else if($d['flattrID']!=NULL)
						{
							flattr_button_show($d['flattrID'], SITE_URL."?p=feedback&amp;id=".$d['id'] , feedback_get_title($id)." - a feedback post on ".SITE_NAME, $d['text'], 'compact', 'en_GB');
						}
					echo "</div>";
					echo "<div class=\"col-lg-12\">";
						//Plus-knapp
						echo "<div class=\"plusone\">";
							// echo "<p>Plus-knapp</p>";
							echo "<form method=\"post\">";
							echo "<input type=\"submit\" name=\"feedback_plusone\" value=\"+".($d['plusones']+1)."\">
								<input type=\"hidden\" name=\"id\" value=\"".$d['id']."\">";
							echo "<br />".$d['plusones']." +1's";
							echo "</form>";
						echo "</div>";
					echo "</div>";
					echo "<div class=\"col-lg-12\">";
						//Kolla om det är användarens feedback.
						if($d['user']==NULL || (isset($_SESSION[PREFIX.'user_id']) && strcmp($d['user'],$_SESSION[PREFIX.'user_id'])))
							spam_show_clicker($d['id'], "feedback");
					echo "</div>";
				echo "</div>";
			echo "</div>";
			$shown=1;
			echo "</div>";
		}
	}
	if(!$shown)
		echo "<p class=\"error\">Feedback could not be shown</p>";
}

function feedback_get_author_link($feedback_id)
{
	$user_id=feedback_get_user($feedback_id);
	if($user_id!==NULL)
	{
		return user_get_link($user_id);
	}
	else
	{
		$sql="SELECT nick, url FROM ".PREFIX."feedback WHERE id=".sql_safe($feedback_id).";";
		if($ff=mysql_query($sql))
		{
			if($f=mysql_fetch_array($ff))
			{
				if($f['url']!==NULL)
					return '<a href="'.$f['url'].'">'.$f['nick'].'</a>';
				return $f['nick'];
			}
		}
	}
	return NULL;
}

function feedback_display_author_text($feedback_user_id, $feedback_user_nick, $feedback_user_url, $feedback_id, $feedback_created)
{
	$feedback_link=SITE_URL."/?p=feedback&amp;id=".$feedback_id;
	$feedback_time=date("Y-m-d H:i",strtotime($feedback_created));
	
	$user_link=NULL;

	if($feedback_user_id!=NULL)
	{
		$user_name=user_get_name($feedback_user_id);
		$user_link=SITE_URL."/?p=user&amp;user=".$feedback_user_id;
	}
	else if($feedback_user_nick!=NULL)
	{
		$user_name=$feedback_user_nick;
		$user_link=$feedback_user_url;
	}
	
	if(!isset($user_name))
		echo sprintf(_("Posted at <a href=\"%s\">%s</a>"),$feedback_link,$feedback_time);
	else if($user_link==NULL)
		echo sprintf(_("Posted by %s at <a href=\"%s\">%s</a>"), $user_name,$feedback_link,$feedback_time);
	else
		echo sprintf(_("Posted by <a href=\"%s\">%s</a> at <a href=\"%s\">%s</a>"), $user_link, $user_name,$feedback_link,$feedback_time);
}
?>
