<?php

require_once(ABS_PATH."/functions/news.php");
require_once(ABS_PATH."/functions/class_base.php");
require_once(ABS_PATH."/functions/class_db.php");

class comment extends base_class
{
	function __construct($id=NULL, $criteria=NULL)
	{
		parent::__construct(PREFIX."comment", $id, NULL, $criteria);
		
		if(isset($criteria['comment_related_to_user']))
			$this->get_comment_related_to_user($criteria['comment_related_to_user']);
	}
	
	private function get_comment_related_to_user($user_id, $only_unseen=TRUE)
	{
		$query="SELECT
		".PREFIX."comment.*,
		access_log.id as access_log_id
		FROM comment_related_to_users
		INNER JOIN ".PREFIX."comment ON ".PREFIX."comment.id=comment_related_to_users.new_comment_id
		LEFT JOIN ".PREFIX."access_log access_log 
			ON access_log.user_id=".sql_safe($user_id)." 
			AND ".PREFIX."comment.comment_type = access_log.table 
			AND access_log.table_id=".PREFIX."comment.comment_on
			AND access_log.time>".PREFIX."comment.added
        WHERE comment_related_to_users.affected_user_id=".sql_safe($user_id)."
		AND ".PREFIX."comment.is_spam<0
		GROUP BY ".PREFIX."comment.id
		HAVING access_log_id IS NULL
        ORDER BY ".PREFIX."comment.added DESC
		";
		$this->data=$this->db->select($query);
	}
}

