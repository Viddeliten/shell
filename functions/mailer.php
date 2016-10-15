<?php
//This is just kind of a wrapper to get all outgoing automatic mails to look the same

function mailer_utf8($to, $subject = '(No subject)', $message = '', $header = '') {
	mb_internal_encoding("UTF-8");
	$header_ = 'MIME-Version: 1.0' . "\n" . 'Content-type: text/html; charset=UTF-8' . "\n";
	mb_send_mail( $to , $subject , $message, $header_ . $header);
}

function mailer_send_mail($email, $receiver_name="", $subject, $message)
{
	if($receiver_name!="")
		$mess="<h1>Hej ".$receiver_name.",</h1>";
	else
		$mess="<p><strong>Hej,</strong></p>";
	
	$mess_content=str_replace("\n","<br />",$message);
	$mess_content=str_replace("\r","<br />",$message);
	$mess_content=str_replace("<br /></br />","</p><p>",$mess_content);
	$mess_content=str_replace("</p><p></p><p>","</p><p>",$mess_content);
	
	$mess.="<p>".$mess_content."</p>
	<p>"._("Best regards").",
	<br />".SITE_NAME."
	<br />"._("Presented by Vidde Webb")."
	<br />Tel: +46 (0)73 50 66 879 - www.viddewebb.se</a></p>
	<p>github.com/Viddeliten - www.facebook.com/ViddeWebb - www.twitter.com/Vidde - www.flattr.com/profile/vidde</p>";
	
$headers = 	"From: ". mb_encode_mimeheader(SITE_NAME) . " <".CONTACT_EMAIL."> \n" .
"Reply-To: ". mb_encode_mimeheader(SITE_NAME) . " <".CONTACT_EMAIL."> \n" .
"X-Loopia-Domain: ".str_replace("http://","",SITE_URL)." \n" .
"X-Mailer: ViddeMailer/1.2";

	mailer_utf8($email, $subject, $mess, $headers);

}

function mailer_send_newsletter($subject, $message, $type, $scheduled=NULL)
{
	
	if($scheduled===NULL || $scheduled=="")
	{
		$scheduled='NULL';
	}
	else
		$scheduled="'".date("YmdHis", strtotime($scheduled))."'";
	
	$sql="INSERT INTO newsletter SET subject='".sql_safe($subject)."', body='".sql_safe($message)."', name='".sql_safe($type)."', scheduled=$scheduled";
	mysql_query($sql);

}

?>
