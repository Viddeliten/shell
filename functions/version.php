<?php

function version_receive()
{
	if(isset($_POST['increase_version']) && !strcmp($_POST['increase_version'],"yes"))
	{
		if(login_check()>1)
		{
			if(version_increase($_POST['amount']))
				add_message("New version inserted!");
			else
				add_error("There was a problem. Check the database!");
		}
	}
}

function version_add_to_upcomping_version($id, $type)
{
	$sql="INSERT INTO ".PREFIX."version_done SET done_id=".sql_safe($id).", done_type='".sql_safe($type)."';";
	// echo "<br />DEBUG1305: $sql";
	if(mysql_query($sql))
	{
		if(mysql_affected_rows()>0)
		{
			return TRUE;
		}
	}
	return FALSE;
}

function version_increase($amount)
{
	// echo "<br />DEBUG1337: Ny version $amount!";
	/************************************/
	/*	Ta reda p� nytt versionsnummer	*/
	/************************************/
	//Ta reda p� nyvarande versionsnummer
	$sql="SELECT version, time FROM ".PREFIX."version ORDER BY id DESC LIMIT 0,1";
	// echo "<br />DEBUG1342: $sql";
	if($vv=mysql_query($sql))
	{
		if($v=mysql_fetch_array($vv))
		{
			//R�kna ut nytt v�rde
			if(!strcmp($amount,"next"))
			{
				$new_version=(int)($v['version']+1);
			}
			else
			{
				$new_version=$v['version']+$amount;
			}
		}
		else
			$new_version=$amount; //current version is 0
		
		// echo "<br />DEBUG1341: Next version: $new_version";
		//S�tt in en ny version med det nya versionsnumret
		$sql="INSERT INTO ".PREFIX."version SET version=$new_version;";
		if(mysql_query($sql))
		{
			$id=mysql_insert_id();
			//L�gg in alla feedbacks som �r f�rdiga och inte redan finns i version_done
			version_add_unlinked_feedbacks_to_latest($v['time']);
			//S�tt in id f�r den nya versionen till alla som har version=NULL i version_done
			$sql="UPDATE ".PREFIX."version_done SET version=$id WHERE version IS NULL;";
			if(mysql_query($sql))
			{
				return TRUE;
			}
			else
				add_error("There was a problem with version_done!");
		}
		else
			add_error("There was a problem with the version!");
	}
	else
		add_error("There was a problem with the database!");
	return FALSE;
}

function version_add_unlinked_feedbacks_to_latest($time)
{
	$sql="SELECT id, resolved FROM ".PREFIX."feedback WHERE resolved>'".sql_safe($time)."';";
	// echo "<br />DEBUG1351: $sql";
	if($ff=mysql_query($sql))
	{
		while($f=mysql_fetch_array($ff))
		{
			//Kolla om denna finns i version_done
			$sql="SELECT id FROM ".PREFIX."version_done WHERE done_type='feedback' AND done_id=".$f['id'].";"; // AND version IS NULL;";
			// echo "<br />DEBUG1352: $sql";
			mysql_query($sql);
			if(mysql_affected_rows()<1)
			{
				//Den fanns inte, vi beh�ver l�gga in
				$sql="INSERT INTO ".PREFIX."version_done SET done_id=".$f['id'].", done_type='feedback', time='".$f['resolved']."';";
				// echo "<br />DEBUG1353: $sql";
				mysql_query($sql);
			}
		}
	}
}

function version_show_linked_number($before_str, $link_class="")
{
	//Ta reda p� nyvarande versionsnummer
	$sql="SELECT version FROM ".PREFIX."version ORDER BY id DESC LIMIT 0,1";
	// echo "<br />DEBUG1342: $sql";
	if($vv=mysql_query($sql))
	{
		if($v=mysql_fetch_array($vv))
		{
			echo "<a class=\"".$link_class."\" href=\"".SITE_URL."?p=changelog\">";
			if($before_str!="")
				echo $before_str." ";
			// echo round($v['version'], 2);
			echo $v['version']."</a>";
			return true;
		}
	}
	echo "<a class=\"".$link_class."\" href=\"".SITE_URL."?p=changelog\">";
	if($before_str!="")
		echo $before_str." ";
	echo "0</a>";
	return false;
}

