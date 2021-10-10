<?php

define('SPAM_POINTS',0);
define('SPAM_NR_TO_CALC',1500);
define('SPAM_NR_TO_ADMIN',400);
define('SPAM_REMOVE_TIME_SHORT',"1 week");
define('SPAM_REMOVE_TIME_SHORTER',"1 day");
define('SPAM_REMOVE_TIME_LONG',"3 month");
define('SPAM_REMOVE_TIME_LONGER',"6 month");

function spam_receive()
{
	login_check_logged_in_mini();
    
    if(function_exists("spam_custom_receive"))
        spam_custom_receive();
	
	//`spam_id``user``IP``type`
	
	if(isset($_POST['this_is_spam']))
	{
        $marked_as_spam=array();
		//KOlla om det är admin som säger
		if(isset($_SESSION[PREFIX.'user_id']) && user_get_admin($_SESSION[PREFIX.'user_id'])>1)
		{
			foreach($_POST['id'] as $s_id)
			{
				$sql="UPDATE ".PREFIX.sql_safe($_POST['type'])." 
					SET is_spam=2 
					WHERE id=".sql_safe($s_id).";";
				
				if(message_try_mysql($sql,
					"085123", //Error code
					NULL //sprintf(_("%s %s marked as spam"), $_POST['type'], $s_id)// success_message
				))
                {
                    $marked_as_spam[]=$s_id;
                }
			}
            if(!empty($marked_as_spam))
            {
                message_add_success_message(sprintf(_("%s %ss marked as spam: <br />%s"), count($marked_as_spam), $_POST['type'], implode(", ",$marked_as_spam)));
            }
		}
		else
		{
			$sql="INSERT INTO ".PREFIX."spam 
				SET 
					type='".sql_safe($_POST['type'])."', 
					spam_id=".sql_safe($_POST['id']).", ";
			//Om man är inloggad
			if(isset($_SESSION[PREFIX.'user_id']))
				$sql.="user=".sql_safe($_SESSION[PREFIX.'user_id']).";";
			//Annars, kolla mot IP
			else
				$sql.="IP='".sql_safe($_SERVER['REMOTE_ADDR'])."';";
			
			message_try_mysql($sql,
				"084842", //Error code
				_("Thank you for helping us keep the site spam-free!") // success_message
			);
		}
	}
	
	if(isset($_POST['this_is_not_spam']))
	{
		//KOlla om det är admin som säger
		if(isset($_SESSION[PREFIX.'user_id']) && user_get_admin($_SESSION[PREFIX.'user_id'])>1)
		{
			foreach($_POST['id'] as $s_id)
			{
			
				$sql="UPDATE ".PREFIX.sql_safe($_POST['type'])."
					SET is_spam=-2, spam_score=0
					WHERE id=".sql_safe($s_id).";";
				message_try_mysql($sql,
					"084258", //Error code
					sprintf(_("%s %s marked as not spam."), sql_safe($_POST['type']), $s_id) // success_message
				);
			}
		}
		else
		{
			$sql="DELETE FROM ".PREFIX."spam 
				WHERE type='".sql_safe($_POST['type'])."' 
				AND spam_id=".sql_safe($_POST['id'])." 
				AND ";
			//Om man är inloggad
			if(isset($_SESSION[PREFIX.'user_id']))
				$sql.="user=".sql_safe($_SESSION[PREFIX.'user_id']).";";
			//Annars, kolla mot IP
			else
				$sql.="IP='".sql_safe($_SERVER['REMOTE_ADDR'])."';";
			
			message_try_mysql($sql,
				"084675", //Error code
				_("Thank you for helping us keep the site spam-free!") // success_message
			);
		}
	}
}

