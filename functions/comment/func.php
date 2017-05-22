<?php

require_once(ABS_PATH."/functions/news.php");

function comment_receive()
{
	$inloggad=login_check_logged_in_mini();
	
	if(isset($_POST['addcomment']))
	{
		/********************************************/
		/*				Captcha check				*/
		/********************************************/
		if(login_check_logged_in_mini()<1 && isset($_POST['g-recaptcha-response']))
			$response=json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".ReCaptcha_privatekey."&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']), true);

		if(login_check_logged_in_mini()<1 && (!isset($response) || $response['success'] != true))
		{
			if(isset($response) && !strcmp($response['error-codes'][0],'missing-input-response'))
			{
				//Human was a robot or forgot to check captcha
				add_error(_("Comment could not be posted.<br />Seems you forgot to check captcha. Hit 'back' in your browser and try again!"));
			}
			else
				add_error(_("Feedback could not be posted.<br />You do not appear to be human. Feeling ok?"));
		}
		else
		{
			// Captcha or login passed

			//Check login
			if(login_check_logged_in_mini()>0)
			{
				$user=$_SESSION[PREFIX."user_id"];
			}
			else
			{
				$user='NULL';
			}
			$IP=$_SERVER['REMOTE_ADDR'];
				
			//Lägg till en kommentar
			$sql="INSERT INTO ".PREFIX."comment SET
			comment_type='".sql_safe($_POST['type'])."',
			comment_on=".sql_safe($_POST['id']).",
			user=".$user.",
			comment='".sql_safe($_POST['comment'])."',
			added='".date("YmdHis")."',
			IP='".sql_safe($IP)."';";
			// echo "<br />DEBUG 1225: $sql";
			message_try_mysql($sql,"102472");
			
			$id=mysql_insert_id();
			
			if(isset($_POST['nick']))
			{
				$sql="UPDATE ".PREFIX."comment SET nick='".sql_safe($_POST['nick'])."'
				WHERE id=$id;";
				mysql_query($sql);
			}
			if(isset($_POST['email']))
			{
				$sql="UPDATE ".PREFIX."comment SET email='".sql_safe($_POST['email'])."'
				WHERE id=$id;";
				mysql_query($sql);
			}
			if(isset($_POST['url']))
			{
				$sql="UPDATE ".PREFIX."comment SET url='".sql_safe($_POST['url'])."'
				WHERE id=$id;";
				mysql_query($sql);
			}
			if(isset($_POST['flattrID']))
			{
				$sql="UPDATE ".PREFIX."comment SET flattrID='".sql_safe($_POST['flattrID'])."'
				WHERE id=$id;";
				mysql_query($sql);
			}
		}
	}
	else
		// echo "<br />DEBUG1832: !isset(\$_POST['addcomment']))";
	
	if($inloggad>1)
	{
		if(isset($_POST['deletecomment']))
		{
			$sql="DELETE FROM ".PREFIX."comment WHERE id=".sql_safe($_POST['id']).";";
			// echo "<br />DEBUG 2020: $sql";
			mysql_query($sql);
		}
	}
	else if($inloggad>0)
	{
		if(isset($_POST['deletecomment']))
		{
			if($aa=mysql_query("SELECT user from ".PREFIX."comment WHERE id=".sql_safe($_POST['id']).";"))
			{
				if($a=mysql_fetch_array($aa))
				{
					//Kolla om det är användarens kommentar.
					if(!strcmp($a['user'],$_SESSION[PREFIX."user_id"]))
					{
						//Kolla så att det inte finns några svar
						if($dd=mysql_query("SELECT id from ".PREFIX."comment WHERE comment_on=".sql_safe($_POST['id'])." AND comment_type='comment';"))
						{
							if(mysql_affected_rows()<1)
							{
								$sql="DELETE FROM ".PREFIX."comment WHERE id=".sql_safe($_POST['id']).";";
								// echo "<br />DEBUG 2021: $sql";
								mysql_query($sql);
							}
						}
					}
				}
			}
		}
	}
}

function comment_form_show($id, $type, $beforetext)
{
	?>
	<form method="post">
		<h3><?php echo $beforetext ?></h3>
	<?php
	if(login_check_logged_in_mini()<1)
	{
		//Man kanske vill ange namn, e-post, hemsida och Flattr-id om man inte är inloggad
		echo "<p><label for=\"nick\">Name:</label> <input type=\"text\" name=\"nick\" class=\"form-control\">";
		echo "<br /><label for=\"email\">Email:</label> <input type=\"text\" name=\"email\" class=\"form-control\">";
		echo "<br /><label for=\"url\">Website:</label> <input type=\"text\" name=\"url\" class=\"form-control\">";
		echo "<br /><label for=\"flattrID\">Flattr ID:</label> <input type=\"text\" name=\"flattrID\" class=\"form-control\"></p>";
	}
	?>

		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<input type="hidden" name="type" value="<?php echo $type; ?>">
		<?php echo html_form_textarea($type."_comment_textarea_".$id, _("Comment:"), "comment", ""); ?>
		<?php if(login_check_logged_in_mini()<1)
		{
			?><div class="g-recaptcha" data-sitekey="<?php echo ReCaptcha_publickey; ?>"></div><?php
		} ?>
		<input type="submit" name="addcomment" value="<?php echo _("Send"); ?>" class="form-control">
	</form>
	<?php
}