function version_show_latest($nr=10)
{
	echo "<h3>"._("Changelog")."</h3>";
	
	//Om man �r admin vill man se vad som �r f�rdigt, men inte tillagt i en version
	$logged_in_level=login_check();
	if($logged_in_level>1) //login_check()>1)
	{
		version_add_unlinked_feedbacks_to_latest(NULL);
		$sql="SELECT version, done_id, done_type, time FROM ".PREFIX."version_done WHERE version IS NULL ORDER BY time DESC;";
		// echo "<br />DEBUG1730: $sql";
		if($dd=mysql_query($sql))
		{
			while($d=mysql_fetch_array($dd))
			{
				if(!strcmp($d['done_type'],"feedback"))
				{
					//H�mta rubrik och text fr�n feedback s� vi kan skriva ut en gullig l�nk
					$sql="SELECT id, subject, text FROM ".PREFIX."feedback WHERE id=".sql_safe($d['done_id']).";";
					if($ff=mysql_query($sql))
					{
						if($f=mysql_fetch_array($ff))
						{
							echo "<p>".date("Y-m-d H:i",strtotime($d['time']))." : <a href=\"".SITE_URL."?p=feedback&amp;id=".$f['id']."\">";
							if($f['subject']!=NULL && $f['subject']!="")
							{
								echo $f['subject'];
							}
							else
							{
								$f_text=str_replace("\n","<br />",$f['text']);
								echo $f_text;
							}
							echo "</a></p>";
						}
					}
				}
			}
		}
	}
	
	$sql="SELECT id, version FROM ".PREFIX."version WHERE version!=0 ORDER BY id DESC LIMIT 0,".sql_safe($nr).";";
	// echo "<br />DEBUG1422: $sql";
	if($vv=mysql_query($sql))
	{
		while($v=mysql_fetch_array($vv))
		{
			echo "<h4>".round($v['version'], 2)."</h4>";
			//H�mta allt fr�n version_done f�r denna version
			$sql="SELECT version, done_id, done_type, time FROM ".PREFIX."version_done WHERE version=".sql_safe($v['id'])." ORDER BY time DESC;";
			if($dd=mysql_query($sql))
			{
				while($d=mysql_fetch_array($dd))
				{
					if(!strcmp($d['done_type'],"feedback"))
					{
						//H�mta rubrik och text fr�n feedback s� vi kan skriva ut en gullig l�nk
						$sql="SELECT id, subject, text FROM ".PREFIX."feedback WHERE id=".sql_safe($d['done_id']).";";
						if($ff=mysql_query($sql))
						{
							if($f=mysql_fetch_array($ff))
							{
								if($logged_in_level>1)
									echo "<p>".date("Y-m-d H:i",strtotime($d['time']))." : <a href=\"".SITE_URL."?p=feedback&amp;id=".$f['id']."\">";
								else
									echo "<p>".date("Y-m-d",strtotime($d['time']))." : <a href=\"".SITE_URL."?p=feedback&amp;id=".$f['id']."\">";
								if($f['subject']!=NULL && $f['subject']!="")
								{
									echo $f['subject'];
								}
								else
								{
									$f_text=str_replace("\n","<br />",$f['text']);
									echo $f_text;
								}
								echo "</a></p>";
							}
						}
					}
				}
			}
		}
	}
}

function version_display_settings()
{

		
	echo "<h1>Version</h1>";
	version_show_linked_number("Current version: ");
	/********************************/
	/*		Increase version		*/
	/********************************/
	?>
	<form id="increase_version_form" method="post">
	<h3>New version</h3>
	<input type="radio" name="amount" value="0.01" /> 0.01<br />
	<input type="radio" name="amount" value="0.1" /> 0.1<br />
	<input type="radio" name="amount" value="next" /> Next whole number
	<br />
	<input type="button" name="increase_version_button" onclick="confirmation_form('increase_version_form','Do you really want to increase the version?')" value="Increase!">
	<input type="hidden" name="increase_version" value="yes">
	</form>
			<?php
}
?>