function spam_show_clicker($id, $type)
{
	//KOlla om användaren redan rapporterat
	$current_reported=0;
	//Om man är inloggad
	if(isset($_SESSION[PREFIX.'user_id']) && user_get_admin($_SESSION[PREFIX.'user_id'])<2)
		$sql="SELECT id FROM ".PREFIX."spam WHERE type='".sql_safe($type)."' AND spam_id=".sql_safe($id)." AND user=".sql_safe($_SESSION[PREFIX.'user_id']).";";
	//Om man är admin
	else if(isset($_SESSION[PREFIX.'user_id']) && user_get_admin($_SESSION[PREFIX.'user_id'])>1)
	{
		$sql="SELECT COUNT(id) as nr FROM ".PREFIX."spam WHERE type='".sql_safe($type)."' AND spam_id=".sql_safe($id).";";
		// echo "<br />DEBUG0949: $sql";
		if($ss=mysql_query($sql))
		{
			if($s=mysql_fetch_array($ss))
			{
				$current_reported_admin=$s['nr'];
			}
		}
	}
	//Annars, kolla mot IP
	else
		$sql="SELECT id FROM ".PREFIX."spam WHERE type='".sql_safe($type)."' AND spam_id=".sql_safe($id)." AND IP='".sql_safe($_SERVER['REMOTE_ADDR'])."';";
	
	// echo "<br />DEBUG1717: $sql";
	if($ss=mysql_query($sql))
	{
		$current_reported=mysql_affected_rows();
	}
	
	echo "<form method=\"post\">
	<input type=\"hidden\" name=\"type\" value=\"$type\">";
	if(isset($_SESSION[PREFIX.'user_id']) && user_get_admin($_SESSION[PREFIX.'user_id'])>1)
	{
		echo "<input type=\"hidden\" name=\"id[]\" value=\"$id\">";
		if(isset($current_reported_admin) && $current_reported_admin>0) //Den här har markerats som spam av andra
			echo "<input type=\"submit\" class=\"spambutton red\" name=\"this_is_spam\" value=\"Mark as spam ($current_reported_admin)\">";
		else
			echo "<input type=\"submit\" class=\"spambutton\" name=\"this_is_spam\" value=\"Mark as spam\">";
	}
	else
	{	echo "<input type=\"hidden\" name=\"id\" value=\"$id\">";
		if($current_reported<1)
			echo "<input type=\"submit\" class=\"spambutton\" name=\"this_is_spam\" value=\"Report as spam\">";
		else
			echo "<input type=\"submit\" class=\"spambutton\" name=\"this_is_not_spam\" value=\"Reported as spam (click to unreport)\">";
	}
	echo "</form>";
}

