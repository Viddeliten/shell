<?php

define('SPAM_POINTS',0);
define('SPAM_NR_TO_CALC',1500);
define('SPAM_NR_TO_ADMIN',20);
define('SPAM_REMOVE_TIME_SHORT',"1 month");
define('SPAM_REMOVE_TIME_LONG',"3 month");

function spam_receive()
{
	login_check_logged_in_mini();
    
    if(function_exists("spam_custom_receive"))
        spam_custom_receive();
	
	//`spam_id``user``IP``type`
	
	if(isset($_POST['this_is_spam']))
	{
		//KOlla om det är admin som säger
		if(isset($_SESSION[PREFIX.'user_id']) && user_get_admin($_SESSION[PREFIX.'user_id'])>1)
		{
			foreach($_POST['id'] as $s_id)
			{
				$sql="UPDATE ".PREFIX.sql_safe($_POST['type'])." 
					SET is_spam=2 
					WHERE id=".sql_safe($s_id).";";
				
				message_try_mysql($sql,
					"085123", //Error code
					sprintf(_("%s %s marked as spam"), $_POST['type'], $s_id)// success_message
				);
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
					SET is_spam=-2
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
	spam_calculate(SPAM_NR_TO_CALC,"comment");
	spam_calculate(SPAM_NR_TO_CALC,"feedback");
	spam_calculate(SPAM_NR_TO_CALC,"FAQ");
    
    if(function_exists("spam_custom_calculate"))
        spam_custom_calculate(SPAM_NR_TO_CALC);
	
	spam_remove_old("comment", SPAM_REMOVE_TIME_SHORT, 2);
	spam_remove_old("feedback", SPAM_REMOVE_TIME_SHORT, 2);
	spam_remove_old("FAQ", SPAM_REMOVE_TIME_SHORT, 2);
	spam_remove_old("comment", SPAM_REMOVE_TIME_LONG, 1);
	spam_remove_old("feedback", SPAM_REMOVE_TIME_LONG, 1);
	spam_remove_old("FAQ", SPAM_REMOVE_TIME_LONG, 1);
	
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
			echo "<p><input type=\"checkbox\" name=\"id[]\" value=\"".$c['id']."\"> <a href=\"".spam_get_link($c['id'], "comment")."\">[".$c['spam_score']."]</a>:  ".$c['comment']." <a href=\"".comment_get_link($c['id'])."\">[...]</a></p>";
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

}

function spam_calculate($nr, $type, $specific_id=NULL, $output=0)
{
	//Räkna poäng för kommentarer
	
	//$nr med högst spam_score
	
	//Kommentarer
	if($specific_id!=NULL && !strcmp($type, "comment"))
		$sql="SELECT id, spam_score, is_spam, ".sql_safe($type)." as text, user, IP FROM ".PREFIX.sql_safe($type)." WHERE id=".sql_safe($specific_id).";";
	else if($specific_id!=NULL)
		$sql="SELECT id, spam_score, is_spam, text, user, IP FROM ".PREFIX.sql_safe($type)." WHERE id=".sql_safe($specific_id).";";
	else if(!strcmp($type, "comment"))
		$sql="SELECT id, user, IP, ".sql_safe($type)." as text FROM ".PREFIX.sql_safe($type)." WHERE is_spam=0 
            OR is_spam=1 OR is_spam=-1 
            ORDER BY IF(spam_score IS NULL, 1, 0) DESC, spam_score ASC LIMIT 0,".sql_safe($nr).";";
	else
		$sql="SELECT id, user, IP, text FROM ".PREFIX.sql_safe($type)." WHERE 
            is_spam=0 
            OR is_spam=1 OR is_spam=-1 
            ORDER BY  IF(spam_score IS NULL, 1, 0) DESC, spam_score ASC LIMIT 0,".sql_safe($nr).";";
	// echo "<br />DEBUG0904: $sql";
	if($cc=mysql_query($sql))
	{
        message_print_message(sprintf("Checking %s %s items for spam...", mysql_affected_rows(), $type));
        
		while($c=mysql_fetch_array($cc))
		{
			if($output)
				echo "<p><strong>\"".$c['text']."\"</strong></p>";
			//räkna ut ny score
			//räkna hur många människor som tycker det är spam
			$sql="SELECT count(id) as nr FROM ".PREFIX."spam WHERE spam_id=".$c['id']." AND type='".sql_safe($type)."';";
			$spam_clicks=0;
			if($ss=mysql_query($sql))
				if($s=mysql_fetch_array($ss))
					$spam_clicks=$s['nr'];
			if($output)
				echo "<p>$spam_clicks users consider this as spam</p>";
				
			//Kolla hur många andra från samma användare eller IP som är klassade som spam redan
			if($c['user']!=NULL)
				$sql="SELECT SUM(is_spam) as nr FROM ".PREFIX.sql_safe($type)." WHERE user='".$c['user']."' AND id!=".$c['id'].";";
			else
				$sql="SELECT SUM(is_spam) as nr FROM ".PREFIX.sql_safe($type)." WHERE IP='".$c['IP']."' AND id!=".$c['id'].";";
			$previous_spam=0;
			if($ss=mysql_query($sql))
				if($s=mysql_fetch_array($ss))
					$previous_spam=$s['nr'];
			
			if($output)
			{
				if($c['user']!=NULL)
					echo "<p>$previous_spam other spam from this user ('".user_get_link($c['user']).")</p>";
				else
					echo "<p>$previous_spam other spam from this IP ('".$c['IP']."')</p>";
			}
			
			//Kolla om det finns länkar eller dumma ord
			$words = 0;  
			$text = strtolower($c['text']); // lowercase it to speed up the loop
			$myDict = array("http","<",">","://","penis","pill","sale","cheap","viagra","cialis", "buy", "tramadol", "kamagra", "xanax", "prescription", "hydroxy", "chloroquin", "corona", "virus", "pandemic"); 
			foreach($myDict as $word)
			{
				$count = substr_count($text, $word);
				$words += $count;
			}
			if($output)
				echo "<p>$words bad words</p>";
			
			$points=$spam_clicks+$previous_spam*0.1+$words;
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

function spam_remove_old($type, $time_str, $is_spam)
{
	if($type=="comment")
		$created="added";
	else if($type=="feedback")
		$created="created";
	else if($type=="FAQ")
		$created="created";

	$sql="DELETE FROM ".PREFIX.sql_safe($type)." 
	WHERE is_spam>=".sql_safe($is_spam)." 
	AND $created<'".date("YmdHis", strtotime("- ".$time_str))."';";
	// echo "<br />DEBUG2258 ".$sql;
	mysql_query($sql);
    $nr=mysql_affected_rows();
    if($nr>0)
        message_print_message(sprintf(_("Removed %s %s messages marked as spam (%s)."), $nr, $type, $is_spam));
}

function spam_get_link($id, $type)
{
	return SITE_URL."?p=admin&amp;s=individual_spam_score&amp;type=".$type."&amp;id=".$id;
}

?>