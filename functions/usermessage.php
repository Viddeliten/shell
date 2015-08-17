<?php
function usermessage_receive()
{	
	// echo  "<pre>".print_r($_POST,1)."</pre>";
	
	if(isset($_POST['add_message']) && (!strcmp($_POST['add_message'],"Save this message") || !strcmp($_POST['add_message'],"Save new version")))
	{
		if(login_check_logged_in_mini()<2)
		{
			add_error(_("Unsuffient access"));
			return NULL;
		}
		
		if($_POST['event']=="")
		{
			add_error("Event cannot be empty");
		}
		else if($_POST['message']=="")
		{
			add_error("Message cannot be empty");
		}
		else
		{
			//ok, lägg in skiten då
			if(!isset($_POST['subject']) || $_POST['subject']=="")
				$subject='NULL';
			else
				$subject=$_POST['subject'];
			
			if(!isset($_POST['criteria_name']) || $_POST['criteria_name']=="")
				$criteria_name='NULL';
			else
				$criteria_name=$_POST['criteria_name'];
			
			if(!isset($_POST['reward']) || $_POST['reward']=="")
				$reward='NULL';
			else
				$reward=$_POST['reward'];
			if(!isset($_POST['once']))
				$once="once";
			else
				$once=$_POST['once'];
			
			$sendby_str="";
			$sendby=implode(",",$_POST['sendby']);
			if($sendby!="");
				$sendby_str=", sendby='".$sendby."'";
				
			$sql="INSERT INTO ".PREFIX."messages_to_users SET
			event='".sql_safe($_POST['event'])."',
			type='".sql_safe($_POST['type'])."',
			subject='".sql_safe($subject)."',
			message='".sql_safe($_POST['message'])."',
			criteria_name='".sql_safe($criteria_name)."',
			reward='".sql_safe($reward)."',
			once='".sql_safe($once)."'
			".$sendby_str.";";
			
			// echo "DEBUG1415:<pre>$sql</pre>";
			
			if(!mysql_query($sql))
			{
				add_error(sprintf(_("Message could not be added. Error: %s"),mysql_error()));
			}
			else
			{
				add_message(_("Message added"));
				//Lägg in criterier också.
				$checkarr=array();
				foreach($_POST['criteria'] as $c)
				{
					if($c['table_name']!="")
					{
						add_message("<pre>".print_r($c,1)."</pre>");
						
						//Check that it doesn't already exist
						$sql="SELECT id 
						FROM ".PREFIX."criteria
							WHERE name='".sql_safe($criteria_name)."'
							AND table_name='".sql_safe($c['table_name'])."'
							AND user_column='".sql_safe($c['user_column'])."'
							AND table_where='".sql_safe($c['table_where'])."'";
						 // echo "<br />DEBUG1021:";
						 // echo preprint($sql);
						if($dd=mysql_query($sql))
						{
							if(mysql_affected_rows()<1)
							{
								$sql="INSERT INTO ".PREFIX."criteria SET 
								name='".sql_safe($criteria_name)."',
								 table_name='".sql_safe($c['table_name'])."',
								 user_column='".sql_safe($c['user_column'])."',
								 table_where='".sql_safe($c['table_where'])."'";
								 // echo "<br />DEBUG1022:";
								 // echo preprint($sql);
								 if(!mysql_query($sql))
								{
									add_error(sprintf(_("Criteria could not be added. Error: %s"),mysql_error()));
								}
							}
						}
						$checkarr[]=$c['table_name'].",".$c['user_column'].",".$c['table_where'];
					}
				}
				
				// echo "<br />checkarr:<pre>".print_r($checkarr,1)."</pre>";
				
				//Kolla att alla i databasen ska vara där
				$sql="SELECT id, table_name, user_column, table_where FROM  ".PREFIX."criteria WHERE name='".sql_safe($criteria_name)."';";
				if($cc=mysql_query($sql))
				{
					while($c=mysql_fetch_array($cc))
					{
						if(!in_array($c['table_name'].",".$c['user_column'].",".$c['table_where'], $checkarr))
						{
							$sql="DELETE FROM ".PREFIX."criteria WHERE id=".$c['id'].";";
							// echo "<br />$sql
							// <br />!in_array(".$c['table_name'].",".$c['user_column'].",".$c['table_where'].", ".print_r($checkarr,1)."))";
							mysql_query($sql);
						}
					}
				}
			}
		}
	}
}