function spam_admin_list($nr=SPAM_NR_TO_ADMIN)
{
	if(login_check_logged_in_mini()<=1)
		return NULL;
	
	spam_calculate(SPAM_NR_TO_CALC,"comment");
	spam_calculate(SPAM_NR_TO_CALC,"feedback");
    
    if(function_exists("spam_custom_calculate"))
        spam_custom_calculate(SPAM_NR_TO_CALC);
	
	// ONLY remove old if admin wants us to!!!
	if(isset($_REQUEST["spam_remove_old_by_admin"]))
	{
		spam_remove_old("comment", SPAM_REMOVE_TIME_SHORT, 2, 200);
		spam_remove_old("feedback", SPAM_REMOVE_TIME_SHORT, 2, 200);
		spam_remove_old("comment", SPAM_REMOVE_TIME_LONG, 1, 50);
		spam_remove_old("feedback", SPAM_REMOVE_TIME_LONG, 1, 50);
		spam_remove_old("feedback", SPAM_REMOVE_TIME_LONGER, 1, 20);
		spam_remove_old("comment", SPAM_REMOVE_TIME_LONGER, 1, 20);

		spam_remove_old("comment", SPAM_REMOVE_TIME_SHORTER, 3, 400); // They won't have 3, but this will make the shorter time span only care about the 400
		spam_remove_old("feedback", SPAM_REMOVE_TIME_SHORTER, 3, 400);
	}
	?>
	<form method="post">
		<input type="submit" name="spam_remove_old_by_admin" value="Remove old, aggressively" onclick="return confirm('<?= sprintf(_("Are you sure you want to do this?      Everything older than %s with more than 50 spam score will be removed, among other things."), SPAM_REMOVE_TIME_LONG) ?>')">
	</form>
	<?php
	
	//Visa en lista på kommentarer med lägst poäng
	echo "<h2>Comments</h2>";
	$sql="SELECT id, spam_score, is_spam, comment FROM ".PREFIX."comment WHERE is_spam>-02 AND is_spam<2 ORDER BY IFNULL(spam_score,0) ASC, added ASC LIMIT 0,".sql_safe($nr).";";
	// echo "<br />DEBUG1018: $sql";
	if($cc=mysql_query($sql))
	{
		echo "<form method=\"post\">";
		echo "<input type=\"hidden\" name=\"type\" value=\"comment\">";
		while($c=mysql_fetch_array($cc))
		{

			echo "<p><input type=\"checkbox\" name=\"id[]\" value=\"".$c['id']."\"> <a href=\"".spam_get_link($c['id'], "comment")."\">[".$c['spam_score']."]</a>:  ".
						$c['comment'].
						" <a href=\"".comment_get_link_url($c['id'], NULL, $notext)."\">[...]</a></p>";
		}
		echo "<input type=\"button\" value=\"Markera alla\" onclick=\"CheckAll(this.form);\"><br />";

		echo "<input type=\"submit\" name=\"this_is_spam\" value=\"Mark as spam\">";
		echo "<input type=\"submit\" name=\"this_is_not_spam\" value=\"Mark as not spam\">";
		echo "</form>";
	}
	
	//Visa en lista på feedback med lägst poäng
	echo "<h2>feedback</h2>";
	$sql="SELECT id, spam_score, is_spam, subject, text FROM ".PREFIX."feedback WHERE is_spam>-02 AND is_spam<2 ORDER BY IFNULL(spam_score,0) ASC, created ASC LIMIT 0,".sql_safe($nr).";";
	// echo "<br />DEBUG1018: $sql";
	if($cc=mysql_query($sql))
	{
		echo "<form method=\"post\">";
		echo "<input type=\"hidden\" name=\"type\" value=\"feedback\">";
		while($c=mysql_fetch_array($cc))
		{
			echo "<p><input type=\"checkbox\" name=\"id[]\" value=\"".$c['id']."\">
			<a href=\"".spam_get_link($c['id'], "feedback")."\">[".$c['spam_score']."]</a>:  <strong>".$c['subject']."</strong> - ".$c['text']." 
			".feedback_get_link($c['id'],"[...]")."</p>";
		}
		echo "<input type=\"button\" value=\"Markera alla\" onclick=\"CheckAll(this.form);\"><br />";

		echo "<input type=\"submit\" name=\"this_is_spam\" value=\"Mark as spam\">";
		echo "<input type=\"submit\" name=\"this_is_not_spam\" value=\"Mark as not spam\">";
		echo "</form>";
	}
	//Visa en lista på FAQ med lägst poäng
	/*
	echo "<h2>Help!</h2>";
	$sql="SELECT id, spam_score, is_spam, subject, text FROM ".PREFIX."FAQ WHERE is_spam>-02 AND is_spam<2 ORDER BY IFNULL(spam_score,0) ASC, created ASC LIMIT 0,".sql_safe($nr).";";
	// echo "<br />DEBUG1018: $sql";
	if($cc=mysql_query($sql))
	{
		echo "<form method=\"post\">";
		echo "<input type=\"hidden\" name=\"type\" value=\"FAQ\">";
		while($c=mysql_fetch_array($cc))
		{
			echo "<p><input type=\"checkbox\" name=\"id[]\" value=\"".$c['id']."\"> <a href=\"".spam_get_link($c['id'],"FAQ")."\">[".$c['spam_score']."]</a>:  <strong>".$c['subject']."</strong> - ".$c['text']." 
			<a href=\"".SITE_URL."?p=FAQ&amp;id=".$c['id']."\">[...]</a></p>";
		}
		echo "<input type=\"button\" value=\"Markera alla\" onclick=\"CheckAll(this.form);\"><br />";

		echo "<input type=\"submit\" name=\"this_is_spam\" value=\"Mark as spam\">";
		echo "<input type=\"submit\" name=\"this_is_not_spam\" value=\"Mark as not spam\">";
		echo "</form>";
	}
	*/

}