function comment_show_comments($id, $type)
{
	$nr=0;
	$inloggad=login_check_logged_in_mini();
	
	//Hämta alla kommentarer
	$sql="SELECT * FROM ".PREFIX."comment WHERE comment_on=".sql_safe($id)." AND comment_type='$type' AND is_spam<1;";
	if($cc=@mysql_query($sql))
	{
		while($c=mysql_fetch_array($cc))
		{
			$nr++;
			
			//Kolla om författaren är admin
			$admin=user_get_admin($c['user']);
			
			//skriv ut en anchor-pryl
			echo '<span class="anchor" id="anchor_comment_'.$c['id'].'"></span>';
			
			//Skriv ut kommentar
			if($admin<2)
				echo "<div class=\"comment\" id=\"comment_".$c['id']."\">";
			else
				echo "<div class=\"comment admin_comment\" id=\"comment_".$c['id']."\">";
			
				comment_display_single($c['id']);

			//Visa knapp för borttagning om man är admin
			if($inloggad>1)
			{
				echo "<form id=\"delete_comment_".$c['id']."\" method=\"post\">
						<input type=\"hidden\" name=\"id\" value=\"".$c['id']."\">
						<input type=\"hidden\" name=\"deletecomment\" value=\"".$c['id']."\">
						<input type=\"button\" name=\"deletecomment_button\" onclick=\"confirmation_delete('delete_comment_".$c['id']."', '"._("Do you really want to delete the comment? This cannot be undone.")."')\"  value=\""._("Remove comment")."\">
					</form>";
						// <input type=\"button\" name=\"deletecomment_button\" onclick=\"return confirm('"._("Do you really want to delete the comment? This cannot be undone.")."');\"  value=\""._("Remove comment")."\">
				if($aa=mysql_query("SELECT user from ".PREFIX."comment WHERE id=".sql_safe($c['id']).";"))
				{
					if($a=mysql_fetch_array($aa))
					{
						//Kolla om det är användarens kommentar.
						if(strcmp($a['user'],$_SESSION[PREFIX."user_id"]))
							spam_show_clicker($c['id'], "comment");
					}
				}
			}
			else if($inloggad>=1)
			{
				//Om det är ens egen kommentar och den inte har några svar ska man kunna ta bort den.
				if($aa=mysql_query("SELECT user from ".PREFIX."comment WHERE id=".sql_safe($c['id']).";"))
				{
					if($a=mysql_fetch_array($aa))
					{
						//Kolla om det är användarens kommentar.
						if(!strcmp($a['user'],$_SESSION[PREFIX."user_id"]))
						{
							//Kolla så att det inte finns några svar
							if($dd=mysql_query("SELECT id from ".PREFIX."comment WHERE comment_on=".sql_safe($c['id'])." AND comment_type='comment';"))
							{
								if(mysql_affected_rows()<1)
								{
									echo "<form id=\"delete_comment_".$c['id']."\" method=\"post\">
											<input type=\"hidden\" name=\"id\" value=\"".$c['id']."\">
											<input type=\"hidden\" name=\"deletecomment\" value=\"".$c['id']."\">
											<input type=\"button\" name=\"deletecomment_button\" onclick=\"confirmation_delete('delete_comment_".$c['id']."', '"._("Do you really want to delete the comment? This cannot be undone.")."')\"  value=\""._("Remove comment")."\">
										</form>";
											// <input type=\"button\" name=\"deletecomment_button\" onclick=\"return confirm('"._("Do you really want to delete the comment? This cannot be undone.")."');\"  value=\""._("Remove comment")."\">
								}
							}
						}
						else
							spam_show_clicker($c['id'], "comment");
					}
				}
			}
		
			
			if($inloggad>0)
			{
				//Om man är inloggad ska man kunna svara
				echo "<a class=\"button\" onClick=\"showhide('replyto".$c['id']."');\" href=\"#reply\">"._("Reply")."</a>";
				echo "<div id=\"replyto".$c['id']."\" style=\"display:none\">";
				comment_form_show($c['id'], "comment", "");
				echo "</div>";
			}
			else
			{
				//Om man inte är inloggad ska man kunna svara med captcha

				echo "<a class=\"button\" onClick=\"showhide('replyto".$c['id']."');\" href=\"#reply\">"._("Reply")."</a>";
				echo "<div id=\"replyto".$c['id']."\" style=\"display:none\">";
				comment_form_show($c['id'], "comment", "");
				echo "</div>";
			}
			
			
		
			//Skriv ut svar på denna
			//echo "<br />DEBUG: $nr + comment_show_comments = ";
			$nr+=comment_show_comments($c['id'], "comment");
			//echo "$nr";
			
			echo "</div>";
		}
	}
	
	//echo "<br />DEBUG: return $nr;";
	return $nr;
}