function usermessage_admin_show_selecter_form()
{
	echo '<form method="post" class="form-inline">';
	$sql="SELECT event, MAX(activated) as active FROM ".PREFIX."messages_to_users GROUP BY event ORDER BY active ASC;"; //Den senaste är alltid den som gäller
	// echo "<br />DEBUG2106: $sql";
	if($mm=mysql_query($sql))
	{
		echo '<div class="form-group">';
			echo "<select name='event' class=\"form-control\">";
			while($m=mysql_fetch_array($mm))
			{
				echo "<option value='".$m['event']."'>".$m['event']." - ".$m['active']."</option>";
			}
			echo "</select>";
		echo '</div>';
		echo "<input class=\"btn btn-default\" type='submit' name='edit_message' value='Show and edit'>";
	}
	echo "</form>";
	echo "<form method=\"post\">";
		echo "<input class=\"btn btn-default\" type='submit' name='add_message' value='Add new message'>";
	echo "</form>";
}

function usermessage_admin_show_editer_form($event)
{
	//Hämta allt
	if(!isset($_POST['id']))
	{
		//Hämta den senaste
		$sql="SELECT * FROM ".PREFIX."messages_to_users WHERE event='".sql_safe($event)."' ORDER BY activated DESC;";
	}
	
	if($mm=mysql_query($sql))
	{
		if($m=mysql_fetch_array($mm))
		{
			usermessage_admin_show_form($m);
		}
	}
	else
	{
		add_error("Could not get message");
	}
}

