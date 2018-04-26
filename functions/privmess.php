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

function privmess_send($sender, $reciever, $subject, $message, $display_sucess_message=TRUE)
{
	$sql="INSERT INTO ".PREFIX."privmess SET 
	sender='".sql_safe($sender)."',
	reciever='".sql_safe($reciever)."', 	 
	subject='".sql_safe($subject)."',
	message='".sql_safe($message)."';";
	if(mysql_query($sql))
	{
		if($display_sucess_message)
			add_message(_("Message sent"));
		return mysql_insert_id();
	}
	else
		add_error(sprintf(_("Message could not be sent. Error: %s"),mysql_error()));
	return NULL;
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

function privmess_display($return_html=FALSE)
{
    if(defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0"))
    {
        $tabs=array();
        if(login_check_logged_in_mini()>0)
        {
			$tabs['inbox']['content']=privmess_display_inbox($_SESSION[PREFIX.'user_id'], TRUE);
			$tabs['inbox']['has_tab']=TRUE;
            $tabs['sent']['content']=privmess_display_outbox($_SESSION[PREFIX.'user_id'], TRUE);
			$tabs['sent']['has_tab']=TRUE;
        }
        $tabs['compose']['content']=privmess_display_compose(NULL, "", "", TRUE);
        $tabs['compose']['has_tab']=TRUE;

        $tabs['reply']['content']=(isset($_GET['message_id']) ? privmess_display_reply($_GET['message_id']) : "");
        $tabs['reply']['has_tab']=FALSE;
        
        $tabs['single']['content']=(isset($_GET['message_id']) ? privmess_display_single_message($_GET['message_id']) : "");
        $tabs['single']['has_tab']=FALSE;

        $content=html_nav_tabs($tabs);

        if($return_html)
            return $content;
        else
            echo $content;
        return TRUE;
    }
    
	// http://getbootstrap.com/javascript/#tabs (works with v3.3.4)
	echo '
	<div class="row">
		<div class="col-lg-12">
			<!-- Nav tabs -->
		  <ul class="nav nav-tabs" role="tablist">
			<li role="presentation" class="active"><a href="'.SITE_URL.'?p=user&s=privmess" >'._("Inbox").'</a></li>
			<li role="presentation"><a href="#sent" aria-controls="sent" role="tab" data-toggle="tab">'._("Sent messages").'</a></li>
			<li role="presentation"><a href="#compose" aria-controls="compose" role="tab" data-toggle="tab">'._("Compose").'</a></li>
			<span style="display:none">
				<li role="presentation"><a href="#single" aria-controls="single" role="tab" data-toggle="tab">'._("Single").'</a></li>
			</span>
		  </ul>

		  <!-- Tab panes -->
		  <div class="tab-content">
			<div role="tabpanel" class="tab-pane fade in active" id="inbox">';
				if(login_check_logged_in_mini()>0)
					privmess_display_inbox($_SESSION[PREFIX.'user_id']);
			echo '</div>
			<div role="tabpanel" class="tab-pane fade" id="sent">';
				if(login_check_logged_in_mini()>0)
					privmess_display_outbox($_SESSION[PREFIX.'user_id']);
			echo '</div>
			<div role="tabpanel" class="tab-pane fade" id="compose">';
				privmess_display_compose();
			echo '</div>';
			// below does not have tab, but can be shown by links
			echo '
			<div role="tabpanel" class="tab-pane fade" id="reply">';
				if(isset($_GET['message_id']))
					privmess_display_reply($_GET['message_id']);
			echo '</div>
			<div role="tabpanel" class="tab-pane fade" id="single">';
				if(isset($_GET['message_id']))
					privmess_display_single_message($_GET['message_id']);
			echo '</div>
		  </div>
	  </div>
  </div>';
}

function privmess_display_single_message($message_id)
{
	if(login_check_logged_in_mini()>0)
	{
		//Show message
		$sql="SELECT sender, sent, subject, message, reciever
			FROM ".PREFIX."privmess 
			WHERE (reciever='".sql_safe($_SESSION[PREFIX.'user_id'])."' OR sender='".sql_safe($_SESSION[PREFIX.'user_id'])."')
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
				
				if(defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0"))
                {
                    $reply_link=html_link("#reply",html_button(_("Reply"), "btn btn-default", "replace_html_div_inner('reply', '".SITE_URL."/operation/privmess_reply.php?message_id=".$message_id."'); $('#reply-tab').tab('show');", TRUE), "reply_tab_link");
                }
                else
                {
                    $reply_link='<a class="reply_tab_link btn btn-default"
                                            href="#reply"
                                            aria-controls="reply"
                                            role="tab"
                                            data-toggle="tab"
                                            onclick="return replace_html_div_inner(\'reply\', \''.SITE_URL.'/operation/privmess_reply.php?message_id='.$message_id.'\');"
                                >'._("Reply").'</a>';
                }
				
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
							<div class="panel-footer">';
							if($m['reciever']==$_SESSION[PREFIX.'user_id'])
							{
								echo '
							
								<form method="get">
									<input type="hidden" name="message_id" value="'.$message_id.'">
									<input type="hidden" name="p" value="user">
									<input type="hidden" name="s" value="privmess">';
									if($m['sender'])
										echo $reply_link;
											// '<a name="privmess_reply" value="'.
											
									echo '
									<input type="submit" name="privmess_mark_unread" value="'._("Mark unread").'" class="btn btn-default">
									<input type="submit" name="privmess_delete" value="'._("Delete").'" class="btn btn-default"
											onclick="return confirm(\''._("Are you sure you want to delete this message?").'\')">';
									echo '
								</form>
							';
							}
							else
							{
								echo "<p>".sprintf(_("Sent to %s"), user_get_link($m['reciever']))."</p>";
							}
							echo '
							</div>
						</div>
					</div>
				</div>';
			}
			else
				echo "<p class=\"error\">Message could not be found</p>";
		}
	}
	else
		echo "Not logged in";
}

function privmess_display_reply($message_id)
{
	// echo "Replyinig to message $message_id";
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

function privmess_display_compose($receiver=NULL, $subject="", $quoted_text="", $return_html=fALSE)
{
    ob_start();
    ?>
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
    $contents = ob_get_contents();
	ob_end_clean();
	
	if(!$return_html)
		echo $contents;
	else
		return $contents;
}

function privmess_display_inbox($receiver_id, $return_html=FALSE)
{
    ob_start();
    
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
			// $message_link=SITE_URL."/?p=user&amp;s=privmess&amp;message_id=".$m['id'];
			if(defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0"))
            {
                $message_link='<a href="#single" class="single_tab_link" onclick="return replace_html_div_inner(\'single\', \''.SITE_URL.'/operation/privmess_single.php?message_id='.$m['id'].'\');">';
            }
            else
            {
                $message_link='<a href="#single" aria-controls="single" role="tab" data-toggle="tab" onclick="return replace_html_div_inner(\'single\', \''.SITE_URL.'/operation/privmess_single.php?message_id='.$m['id'].'\');">';
            }
            
			if($m['opened']===NULL)
				echo '<tr class="active">';
			else
				echo '<tr>';
			echo '
					<td>'.user_get_link($m['sender']).'</td>
					<td>'.$message_link.$m['subject'].'</a></td>
					<td>'.$message_link.$m['sent'].'</a></td>
					<td>'.$m['opened'].'</td>
				</tr>';
		}
		if($nr>0)
			echo '</table>';
	}
	
	echo '
		</div>
	</div>';
    
    $contents = ob_get_contents();
	ob_end_clean();
	
	if(!$return_html)
		echo $contents;
	else
		return $contents;
}

function privmess_display_outbox($sender_id, $return_html)
{
    ob_start();
    
	echo '<div class="row">
		<div class="col-xs-12">
			<h1>'._("Sent messages").'</h1>';
	
	//Get all messages
	$sql="SELECT id, reciever, sent, subject, opened
		FROM ".PREFIX."privmess 
		WHERE sender='".sql_safe($sender_id)."'
		ORDER BY sent DESC;";
	// echo preprint($sql);
	if($mm=mysql_query($sql))
	{
		$nr=mysql_affected_rows();
		if($nr>0)
			echo '<table class="table table-hover">
				<tr>
					<th>'._("Receiver").'</th>
					<th>'._("Subject").'</th>
					<th>'._("Sent").'</th>
					<th>'._("Opened").'</th>
				</tr>';
		else
			echo "<p>No messages</p>";
		
		while($m=mysql_fetch_array($mm))
		{
			// $message_link=SITE_URL."/?p=user&amp;s=privmess&amp;message_id=".$m['id'];
            if(defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0"))
            {
                // $('#someTab').tab('show')
                $message_link='<a 
                                    href="#single" 
                                    class="single_tab_link"
                                    onclick="replace_html_div_inner(\'single\', \''.SITE_URL.'/operation/privmess_single.php?message_id='.$m['id'].'\');">';
                                    
            }
            else
            {
                $message_link='<a 
                                    href="#single" 
                                    aria-controls="single" 
                                    role="tab" 
                                    data-toggle="tab"
                                    onclick="return replace_html_div_inner(\'single\', \''.SITE_URL.'/operation/privmess_single.php?message_id='.$m['id'].'\');">';
            }
            if($m['opened']===NULL)
                echo '<tr class="active">';
            else
                echo '<tr>';
            echo '
                    <td>'.user_get_link($m['reciever']).'</td>
                    <td>'.$message_link.$m['subject'].'</a></td>
                    <td>'.$message_link.$m['sent'].'</a></td>
                    <td>'.$m['opened'].'</td>
                </tr>';
		}
		if($nr>0)
			echo '</table>';
	}
	
	echo '
		</div>
	</div>';
    
    $contents = ob_get_contents();
	ob_end_clean();
	
	if(!$return_html)
		echo $contents;
	else
		return $contents;
}

?>