function comments_show_comments_and_replies($id, $type)
{
	if(isset($_GET['comment'])) //we are on a link to a specific comment
		echo "<div id=\"comments".$id."\">";
	else
		echo "<div id=\"comments".$id."\" style=\"display:none\">";
		
		echo "<p><a class=\"comments".$id." commentclicker\" onClick=\"showhide('comments".$id."');showhide('".$type."comments".$id."');\" class=\"commentclicker\" href=\"#comment\">[-"._("Hide comments")."-]</a></p>"; //toggle-pryl! =)
		$nrcomments=comment_show_comments($id, $type);
		comment_form_show($id, $type, _("Add a comment:"));
	echo "</div>";
	echo "<p>";
	if(isset($_GET['comment'])) //we are on a link to a specific comment
		echo "
		<a id=\"".$type."comments".$id."\" style=\"display:none\"";
	else
		echo "
		<a id=\"".$type."comments".$id."\"";
	echo "onClick=\"showhide('comments".$id."');showhide('".$type."comments".$id."');\" class=\"commentclicker\" href=\"#comment\">
				[-"._("Show comments")." ($nrcomments)-]
		</a>
	</p>";
}

function comments_show_latest_short($antal=3, $length=150, $ul_class="commentlist")
{
	$sql="SELECT id, comment_type, user, nick, email, url, flattrID, added, SUBSTRING(`comment`, 1, ".sql_safe( $length).") AS comment FROM ".PREFIX."comment WHERE is_spam<1 ORDER BY added DESC LIMIT 0,".sql_safe($antal).";";
	//echo "<br />DEBUG1323: $sql";
	if($cc=mysql_query($sql)) //Hämta bara de senaste
	{
		if(mysql_affected_rows()<1)
			echo "<p>"._("No resent comments")."</p>";
		echo "<ul class=\"".$ul_class."\">";
		$first=1;
		while($c = mysql_fetch_array($cc))
		{
			
			if($first)
			{
				echo "<li class=\"first\">";
				$first=0;
			}
			else
			{
				echo "<li>";
			}
			
			comment_display_single($c['id'], $length);

			echo "<div class=\"clearer\"></div></li>";
		}
		echo "</ul>";
	}
}

function comment_display_single($comment_id, $max_length=NULL)
{
	$sql="SELECT 
					id,
					comment_type,
					user,
					nick,
					email,
					url,
					flattrID,
					added,";
	if( $max_length !== NULL)
		$sql.="
					SUBSTRING(`comment`, 1, ".sql_safe( $max_length).") AS comment";
	else
		$sql.="
					comment";
	$sql.="
		FROM ".PREFIX."comment 
		WHERE id=".sql_safe($comment_id).";";
	//echo "<br />DEBUG1323: $sql";
	if($cc=mysql_query($sql)) //Hämta bara de senaste
	{
		if($c = mysql_fetch_array($cc))
		{
			$comment_link=comment_get_link($c['id']);
					//Skriv ut info om när kommentaren skrevs och av vem
				echo "<div class=\"comment_head\">";
					if($c['user']!=NULL)
					{
						// Kolla om vi har en avatar
						echo '<a href="'.user_get_link_url($c['user']).'"><img class="left_avatar leftfloat"  src="'.user_get_avatar_path($c['user'], 60).'"></a>';
					}
					else if($c['nick']!=NULL)
					{
						//Kolla om vi har en gravatar
						if($c['url']!=NULL)
							echo '<a href="'.$c['url'].'">';
						if($c['email']!=NULL)
						{
							echo "<img class=\"left_avatar leftfloat\"  src=\"https://www.gravatar.com/avatar/".md5( strtolower( trim( $c['email'] ) ) )."?s=60\" />" ;
						}
						if($c['url']!=NULL)
							echo '</a>';
					}
					echo "<div class=\"date\">";

					comment_display_author_text($c['id']);
					
					//Eventuell Flattr-knapp
					if($c['user']!=NULL && flattr_get_flattr_choice($c['user'], "comment"))
						$flattrID=flattr_get_flattrID($c['user']);
					else if($c['flattrID']!=NULL)
						$flattrID=$c['flattrID'];
					else
						$flattrID=NULL;
						
					if($flattrID)
					{
						echo "<br />";
						flattr_button_show($flattrID, $comment_link , "Comment ".$c['id']." - a ".$c['comment_type']." comment on ".SITE_URL, $c['comment'], 'compact', 'en_GB');
					}
					echo "</div>";
				echo '<div class="clearfix"></div></div>';
				
				echo "<div class=\"comment_body\">";
					//Skriv ut kommentar
					$c_text=str_replace("\n","<br />",$c['comment']);
					echo "<p class=\"comment_text\">".$c_text;
					if( $max_length !== NULL)
						echo "<a href=\"$comment_link\">[...]</a>";
					echo "</p>";			
				echo "</div>";
		}
	}
}