function spam_calculate($nr, $type, $specific_id=NULL, $output=0)
{
	$subject = " '' as subject ";
	if(!strcmp($type, "feedback"))
	{
		$subject = " subject ";
	}
	
	//Räkna poäng för kommentarer
	
	//$nr med högst spam_score
	
	//Kommentarer
	if($specific_id!=NULL && !strcmp($type, "comment"))
		$sql="SELECT id, spam_score, is_spam, ".sql_safe($type)." as text, user, nick, url, IP, ".$subject.", comment_type, comment_on, added as insert_time FROM ".PREFIX.sql_safe($type)." WHERE id=".sql_safe($specific_id).";";
	else if($specific_id!=NULL && !strcmp($type, "comment"))
		$sql="SELECT id, spam_score, is_spam, text, user, nick, url, IP, ".$subject.", added as insert_time FROM ".PREFIX.sql_safe($type)." WHERE id=".sql_safe($specific_id).";";
	else if($specific_id!=NULL)
		$sql="SELECT id, spam_score, is_spam, text, user, nick, url, IP, ".$subject.", created as insert_time FROM ".PREFIX.sql_safe($type)." WHERE id=".sql_safe($specific_id).";";
	else if(!strcmp($type, "comment"))
		$sql="SELECT id, user, nick, url, is_spam,  IP, ".sql_safe($type)." as text, ".$subject.", comment_type, comment_on, added as insert_time FROM ".PREFIX.sql_safe($type)." WHERE is_spam=0 
            OR is_spam=1 OR is_spam=-1 
            ORDER BY IF(spam_score IS NULL, 1, 0) DESC, spam_score ASC LIMIT 0,".sql_safe($nr).";";
	else
		$sql="SELECT id, user, nick, url, IP, is_spam, text, ".$subject.", created as insert_time FROM ".PREFIX.sql_safe($type)." WHERE 
            is_spam=0 
            OR is_spam=1 OR is_spam=-1 
            ORDER BY  IF(spam_score IS NULL, 1, 0) DESC, spam_score ASC LIMIT 0,".sql_safe($nr).";";
	// echo "<br />DEBUG0904: $sql";
	if($cc=mysql_query($sql))
	{
        if($output)
            message_print_message(sprintf("Checking %s %s items for spam...", mysql_affected_rows(), $type));
        
		while($c=mysql_fetch_assoc($cc))
		{
			if($output)
				echo html_tag("div", prestr($c),"well");
			//räkna ut ny score
			//räkna hur många människor som tycker det är spam
			$sql="SELECT count(id) as nr FROM ".PREFIX."spam WHERE spam_id=".$c['id']." AND type='".sql_safe($type)."';";
			$spam_clicks=0;
			if($ss=mysql_query($sql))
				if($s=mysql_fetch_array($ss))
					$spam_clicks=$s['nr'];
			if($output)
				echo "<p>$spam_clicks users consider this as spam</p>";
				
			//Kolla hur många andra från samma användare eller IP som är klassade som spam redan och hur många meddelanden användaren skickat i samband med detta.
			$previous_spam=0;
			
			$types=array("comment", "feedback");
			if(!in_array($type,$types))
				$types[]=$type;
			
			foreach($types as $t)
			{
				if($c['user']!=NULL)
					$sql="SELECT SUM(is_spam) as nr FROM ".PREFIX.sql_safe($t)." WHERE user='".$c['user']."' AND id!=".$c['id'].";";
				else
					$sql="SELECT SUM(is_spam) as nr FROM ".PREFIX.sql_safe($t)." WHERE IP='".$c['IP']."' AND id!=".$c['id'].";";

				if($ss=mysql_query($sql))
				{
					if($ss && $s=$ss->fetch_assoc()) //  && $s != NULL && $s != FALSE && isset($s['nr']))
					{
						$previous_spam += ($s['nr'] != NULL ? $s['nr'] : 0);
					}
				}
            }
			if($output)
			{
				if($c['user']!=NULL)
					echo "<p>".$previous_spam." other spam from this user ('".user_get_link($c['user']).")</p>";
				else
					echo "<p>".$previous_spam." other spam from this IP ('".$c['IP']."')</p>";
			}
            
            // Check how many previous messages the user has sent
            $previous_messages=spam_previous_messages($c);            
            
            $previous_messages_spam_points=floor($previous_messages/5);
            
            //Skriv ut resultat
			if($output)
			{
				if($c['user']!=NULL)
					echo "<p>".$previous_messages." other messages from this user ('".user_get_link($c['user']).") during the same 2 hour period. +".$previous_messages_spam_points." spam points.</p>";
				else
					echo "<p>".$previous_messages." other messages from this IP ('".$c['IP']."') during the same 2 hour period. +".$previous_messages_spam_points." spam points.</p>";
			}
                
			
			
			//Kolla om det finns länkar eller dumma ord
			$words = 0;  
			$text = strtolower($c['text']).strtolower($c['subject']).strtolower($c['nick']); // lowercase it to speed up the loop, also check both text and subject
			$myDict = array("http","<",">","://","penis","pill","drug","abuse","cymbalta","xevil","blog","topic","adult","! bookmarked. ","hottest information","order","casino","impotence","sale","cheap",
                "viagra",
                "cialis",
                "dapoxetine",
                "buy", "tramadol", "kamagra", "xanax", 
                "prescription", "hydroxy", "chloroquin", "corona", "virus", "pandemic","levitra",
				"free",
				" mg",
				"purchase",
				"generic",
				"doctor",
				"dating",
				"online",
				"tadalafil",
				"pharmac",
				"shop",
				"tadalafil",
				"unprescribed",
				"tablet",
				"blogroll",
				"all the internet people",
				"thank you for post",
				"finally something about",
				"his site provides quality",
				"his webpage presents helpful fact",
				"here's a lot of folks",
				"what a material!",
				"was browsing on google for something else",
				"kinda off topic",
				"t's difficult to find high quality writing like yours",
				"this particular topic",
				"serial key",
				"came here by searching",
				"share your blog",
				"knowledgeable people",
				"erectile","breast","vacuum",
				"article",
				"your articles",
				"very cool website",
				"your rss",
				"guos",
				".com",
				"aol",
				"카","지","노","바","라","사","이","트","더","킹",
				"п","л","д","н","в","м","я","г",
				" | ","| comment |","| feedback |",
				"i just wrote an extremely long comment but",
				"we're a group of volunteers and",
				"great site, stick with it!",
				"this is really interesting",
				"this was an exceptionally nice post",
				"it's nearly impossible to find educated people on this subject",
				"very nice write-up. i certainly love this site. keep writing!",
				"which is valuable in support of my knowledge",
				"on the internet for additional information about the issue and found",
				"after i initially commented",
				"seek advice from my site",
				"impressive piece of writing"
				); 
			$bad_words_found=array();
			foreach($myDict as $word)
			{
				$count = substr_count($text, $word);
				$words += $count;
				if($count > 0)
					$bad_words_found[]=$word;
			}
			if($output)
				echo "<p>$words bad words (".implode(", ",$bad_words_found).")</p>";
			
			// Om subject börjar med "SITE_NAME |", lägg på 10 "fula ord"
			$dont_start = SITE_NAME." |";
			if(!strcmp(substr($c['subject'], 0, strlen($dont_start)), $dont_start))
			{
				$words += 10;
				if($output)
					echo "<p>Starts badly</p>";
			}
			
			$points=$spam_clicks+$previous_spam*0.1+$previous_messages_spam_points+$words;
			if($output)
				echo "<p>Points: $points";
			
			//Bestäm om det är spam eller inte
			if($points>SPAM_POINTS)
				$is_spam=1;
			else
				$is_spam=-1;
			
			if($output && $is_spam==-1)
				echo "<p>This might <strong>not</strong> be spam<p>";
			else if($output)
				echo "<p>This might be spam</p>";
				
			if($output && $c['is_spam']<1)
				echo "<p>This is considered <strong>not</strong> to be spam</p>";
			else if($output && $c['is_spam']>0 && $c['is_spam']<2)
				echo "<p>This is considered as possible spam</p>";
			else if($output)
				echo "<p>This is considered to be spam</p>";
			if($output)
				echo "(".$c['is_spam'].")";

				
			if($output && $specific_id!=NULL)
			{
				//Knapp för att ändra
				echo "<form method=\"post\">";
				echo "<input type=\"hidden\" name=\"type\" value=\"$type\">";
				echo "<input type=\"hidden\" name=\"id[]\" value=\"".$specific_id."\">";
				if($output && $c['is_spam']!=2)
					echo "<input type=\"submit\" name=\"this_is_spam\" value=\"Mark as spam\">";
				if($output && $c['is_spam']!=-2)
					echo "<input type=\"submit\" name=\"this_is_not_spam\" value=\"Mark as not spam\">";
				echo "</form>";
			}

            //mata in i databas
            $sql="UPDATE ".PREFIX.sql_safe($type)." SET spam_score=".ceil($points).", is_spam=$is_spam WHERE id=".$c['id'].";";
            mysql_query($sql);
            // echo "<br />DEBUG1850: $sql";
		}
	}	
}