function usermessage_admin_show_form($SOURCE)
{
	// echo "<br />DEBUG2058: <pre>".print_r($SOURCE,1)."</pre>";
	//event 	subject 	message 	criteria 	reward 	once om detta meddelandet ska skickas bara en gång och inte varje gång något är uppfyllt	activated 
	if(is_array($SOURCE['sendby']))
		$sendby=$SOURCE['sendby'];
	else
		$sendby=explode(",",$SOURCE['sendby']);
	
	?>
	<h2><?php echo _("New message"); ?></h2>
	<form method="post">
		<div class="form-group">
			<label for="event_text"><?php echo _("Event:"); ?></label>
			<input id="event_text" class="form-control" type="text" value="<?php if(isset($SOURCE['event'])) echo $SOURCE['event']; ?>" name="event">
		</div>
		<div class="form-group">
			<label for="subject_text"><?php echo _("Subject:"); ?></label>
			<input id="subject_text" class="form-control" type="text" value="<?php if(isset($SOURCE['subject'])) echo $SOURCE['subject']; ?>" name="subject">
		</div>
		<div class="form-group">
			<label for="message"><?php echo _("message:"); ?></label>
			<textarea class="form-control" name="message"><?php if(isset($SOURCE['message']))  echo $SOURCE['message']; ?></textarea>
		</div>
		<div class="form-group">
			<label for="reward"><?php echo _("Reward (on site currency, if any):"); ?></label>
			<input class="form-control" type="text" value="<?php if(isset($SOURCE['reward'])) echo $SOURCE['reward']; ?>" name="reward">
		</div>
		<label><?php echo _("Once:"); ?></label>
		<div class="radio">
			<label>
				<input type="radio" name="once" value="multiple" />
				<?php echo _("Multiple"); ?>
			</label>
		</div>
		<div class="radio">
			<label>
				<input type="radio" name="once" value="once" checked="checked" />
				<?php echo _("Once"); ?>
			</label>
		</div>
		<div class="radio">
			<label>
				<input type="radio" name="once" value="one_instance" />
				<?php echo _("One at the time"); ?>
			</label>
		</div>
		<label for="sendby"><?php echo _("Send by:"); ?></label>
		<div class="checkbox">
			<label>
				<input type="checkbox" name="sendby[]" value="insite_privmess" <?php if(in_array("insite_privmess",$sendby)) echo ' checked="checked"'; ?> /> <?php echo _("Private message on site"); ?>
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" name="sendby[]" value="insite_notice" <?php if(in_array("insite_notice",$sendby)) echo ' checked="checked"'; ?> /> <?php echo _("Notice popup on site"); ?>
			</label>
		</div>
		<div class="checkbox">
			<label>
				<input type="checkbox" name="sendby[]" value="email" <?php if(in_array("email",$sendby)) echo ' checked="checked"'; ?> /> <?php echo _("E-mail"); ?>
			</label>
		</div>
		<label for="type"><?php echo _("Type:"); ?></label>
		<select class="form-control" name="type">
			<option value="information"><?php echo _("Information"); ?></option>
			<option value="success"><?php echo _("Success"); ?></option>
			<option value="warning"><?php echo _("Warning"); ?></option>
			<option value="error"><?php echo _("Error"); ?></option>
		</select>
		<h3><?php echo _("Criteria"); ?></h3>
		<div class="form-group">
			<label for="criteria_name_text"><?php echo _("Name:"); ?></label>
			<input id="criteria_name_text" class="form-control" type="text" value="<?php if(isset($SOURCE['criteria_name'])) echo $SOURCE['criteria_name']; ?>" name="criteria_name">
		</div>
		<?php 
		if(isset($SOURCE['criteria_name']))
			usermessage_criterias_form(0, $SOURCE['criteria_name']);
		else
			usermessage_criterias_form(0);
		?>
		
		<br /><input class="btn btn-success" type='submit' name='add_message' value='Save this message'>
	</form>
	<?php
}

function usermessage_criterias_form($nr_id, $criteria_name=NULL)
{
	//Check if $criteria_name is set, and if so, get the criterias
	$criterias=array();
	if($criteria_name!=NULL && $criteria_name!="")
	{
		$sql="SELECT table_name,user_column,table_where FROM ".PREFIX."criteria WHERE name='".sql_safe($criteria_name)."'";
		if($cc=mysql_query($sql))
		{
			while($c=mysql_fetch_assoc($cc))
			{
				$criterias[]=$c;
			}
		}
	}
	
	if(empty($criterias))
		$next_id=usermessage_criterias_form_row($nr_id);
	else
	{
		foreach($criterias as $key => $c)
			$next_id=usermessage_criterias_form_row($key, $c);
	}
	
	?>
	<div id="condition_add">
		<button class="btn btn-default" 
				<?php $path=SITE_URL.'/operation/condition_form.php/?1='.($next_id); ?>
				onclick="replace_html_div('condition_add', '<?php echo $path; ?>'); return false;">
			Add condition
		</button>
	</div>
	
	<?php //TODO: add fields for adding table criteria here!!!
}

function usermessage_criterias_form_row($nr_id, $c=NULL)
{
	?>
	<div class="form-inline">
		<div class="form-group">
			<label for="criteria_table_name_<?php echo $nr_id; ?>"><?php echo _("Table name:"); ?></label>
			<input id="criteria_table_name_<?php echo $nr_id; ?>" class="form-control" type="text" value="<?php if(isset($c['table_name'])) echo $c['table_name']; ?>" name="criteria[<?php echo $nr_id; ?>][table_name]">
		</div>
		<div class="form-group">
			<label for="criteria_user_column_<?php echo $nr_id; ?>"><?php echo _("User column:"); ?></label>
			<input id="criteria_user_column_<?php echo $nr_id; ?>" class="form-control" type="text" value="<?php if(isset($c['user_column'])) echo $c['user_column']; ?>" name="criteria[<?php echo $nr_id; ?>][user_column]">
		</div>
		<div class="form-group">
			<label for="where_value_<?php echo $nr_id; ?>"><?php echo _("WHERE:"); ?></label>
			<textarea id="where_value_<?php echo $nr_id; ?>" class="form-control" name="criteria[<?php echo $nr_id; ?>][table_where]"><?php if(isset($c['table_where'])) echo $c['table_where']; ?></textarea>
		</div>
	</div>
	<?php
	return $nr_id+1;
}