function comment_get_link($id, $link_id=NULL)
{
	if($link_id===NULL)
		$link_id=$id;
	$sql="SELECT id, comment_type, comment_on FROM ".PREFIX."comment WHERE id=".sql_safe($id).";";
	// echo "<br />DEBUG2055: $sql";
	if($cc=mysql_query($sql))
	{
		if($c=mysql_fetch_array($cc))
		{
			// echo "<br />DEBUG2056: ".$c['comment_type'];
			if(!strcmp($c['comment_type'],"comment"))
			{
				return comment_get_link($c['comment_on'], $id);
			}
			else
			{
				if(!strcmp($c['comment_type'],"feedback"))
					return SITE_URL."?comment&amp;p=feedback&amp;id=".$c['comment_on']."#anchor_comment_".$link_id;
				else if(!strcmp($c['comment_type'],"user"))
					// return SITE_URL."?comment&amp;p=user&amp;user=".$c['comment_on']."#anchor_comment_".$link_id;
					return user_get_link_url($c['comment_on'])."&amp;comment#anchor_comment_".$link_id;
				else if(!strcmp($c['comment_type'],"news"))
					return news_get_link_url($c['comment_on'])."#anchor_comment_".$link_id;
				else if(!strcmp($c['comment_type'],"stable"))
					return stable_get_link_url($c['comment_on'])."&amp;comment#anchor_comment_".$link_id;
				else
					return SITE_URL."?p=".$c['comment_type']."&amp;".$c['comment_type']."=".$c['comment_on']."&amp;comment#anchor_comment_".$link_id;
			}
		}
	}

	return NULL;
}

function comment_get_main($id)
{
	$sql="SELECT id, comment_type, comment_on FROM ".PREFIX."comment WHERE id=".sql_safe($id).";";
	if($cc=mysql_query($sql))
	{
		if($c=mysql_fetch_array($cc))
		{
			if(!strcmp($c['comment_type'],"comment"))
			{
				return comment_get_main($c['comment_on']);
			}
			else
			{
				return $c;
			}
		}
	}
}

function comment_display_author_text($comment_id)
{
	$sql="SELECT user, nick, email, url, added FROM ".PREFIX."comment WHERE id=".sql_safe($comment_id).";";
	if($cc=mysql_query($sql))
	{
		if($c=mysql_fetch_assoc($cc))
		{
			$comment_time=date("Y-m-d H:i",strtotime($c['added']));
			$comment_link=comment_get_link($comment_id);
			
			$user_link=NULL;

			if($c['user']!==NULL)
			{
				$user_name=user_get_name($c['user']);
				$user_link=user_get_link($c['user']);
			}
			else if($c['nick']!==NULL)
			{
				$user_name=$c['nick'];
				$user_link="<a href=\"".$c['url']."\">".$user_name."</a>";
			}
			
			//Kolla om författaren är admin
			
			if(user_get_admin($c['user'])>1)
				$admin=" "._("(Admin)");
			else
				$admin="";
			
			if(!isset($user_name))
				echo sprintf(_("Posted at <a href=\"%s\">%s</a>"),$comment_link,$comment_time);
			else if($user_link==NULL)
				echo sprintf(_("Posted by %s%s at <a href=\"%s\">%s</a>"), $user_name, $admin, $comment_link,$comment_time);
			else
				echo sprintf(_("Posted by %s%s at <a href=\"%s\">%s</a>"), $user_link, $admin, $comment_link, $comment_time);
		}
	}
}

function comment_count($type, $id)
{
	$sql="SELECT id FROM ".PREFIX."comment WHERE comment_type='".$type."' AND comment_on='".$id."';";
	$return=0;
	if($cc=mysql_query($sql))
	{
		while($c=mysql_fetch_assoc($cc))
		{
			$return++;
			$return+=comment_count('comment', $c['id']);
		}
	}
	return $return;
}
?>