function spam_show_individual_calculation()
{
	echo "<h2>".sql_safe($_GET['type'])."</h2>";

	spam_calculate(0, sql_safe($_GET['type']), sql_safe($_GET['id']), 1);
}

function spam_remove_old($type, $time_str, $is_spam, $spam_score=NULL)
{
	if($type=="comment")
		$created="added";
	else if($type=="feedback")
		$created="created";
	// else if($type=="FAQ")
		// $created="created";

	$sql="DELETE FROM ".PREFIX.sql_safe($type)." 
	WHERE is_spam>=".sql_safe($is_spam)." 
	AND $created<'".date("YmdHis", strtotime("- ".$time_str))."';";
	// echo "<br />DEBUG2258 ".$sql;
	mysql_query($sql);
    $nr=mysql_affected_rows();
    if($nr>0)
        message_print_message(sprintf(_("Removed %s %s messages marked as spam (%s) and created before %s."), $nr, $type, $is_spam, date("Y-m-d", strtotime("- ".$time_str))));

	if($spam_score!==NULL)
	{
		$sql="DELETE FROM ".PREFIX.sql_safe($type)." 
		WHERE spam_score>=".sql_safe($spam_score)."
		AND is_spam>=1		
		AND $created<'".date("YmdHis", strtotime("- ".$time_str))."';";
		// echo "<br />DEBUG2258 ".$sql;
		mysql_query($sql);
		$nr=mysql_affected_rows();
		if($nr>0)
			message_print_message(sprintf(_("Removed %s %s messages with spam score >= %s and created before %s."), $nr, $type, $spam_score, date("Y-m-d", strtotime("- ".$time_str))));
	}
}

