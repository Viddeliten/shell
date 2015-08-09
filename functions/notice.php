<?php

function notice_receive()
{
	if(isset($_POST['notice_close']))
	{
		//Check that logged in user owns the notice
		if(login_check_logged_in_mini()>0)
		{
			$sql="UPDATE ".PREFIX."notice 
			SET closed=NOW()
			WHERE user=".sql_safe($_SESSION[PREFIX.'user_id'])." AND id=".sql_safe($_POST['notice_id']).";";
			if(!mysql_query($sql))
				add_error(sprintf(_("Notice could not be closed.<br />SQL: %s<br />Error: %s"),$sql, mysql_error()));
		}
	}
}

function notice_send($user, $event, $type, $subject, $message)
{
	$sql="INSERT INTO ".PREFIX."notice SET 
	user='".sql_safe($user)."',
	event='".sql_safe($event)."',
	type='".sql_safe($type)."',
	subject='".sql_safe($subject)."',
	message='".sql_safe($message)."';";
	mysql_query($sql);
}

function notice_display_notices($user)
{
	$sql="SELECT id, type, subject, message FROM ".PREFIX."notice WHERE user=".sql_safe($user)." AND closed IS NULL";
	if($nn=mysql_query($sql))
	{
		while($n=mysql_fetch_assoc($nn))
		{
			echo '
				<div class="row notice">
					<div class="panel panel-default '.$n['type'].'">
						<div class="panel-heading">
							<form method="post">
								<input type="hidden" name="notice_id" value="'.$n['id'].'">
								<input type="submit" name="notice_close" value="x" class="rightfloat close-button">
							</form>
							<h3 class="panel-title">'.$n['subject'].'</h3>
						</div>
						<div class="panel-body">
							'.$n['message'].'
						</div>
					</div>
				</div>';
		}
	}
}

?>