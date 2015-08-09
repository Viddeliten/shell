<?php

function privmess_send($sender, $reciever, $subject, $message)
{
	$sql="INSERT INTO ".PREFIX."privmess SET 
	sender='".sql_safe($sender)."',
	reciever='".sql_safe($reciever)."', 	 
	subject='".sql_safe($subject)."',
	message='".sql_safe($message)."';";
	mysql_query($sql);
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
?>