function spam_get_link($id, $type)
{
	return SITE_URL."?p=admin&amp;s=individual_spam_score&amp;type=".$type."&amp;id=".$id;
}

function spam_previous_messages($c)
{
     $previous_messages=0;

    // Kolla hur många feedback användaren skickat i samband med detta.
    if($c['user']!=NULL)
        $sql="SELECT COUNT(id) as nr FROM ".PREFIX."feedback WHERE user='".$c['user']."' AND id!=".$c['id']." AND created BETWEEN DATE_SUB('".$c['insert_time']."', INTERVAL 1 HOUR) AND DATE_ADD('".$c['insert_time']."', INTERVAL 1 HOUR);";
    else
        $sql="SELECT COUNT(id) as nr FROM ".PREFIX."feedback WHERE IP='".$c['IP']."' AND id!=".$c['id']." AND created BETWEEN DATE_SUB('".$c['insert_time']."', INTERVAL 1 HOUR) AND DATE_ADD('".$c['insert_time']."', INTERVAL 1 HOUR);";
        
    if($ss=mysql_query($sql))
    {
        if($ss && $s=$ss->fetch_assoc()) //  && $s != NULL && $s != FALSE && isset($s['nr']))
        {
            $previous_messages += ($s['nr'] != NULL ? $s['nr'] : 0);
        }
    }

    // Kolla hur många comments användaren skickat i samband med detta.
    if($c['user']!=NULL)
        $sql="SELECT COUNT(id) as nr FROM ".PREFIX."comment WHERE user='".$c['user']."' AND id!=".$c['id']." AND added BETWEEN DATE_SUB('".$c['insert_time']."', INTERVAL 1 HOUR) AND DATE_ADD('".$c['insert_time']."', INTERVAL 1 HOUR);";
    else
        $sql="SELECT COUNT(id) as nr FROM ".PREFIX."comment WHERE IP='".$c['IP']."' AND id!=".$c['id']." AND added BETWEEN DATE_SUB('".$c['insert_time']."', INTERVAL 1 HOUR) AND DATE_ADD('".$c['insert_time']."', INTERVAL 1 HOUR);";
        
    if($ss=mysql_query($sql))
    {
        if($ss && $s=$ss->fetch_assoc()) //  && $s != NULL && $s != FALSE && isset($s['nr']))
        {
            $previous_messages += ($s['nr'] != NULL ? $s['nr'] : 0);
        }
    }
            
    return $previous_messages;
}

?>