function usermessage_get_message($event)
{
	$sql="SELECT message FROM ".PREFIX."messages_to_users WHERE event='".sql_safe($event)."' ORDER BY activated DESC LIMIT 1;";
	if($mm=mysql_query($sql))
	{
		if($m=mysql_fetch_array($mm))
		{
			return $m['message'];
		}
	}
}
function usermessage_get_subject($event)
{
	$sql="SELECT subject FROM ".PREFIX."messages_to_users WHERE event='".sql_safe($event)."' ORDER BY activated DESC LIMIT 1;";
	if($mm=mysql_query($sql))
	{
		if($m=mysql_fetch_array($mm))
		{
			return $m['subject'];
		}
	}
}

function usermessage_get_criterias($criteria_name)
{
	$ret=array();
	if($cc=mysql_query("SELECT * FROM ".PREFIX."criteria WHERE name='".sql_safe($criteria_name)."';"))
	{
		while($c=mysql_fetch_assoc($cc))
		{
			$ret[]=$c;
		}
	}
	return $ret;
}

/****************************************************/
/*	Function: usermessage_check_messages			*/
/*	checks for messages to send to specific user	*/
/*	If criterias are met, messages are sent.		*/
/****************************************************/
function usermessage_check_messages($user)
{
	$sql="SELECT event FROM ".PREFIX."messages_to_users GROUP BY event;";
	if($mm=mysql_query($sql))
	{
		while($m=mysql_fetch_array($mm))
		{
			// echo "<br />DEBUG1537: usermessage_check_criteria(".$user.", ".$m['event'].")";
			if(usermessage_check_criteria($user, $m['event']))
			{
				// echo "<br />SEND ".$m['event']."!!!";
				usermessage_send_to_user($user, $m['event']);
			}
		}
	}
	
}

