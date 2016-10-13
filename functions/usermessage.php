<?php
/************************************************************************/
/*		This file is for functions handling messages automatically		 
/*		sent by website according to criteria admin sets logged in 
/*		to website
/************************************************************************/

/**************************************************************/
/*		Function:	usermessage_receive
/*		Summary:	Standard receiving function for 
/*					handling _POST sent by admin, probably
/**************************************************************/	
function usermessage_receive()
{	
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
			//Save data
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
			once='".sql_safe($once)."',
			every_hours='".(isset($_POST['every_hours'])? sql_safe($_POST['every_hours']) : 0)."'
			".$sendby_str.";";
			
			// echo "DEBUG1415:<pre>$sql</pre>";
			
			if(!mysql_query($sql))
			{
				add_error(sprintf(_("Message could not be added. Error: %s"),mysql_error()));
			}
			else
			{
				add_message(_("Message added"));
				//L�gg in criterier ocks�.
				usermessage_criteria_save($criteria_name, $_POST['criteria']);
			}
		}
	}
}

function usermessage_admin_show_selecter_form()
{
	echo '<form method="post" class="form-inline">';
	$sql="SELECT event, MAX(activated) as active FROM ".PREFIX."messages_to_users GROUP BY event ORDER BY active ASC;"; //Den senaste �r alltid den som g�ller
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
	//H�mta allt
	if(!isset($_POST['id']))
	{
		//H�mta den senaste
		$sql="SELECT * FROM ".PREFIX."messages_to_users WHERE event='".sql_safe($event)."' ORDER BY activated DESC;";
	}

	
	if($mm=mysql_query($sql))
	{
		if($m=mysql_fetch_array($mm))
		{
			echo '<h2>'._("Edit message").'</h2>';
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
	//event 	subject 	message 	criteria 	reward 	once om detta meddelandet ska skickas bara en g�ng och inte varje g�ng n�got �r uppfyllt	activated 
	if(isset($SOURCE['sendby']))
	{
		if(is_array($SOURCE['sendby']))
			$sendby=$SOURCE['sendby'];
		else
			$sendby=explode(",",$SOURCE['sendby']);
	}
	else
		$sendby=array();
	
	?>
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
		<label><?php echo _("Once"); ?>:</label>
		<div class="radio">
			<label>
				<input type="radio" name="once" value="once" <?php echo (!strcmp($SOURCE['once'],"once")? 'checked="checked"' :''); ?> />
				<?php echo _("Once").html_tooltip(_("Will only be sent once per user. Once you have gotten this, you will never see it again.")); ?>
			</label>
		</div>
		<div class="radio">
			<label>
				<input type="radio" name="once" value="one_instance" <?php echo (!strcmp($SOURCE['once'],"one_instance")? 'checked="checked"' :''); ?>/>
				<?php echo _("One at the time").html_tooltip(_("Sending all the time when the criteria is fullfilled, but only if there is not a current message about it. Only use for in site private messages and notices.")); ?>
			</label>
		</div>
		<div class="radio">
			<label>
				<input type="radio" name="once" value="multiple" <?php echo (!strcmp($SOURCE['once'],"multiple")? 'checked="checked"' :''); ?>/>
				<?php echo _("Multiple times").html_tooltip(_("Sending every time criteria is fullfilled, but cares about minimum waiting time.")); ?>
			</label>
		</div>
		<?php //How often then message should be allowed to be sent
		echo html_form_input("every_hours_number", _("Minimum waiting time between messages (hours)"), "number", "every_hours", (isset($SOURCE['every_hours']) ? $SOURCE['every_hours'] : 24)); ?>
		
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
		
		<?php
		$_REQUEST['criteria_name']=(isset($SOURCE['criteria_name'])?$SOURCE['criteria_name']:"");
		usermessage_criteria_form(); ?>
		
		<br /><input class="btn btn-success" type='submit' name='add_message' value='Save this message'>
	</form>
	<?php
}

function usermessage_criteria_save($criteria_name, $criteria_arr)
{
	$checkarr=array();
	
	foreach($criteria_arr as $c)
	{
		if($c['table_name']!="")
		{
			// add_message("<pre>".print_r($c,1)."</pre>");
			
			//Check that it doesn't already exist
			$sql="SELECT id 
			FROM ".PREFIX."criteria
				WHERE name='".sql_safe($criteria_name)."'
				AND table_name='".sql_safe($c['table_name'])."'
				AND user_column='".sql_safe($c['user_column'])."'
				AND count_required='".sql_safe($c['count_required'])."'
				AND table_where='".sql_safe($c['table_where'])."'";
			if($dd=mysql_query($sql))
			{
				if(mysql_affected_rows()<1)
				{
					$sql="INSERT INTO ".PREFIX."criteria SET 
					name='".sql_safe($criteria_name)."',
					 table_name='".sql_safe($c['table_name'])."',
					 user_column='".sql_safe($c['user_column'])."',
					 count_required='".sql_safe($c['count_required'])."',
					 table_where='".sql_safe($c['table_where'])."'";
					 if(!mysql_query($sql))
					{
						add_error(sprintf(_("Criteria could not be added. Error: %s"),mysql_error()));
					}
				}
			}
			$checkarr[]=$c['table_name'].",".$c['user_column'].",".$c['table_where'].",".$c['count_required'];
		}
	}
	
	// echo "<br />checkarr:<pre>".print_r($checkarr,1)."</pre>";
	
	//Kolla att alla i databasen ska vara d�r
	$sql="SELECT id, table_name, user_column, table_where, count_required FROM  ".PREFIX."criteria WHERE name='".sql_safe($criteria_name)."';";
	if($cc=mysql_query($sql))
	{
		while($c=mysql_fetch_array($cc))
		{
			if(!in_array($c['table_name'].",".$c['user_column'].",".$c['table_where'].",".$c['count_required'], $checkarr))
			{
				$sql="DELETE FROM ".PREFIX."criteria WHERE id=".$c['id'].";";
				// echo "<br />$sql
				// <br />!in_array(".$c['table_name'].",".$c['user_column'].",".$c['table_where'].",".$c['count_required'].", ".print_r($checkarr,1)."))";
				mysql_query($sql);
			}
		}
	}
}

function usermessage_criteria_form()
{ 
	?>
	<div id="criterias">
		<?php	//Droplist to load existing criteria
		usermessage_criterias_droplist("load_criteria_droplist");
		?>	
		<button class="btn btn-default" 
				<?php $path=SITE_URL.'/operation/criteria_form.php/?1=0&criteria_name='; //#load_criteria_droplist.val()'; ?>
				onclick="replace_html_div('criterias', '<?php echo $path; ?>'+document.getElementById('load_criteria_droplist').value); return false;">
			Load criteria
		</button>
		<div class="form-group">
			<label for="criteria_name_text"><?php echo _("Name:"); ?></label>
			<input id="criteria_name_text" class="form-control" type="text" value="<?php if(isset($_REQUEST['criteria_name'])) echo $_REQUEST['criteria_name']; ?>" name="criteria_name">
		</div>
		<?php 
		if(isset($_REQUEST['criteria_name']))
			usermessage_criterias_form(0, $_REQUEST['criteria_name']);
		else
			usermessage_criterias_form(0);
		?>
	</div> <?php
}

function usermessage_criterias_form($nr_id, $criteria_name=NULL)
{
	?>
	<div id="criteria_<?php echo $nr_id; ?>">
	<?php
	
		//Check if $criteria_name is set, and if so, get the criterias
		$criterias=array();
		if($criteria_name!=NULL && $criteria_name!="")
		{
			$sql="SELECT table_name,user_column,table_where, count_required FROM ".PREFIX."criteria WHERE name='".sql_safe($criteria_name)."'";
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
	</div>
	
	<?php
}

function usermessage_criterias_droplist($droplist_id_name)
{
	$sql="SELECT name FROM ".PREFIX."criteria GROUP BY name";
	if($cc=mysql_query($sql))
	{
		echo '<select id="'.$droplist_id_name.'">
				<option value=""></option>';
		while($c=mysql_fetch_assoc($cc))
		{
			echo '<option value="'.$c['name'].'">'.$c['name'].'</option>';
		}
		echo '</select>';
	}
	
}

/************************************************************************/
/*		Displays a row with form elements for usermessage criteria		*/
/************************************************************************/
function usermessage_criterias_form_row($nr_id, $SOURCE=NULL)
{
	if($SOURCE==NULL)
		$SOURCE=$_REQUEST['criteria'][$nr_id];

	$tables=sql_get_tables();
	foreach($tables as $table)
	{
		$options[$table]=$table;
	}
	?>
	<div class="form-inline">
		<?php	
		//Droplist with all tables
		$path=SITE_URL.'/operation/condition_form.php/?1='.($nr_id).'&criteria['.$nr_id.'][table_name]'."='+this.value+'&criteria[".$nr_id."][table_where]='+document.getElementById('"."where_value_".$nr_id."').value+' ";
		$onclick="replace_html_div('criteria_".$nr_id."', '$path'); return false;";
		echo html_form_droplist(	"criteria_table_name_".$nr_id, 
										_("Table name:"), 
										"criteria[".$nr_id."][table_name]", 
										$options, 
										(isset($SOURCE['table_name'])?$SOURCE['table_name']:""),
										$onclick);	
		
		//Droplist with all columns in selected table
		$selected_table=(isset($SOURCE['table_name'])? $SOURCE['table_name']:$options[0]);
		$options=array();
		$columns=sql_get_columns($selected_table);
		foreach($columns as $column)
		{
			$options[$column]=$column;
		}
		echo html_form_droplist(	"criteria_user_column_".$nr_id, 
										_("User column:"), 
										"criteria[".$nr_id."][user_column]", 
										$options, 
										(isset($SOURCE['criteria'][$nr_id]['user_column'])?$SOURCE['criteria'][$nr_id]['user_column']:""));
										
		?>
		<div class="form-group">
			<label for="where_value_<?php echo $nr_id; ?>"><?php echo _("WHERE:"); ?></label>
			<textarea id="where_value_<?php echo $nr_id; ?>" class="form-control" name="criteria[<?php echo $nr_id; ?>][table_where]"><?php if(isset($SOURCE['table_where'])) echo $SOURCE['table_where']; ?></textarea>
		</div>
		<div class="form-group">
			<label for="criteria_count_required_<?php echo $nr_id; ?>"><?php echo _("Count required:"); ?></label>
			<input id="criteria_count_required_<?php echo $nr_id; ?>" class="form-control" type="text" value="<?php if(isset($c['count_required'])) echo $c['count_required']; ?>" name="criteria[<?php echo $nr_id; ?>][count_required]">
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

function usermessage_get_data($data, $event)
{
	$sql="SELECT ".sql_safe($data)." as data FROM ".PREFIX."messages_to_users WHERE event='".sql_safe($event)."' ORDER BY activated DESC LIMIT 1;";
	if($mm=mysql_query($sql))
	{
		if($m=mysql_fetch_array($mm))
		{
			return $m['data'];
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

/************************************************************************************************/
/*	Function: usermessage_check_messages														*/
/*	checks for messages to send to specific user (or if user_id is NULL, all users)				*/
/*	If criterias are met, messages are sent.													*/
/*	Call this from your cron if you have messages that should be sent to users regardless of if	*/
/*	they log in!																				*/
/************************************************************************************************/
function usermessage_check_messages($user_id=NULL)
{
	$users=array();
	if($user_id!==NULL)
		$users[]=$user_id;
	else
		$users=user_get_all("active");

			$sql="SELECT event FROM ".PREFIX."messages_to_users GROUP BY event;";
	if($mm=mysql_query($sql))
	{
		while($m=mysql_fetch_array($mm))
		{
			
			foreach($users as $user)
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
	
}

function usermessage_check_criteria($user, $message_event)
{
	// echo "<br />DEBUG1248: usermessage_check_criteria($user, $message_event)";
	
	//H�mta alla kriterier fr�n den senast sparade av den typen av event
	$sql="SELECT criteria_name, once, sendby FROM ".PREFIX."messages_to_users WHERE event='".sql_safe($message_event)."' ORDER BY activated DESC LIMIT 0,1;";
	// echo "<br />DEBUG1310: $sql";
	if($mm=mysql_query($sql))
	{
		if($m=mysql_fetch_array($mm))
		{
			$criteria=usermessage_get_criterias($m['criteria_name']);
			
			//Kolla om de �r uppfyllda genom att f�rs�ka hitta ett villkor som inte �r uppfyllt
			
			//Kolla om det bara ska skickas en g�ng, och redan �r skickat
			$sql="";
			$sendby=explode(",",$m['sendby']);
			
			if(!strcmp($m['once'],"once"))
			{
				usermessage_get_data("every_hours", $message_event);
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
			else if(!strcmp($m['once'],"one_instance")) //Detta ska bara skickas "med j�mna mellanrum"
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
			else if(!strcmp($m['once'],"multiple")) //Ska alltid skickas om kriterierna �r uppfyllda, men inte om det har g�tt f�r kort tid sedan senast
			{
				$every_hours=usermessage_get_data("every_hours", $message_event);
				$sql="SELECT COUNT(id) as nr 
				FROM ".PREFIX."messages_to_users_sent 
				WHERE message_event='".sql_safe($message_event)."' 
				AND user='".sql_safe($user)."'
				AND time > NOW() - INTERVAL ".sql_safe($every_hours)." HOUR;";
				if($tt=mysql_query($sql))
				{
					if($t=mysql_fetch_assoc($tt))
					{
						if($t['nr']>0)
							return FALSE;
					}
				}
			}
			
			
			//KOlla alla kriterier
			foreach($criteria as $c)
			{
				$where=$c['table_where'];
				$sql="SELECT COUNT(*) as nr FROM ".sql_safe($c['table_name'])." 
					WHERE ".sql_safe($c['user_column'])."=".sql_safe($user);
				if($where!="")
					$sql.=" AND (".$where.");";
				if($tt=mysql_query($sql))
				{
					if($t=mysql_fetch_assoc($tt))
					{
						if($c['count_required']==0)
							$c['count_required']=1;
						if($t['nr']<$c['count_required'])
							return FALSE;
					}
				}
			}
				
			return TRUE; //Vi kunde inte hitta n�gon orsak att inte skicka meddelandet
		}
	}
	return FALSE; //N�got gick snett med databasen och vi kunde inte ta reda p� n�gonting
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
				//Skicka ett ingame-meddelande till anv�ndaren med meddelandet
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
			
			//Ge eventuellt bel�ning
			if($m['reward']>0)
				money_transaction(0, $user, $m['reward'], "Reward", $m['subject']);
			
			//log successful sent in table messages_to_users_sent
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