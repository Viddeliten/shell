<?php

function privmess_receive()
{
	if(isset($_POST['privmess_send']))
	{
		privmess_send($_SESSION[PREFIX.'user_id'], user_get_id_from_username($_POST['receiver']), $_POST['subject'], $_POST['message']);
	}
	else if(isset($_GET['message_id']))
	{
		if(isset($_GET['privmess_delete']))
		{
			//Mark as deleted
			$sql="UPDATE ".PREFIX."privmess 
				SET deleted=NOW()
				WHERE reciever='".sql_safe($_SESSION[PREFIX.'user_id'])."'
				AND id=".sql_safe($_GET['message_id']).";";
			mysql_query($sql);
		}
		else if(isset($_GET['privmess_mark_unread']))
		{
			//Mark as NOT opened
			$sql="UPDATE ".PREFIX."privmess 
				SET opened=NULL
				WHERE reciever='".sql_safe($_SESSION[PREFIX.'user_id'])."'
				AND id=".sql_safe($_GET['message_id']).";";
			mysql_query($sql);
		}
		else
		{
			//Mark as opened
			$sql="UPDATE ".PREFIX."privmess 
				SET opened=NOW()
				WHERE reciever='".sql_safe($_SESSION[PREFIX.'user_id'])."'
				AND id=".sql_safe($_GET['message_id']).";";
			mysql_query($sql);
		}
	}
}

function privmess_send($sender, $reciever, $subject, $message)
{
	$sql="INSERT INTO ".PREFIX."privmess SET 
	sender='".sql_safe($sender)."',
	reciever='".sql_safe($reciever)."', 	 
	subject='".sql_safe($subject)."',
	message='".sql_safe($message)."';";
	if(mysql_query($sql))
		add_message(_("Message sent"));
	else
		add_error(sprintf(_("Message could not be sent. Error: %s"),mysql_error()));
}

function privmess_get_unread_nr($receiver_id)
{
	//Kolla om det finns olästa privmess
	$antal=0;
	$sql="SELECT id from ".PREFIX."privmess where reciever='".sql_safe($receiver_id)."' and opened IS NULL;";
	if($mm=mysql_query($sql))
	{
		while($m=mysql_fetch_array($mm))
			$antal++;
	}
	return $antal;
}

function privmess_display()
{
	if(isset($_GET['message_id']) && !isset($_GET['privmess_mark_unread']) && !isset($_GET['privmess_delete']) && !isset($_POST['privmess_send']))
	{
		if(isset($_GET['privmess_reply']))
			privmess_display_reply($_GET['message_id']);
		else
			privmess_display_single_message($_GET['message_id']);
	}
	else if(isset($_GET['compose']) && !isset($_POST['privmess_send']))
	{
		privmess_display_compose();
	}
	else
	{
		if(login_check_logged_in_mini()>0)
			privmess_display_inbox($_SESSION[PREFIX.'user_id']);
	}
}

function privmess_display_single_message($message_id)
{
	if(login_check_logged_in_mini()>0)
	{
		//Show message
		$sql="SELECT sender, sent, subject, message 
			FROM ".PREFIX."privmess 
			WHERE reciever='".sql_safe($_SESSION[PREFIX.'user_id'])."'
			AND id=".sql_safe($message_id).";";
		if($mm=mysql_query($sql))
		{
			if(($m=mysql_fetch_array($mm)))
			{
				$message=str_replace("\n\r","<br />",$m['message']);
				$message=str_replace("\r\n","<br />",$message);
				$message=str_replace("\n","<br />",$message);
				$message=str_replace("\r","<br />",$message);
				$message=str_replace("<br /><br />","</p><p>",$message);
				$message=str_replace("</p><p></p><p>","</p><p>",$message);
				echo '
				<div class="row">
					<div class="col-xm-12">
						<div class="panel panel-default">
							<div class="panel-heading">
								<p class="author">';
								if($m['sender'])
									echo sprintf(_("Sent by: %s"),user_get_link($m['sender'])).'<br />';
								echo sprintf(_("Time sent: %s"), date("Y-m-d H:i",strtotime($m['sent']))).'</p>
								<h1 class="panel-title">'.$m['subject'].'</h1>
								<div class="clearfix"></div>
							</div>
							<div class="panel-body">
								<p>'.$message.'</p>
							</div>
							 <div class="panel-footer">
								<form method="get">
									<input type="hidden" name="message_id" value="'.$message_id.'">
									<input type="hidden" name="p" value="user">
									<input type="hidden" name="s" value="privmess">';
									if($m['sender'])
										echo '<input type="submit" name="privmess_reply" value="'._("Reply").'" class="btn btn-default">';
									echo '
									<input type="submit" name="privmess_mark_unread" value="'._("Mark unread").'" class="btn btn-default">
									<input type="submit" name="privmess_delete" value="'._("Delete").'" class="btn btn-default"
									onclick="return confirm(\''._("Are you sure you want to delete this message?").'\')">
								</form>
							 </div>
						</div>
					</div>
				</div>';
			}
			else
				echo "<p class=\"error\">Message could not be found</p>";
		}
	}
}