function usermessage_check_criteria($user, $message_event)
{
	// echo "<br />DEBUG1248: usermessage_check_criteria($user, $message_event)";
	
	//Hämta alla kriterier från den senast sparade av den typen av event
	$sql="SELECT criteria_name, once, sendby FROM ".PREFIX."messages_to_users WHERE event='".sql_safe($message_event)."' ORDER BY activated DESC LIMIT 0,1;";
	// echo "<br />DEBUG1310: $sql";
	if($mm=mysql_query($sql))
	{
		if($m=mysql_fetch_array($mm))
		{
			$criteria=usermessage_get_criterias($m['criteria_name']);
			
			//Kolla om de är uppfyllda genom att försöka hitta ett villkor som inte är uppfyllt
			
			//Kolla om det bara ska skickas en gång, och redan är skickat
			$sql="";
			$sendby=explode(",",$m['sendby']);
			
			if(!strcmp($m['once'],"once"))
			{
				$sql="SELECT COUNT(id) as nr 
					FROM ".PREFIX."messages_to_users_sent 
					WHERE message_event='".sql_safe($message_event)."'
					AND user='".sql_safe($user)."';";
					
					if($sql!="")
					{
						if($tt=mysql_query($sql))
						{
							if($t=mysql_fetch_assoc($tt))
							{
								if($t['nr']>0)
									return FALSE;
							}
						}
					}
			}
			else if(!strcmp($m['once'],"one_instance")) //Detta kan bara gälla notiser och privatmess eftersom vi vet ju inte om användaren tagit emot e-post
			{
				if(in_array("insite_privmess",$sendby))
				{
					$sql="SELECT COUNT(id) as nr 
						FROM ".PREFIX."privmess 
						WHERE subject='".sql_safe(usermessage_get_subject($message_event))."'
						AND user='".sql_safe($user)."'
						AND opened IS NULL;";
					if($sql!="")
					{
						if($tt=mysql_query($sql))
						{
							if($t=mysql_fetch_assoc($tt))
							{
								if($t['nr']>0)
									return FALSE;
							}
						}
					}
				}
				if(in_array("insite_notice",$sendby))
				{
					$sql="SELECT COUNT(id) as nr 
						FROM ".PREFIX."notice 
						WHERE event='".sql_safe($message_event)."'
						AND user='".sql_safe($user)."'
						AND closed IS NULL;";
					if($sql!="")
					{
						if($tt=mysql_query($sql))
						{
							if($t=mysql_fetch_assoc($tt))
							{
								if($t['nr']>0)
									return FALSE;
							}
						}
					}
				}
			}
			//Här skulle vi kunna ha en else för multiple, men i det fallet struntar vi i att kolla om vi skickat förut.
			//Frågan är ju när det ska användas...?
			
			
			
			//KOlla alla kriterier
			foreach($criteria as $c)
			{
				$where=$c['table_where'];
				$sql="SELECT COUNT(*) as nr FROM ".sql_safe($c['table_name'])." 
					WHERE ".sql_safe($c['user_column'])."=".sql_safe($user);
				if($where!="")
					$sql.=" AND (".$where.");";
				// echo "<br />DEBUG1310: $sql";
				// preprint($sql);
				if($tt=mysql_query($sql))
				{
					if($t=mysql_fetch_assoc($tt))
					{
						if($t['nr']<1)
							return FALSE;
					}
				}
			}
				
			return TRUE; //Vi kunde inte hitta någon orsak att inte skicka meddelandet
		}
	}
	return FALSE; //Något gick snett med databasen och vi kunde inte ta reda på någonting
}

function usermessage_send_to_user($user, $message_event)
{
	// echo "<br />DEBUG1303: usermessage_send_to_user($user, $message_event)";
	
	$sql="SELECT type, subject, message, once, reward, sendby FROM ".PREFIX."messages_to_users WHERE event='".sql_safe($message_event)."' ORDER BY activated DESC LIMIT 0,1";
	if($mm=mysql_query($sql))
	{
		if($m=mysql_fetch_array($mm))
		{
			$adress="";
			$sendby=explode(",",$m['sendby']);
			if(in_array("insite_privmess", $sendby))
			{
				//Skicka ett ingame-meddelande till användaren med meddelandet
				$privmess_id=privmess_send(0, $user, $m['subject'], $m['message'], FALSE);
				$adress.="insite_privmess";
			}
			if(in_array("insite_notice", $sendby))
			{
				notice_send($user, $message_event, $m['type'], $m['subject'], $m['message']);
				if($adress!="")
					$adress.=", ";
				$adress.="insite_notice";
			}
			if(in_array("email", $sendby))
			{
				$email=user_get_email($user);
				mailer_send_mail($adress, user_get_name($user), $m['subject'], $m['message']);
				if($adress!="")
					$adress.=", ";
				$adress.=$email;
			}
				//Ge eventuellt belöning
				if($m['reward']>0)
					money_transaction(0, $user, $m['reward'], "Reward", $m['subject']);
				//lägg in att detta skickats i messages_to_users_sent
				$sql="INSERT INTO ".PREFIX."messages_to_users_sent SET
				user='".sql_safe($user)."', 
				message_event='".sql_safe($message_event)."',
				adress='".$adress."'";
				if(isset($privmess_id))
				$sql.=", privmess_id=".sql_safe($privmess_id);
				$sql.=";";
				// echo "<br />DEBUG1753: $sql"; 
				mysql_query($sql);
		}
	}
}
?>