function comment_receive()
{
	$inloggad=login_check_logged_in_mini();
	
	if(isset($_POST['addcomment']))
	{
		// If the comment itself is empty, there was no comment. Tell the user everything is fine and skip inserting it. Because it will be a spammer that entered it, I think you'll find.
		if(!isset($_POST['comment']) || $_POST['comment'] == NULL || $_POST['comment']=="")
		{
			message_add_success_message(_("Thanks! Everything is fine."));
			return TRUE;
		}
		
		if(!is_numeric($_POST['id']))
		{
			message_add_error(sprintf(_("Unable to find instance to comment on. Invalid id '%s'"), $_POST['id']));
		}
		else if(login_check_logged_in_mini()>0 || login_captcha_check())
		{
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
			user=".sql_safe($user).",
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
	echo '<div class="comment_form">';
	
	$action_url="";
	
	if(login_check_logged_in_mini()<1 && !strcmp($type,"comment"))
	{
		$action_url=comment_get_link_url($id, NULL, $linktitle);
	}
	else if(!strcmp($type,"user"))
	{
		$action_url=user_get_link_address($id);
	}
	else if(login_check_logged_in_mini()<1)
	{
		$action_url=SITE_URL."/".$type."/id/".$id;
	}
	?>
	<form method="post" <?php echo ($action_url!="" ? 'action="'.$action_url.'"' : ""); ?>">
	
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
	echo '</div>';
}

function comment_html_list_users_latest($user_id, $only_last_24_hours=TRUE, $limit=20, $offset=0)
{
	if($only_last_24_hours)
	{
		$table="comment_for_alert";
	}
	else
		$table="comment_related_to_users";
	
	$sql="SELECT new_comment_id, type_commented_on, id_commented_on FROM ".PREFIX.$table." WHERE affected_user_id=".sql_safe($user_id)." 
    ORDER BY `time`ASC LIMIT ".sql_safe($offset).", ".sql_safe($limit).";";
	// $comments=sql_get($sql, $array=false, $index_column=NULL, $warning_on_fail=FALSE);
	$comments=sql_get($sql, true, "id_commented_on", TRUE);
	$return=array();
	// return prestr($comments);
	
	foreach($comments as $c)
	{
		$return[]=comment_get_link($c["new_comment_id"]);
	}
	
	return implode("<br />", $return);
	
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
				//Om man inte är inloggad ska man kunna svara med captcha på separat sida
				echo html_link(comment_get_link_url_add_comment($c['id'], "comment"), _("Reply"));
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

function comments_show_comments_and_replies($id, $type, $print=TRUE)
{
	ob_start();

	if(isset($_GET['comment'])) //we are on a link to a specific comment
		echo "<div id=\"comments".$id."\">";
	else
		echo "<div id=\"comments".$id."\" style=\"display:none\">";
		
		echo "<p><a class=\"comments".$id." commentclicker\" onClick=\"showhide('comments".$id."');showhide('".$type."comments".$id."');\" class=\"commentclicker\" href=\"#comment\">[-"._("Hide comments")."-]</a></p>"; //toggle-pryl! =)
		$nrcomments=comment_show_comments($id, $type);
		
	if(login_check_logged_in_mini()>0)
		comment_form_show($id, $type, _("Add a comment:"));
	else if(!isset($_GET['p']) || strcmp($_GET['p'],"add_comment"))
		echo html_link(comment_get_link_url_add_comment($id, $type), _("Add a comment"));
	
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
	
	$contents = ob_get_contents();
	ob_end_clean();
	
	if($print)
		echo $contents;
	else
		return $contents;
}

function comments_show_latest_short($antal=3, $length=150, $ul_class="commentlist", $print=TRUE)
{
	ob_start();

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
	
	$contents = ob_get_contents();
	ob_end_clean();
	
	if($print)
		echo $contents;
	else
		return $contents;

}

function comment_display_single($comment_id, $max_length=NULL, $print=TRUE)
{
	ob_start();
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
			$comment_link=comment_get_link_url($c['id'], NULL, $linktitle);
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
						flattr_button_show($flattrID, $comment_link , "Comment ".$c['id']." - a ".$c['comment_type']." comment on ".SITE_NAME, $c['comment'], 'compact', 'en_GB');
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
	
	$contents = ob_get_contents();
	ob_end_clean();
	
	if($print)
		echo $contents;
	else
		return $contents;
}

function comment_get_link_url_add_comment($id, $type)
{
	return SITE_URL."/add_comment/".$type."/".$id;
}

function comment_get_link($id, $link_id=NULL)
{
	$url=comment_get_link_url($id, $link_id, $text);
	
	if($text=="")
		$text=_("Comment on something");
	
	return html_link($url, $text);
}
function comment_get_link_url($id, $link_id=NULL, &$linktitle)
{
	require_once(ABS_PATH."/functions/feedback/func.php");
	
	if($link_id===NULL)
		$link_id=$id;
	$sql="SELECT id, comment_type, comment_on FROM ".PREFIX."comment WHERE id=".sql_safe($id).";";
	// echo "<br />DEBUG2055: $sql";
	if($cc=mysql_query($sql))
	{
		if($c=mysql_fetch_array($cc))
		{
			//try to get custom link first
			if(function_exists("link_get_custom_comment_link_url"))
			{
				$custom_comment_link = link_get_custom_comment_link_url($c['id'], $c['comment_type'], $c['comment_on'], $linktitle);
				if($custom_comment_link!=NULL)
					return $custom_comment_link;
			}
			
			// echo "<br />DEBUG2056: ".$c['comment_type'];
			if(!strcmp($c['comment_type'],"comment"))
			{
				$return=comment_get_link_url($c['comment_on'], $id, $text);
				$linktitle=sprintf(_("Reply to %s"),strtolower($text));
				return $return;
			}
			else
			{
				if(!strcmp($c['comment_type'],"feedback"))
				{
					$linktitle=sprintf(_("Comment on feedback: %s"),feedback_get_title($c['comment_on']));
					return SITE_URL."?comment&amp;p=feedback&amp;id=".$c['comment_on']."#anchor_comment_".$link_id;
				}
				else if(!strcmp($c['comment_type'],"user"))
					// return SITE_URL."?comment&amp;p=user&amp;user=".$c['comment_on']."#anchor_comment_".$link_id;
					return user_get_link_url($c['comment_on'])."&amp;comment#anchor_comment_".$link_id;
				else if(!strcmp($c['comment_type'],"news"))
					return news_get_link_url($c['comment_on'])."&amp;comment#anchor_comment_".$link_id;
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
			$comment_link=comment_get_link_url($comment_id, NULL, $linktitle);
			
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