function privmess_display_reply($message_id)
{
	echo "Replyinig to message $message_id";
	//Get sender, subject and message text from the message we are replying on
	$sql="SELECT sender, subject, message, sent
		FROM ".PREFIX."privmess 
		WHERE reciever='".sql_safe($_SESSION[PREFIX.'user_id'])."'
		AND id=".sql_safe($message_id).";";
	if($mm=mysql_query($sql))
	{
		if($m=mysql_fetch_assoc($mm))
		{
			$quoted="--- ".user_get_name($m['sender'])." ".$m['sent']." ---\n".$m['message']; 
			privmess_display_compose(user_get_name($m['sender']), "Re: ".$m['subject'], $quoted);
			return true;
		}
	}
	echo "<p class=\"error\">"._("Something went wrong")."<br />$sql</p>";
}

function privmess_display_compose($receiver=NULL, $subject="", $quoted_text="")
{?>
	<h1><?php echo _("Composing message"); ?></h1>
	<form method="post">
		<div class="form-group">
			<label for="receiver_text"><?php echo _("Receiver user name"); ?></label>
			<input name="receiver" type="text" class="form-control" id="receiver_text" <?php
				if($receiver!="")
					echo "value=\"$receiver\"";
			?> placeholder="<?php echo _("User name"); ?>" />
		</div>
		<div class="form-group">
			<label for="subject_text"><?php echo _("Subject"); ?></label>
			<input name="subject" type="text" class="form-control" id="subject_text" <?php
				if($subject!="")
					echo "value=\"$subject\"";
			?> placeholder="<?php echo _("Subject"); ?>" required />
		</div>
		<div class="form-group">
			<label for="message_text"><?php echo _("Messsage"); ?></label>
			<textarea name="message" id="message_text" class="form-control" placeholder="<?php echo _("Message"); ?>"><?php
				if($quoted_text!="")
					echo "\n\n".$quoted_text;
			?></textarea>
		</div>
		<input type="submit" name="privmess_send" value="<?php echo _("Send"); ?>" class="btn btn-success">
	</form>
<?php
}

function privmess_display_inbox($receiver_id)
{
	echo '<div class="row">
		<div class="col-xs-12">
			<form method="get">
				<input type="hidden" name="p" value="user">
				<input type="hidden" name="s" value="privmess">
				<input type="submit" name="compose" value="'._("Compose message").'" class="btn btn-default">
			</form>
		</div>
	</div>';
	echo '<div class="row">
		<div class="col-xs-12">
			<h1>'._("Inbox").'</h1>';
	
	//Get all messages
	$sql="SELECT id, sender, sent, subject, opened
		FROM ".PREFIX."privmess 
		WHERE reciever='".sql_safe($receiver_id)."'
		AND deleted IS NULL
		ORDER BY sent DESC;";
	if($mm=mysql_query($sql))
	{
		$nr=mysql_affected_rows();
		if($nr>0)
			echo '<table class="table table-hover">
				<tr>
					<th>'._("Sender").'</th>
					<th>'._("Subject").'</th>
					<th>'._("Sent").'</th>
					<th>'._("Opened").'</th>
				</tr>';
		else
			echo "<p>No messages</p>";
		
		while($m=mysql_fetch_array($mm))
		{
			$message_link=SITE_URL."/?p=user&amp;s=privmess&amp;message_id=".$m['id'];
			
			if($m['opened']===NULL)
				echo '<tr class="active">';
			else
				echo '<tr>';
			echo '
					<td>'.user_get_link($m['sender']).'</td>
					<td><a href="'.$message_link.'">'.$m['subject'].'</a></td>
					<td><a href="'.$message_link.'">'.$m['sent'].'</a></td>
					<td>'.$m['opened'].'</td>
				</tr>';
		}
		if($nr>0)
			echo '</table>';
	}
	
	echo '
		</div>
	</div>';
}

?>