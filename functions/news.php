<?php

function news_receive()
{
	if(isset($_POST['news']) && isset($_POST['text']))
	{
		//Check for admin
		if(login_check_logged_in_mini()<2)
		{
			add_error(_("Unsufficient access"));
			return NULL;
		}

		if(isset($_POST['time']))
			$time=date("YmdHis",strtotime($_POST['time']));
		else
			$time=date("YmdHis");

		$sql="INSERT INTO ".PREFIX."news set headline='".sql_safe($_POST['headline'])."', author='".sql_safe($_SESSION[PREFIX.'user_id'])."', text='".sql_safe($_POST['text'])."', time='".$time."', autogenerated=0;";
		if(mysql_query($sql))
			add_message(_("News post inserted"));
		else
			add_error(sprintf(_("News post could not be inserted. Error: %s"), mysql_error()));
	}
}

function news_form()
{
	//TODO: Make this more bootstrappy
	echo "<form method=\"post\">
		<h2>News</h2>
		<p>Time:<br /><input type='date' name='time' value='".date("Y-m-d H:i")."'></p>
		<p>Headline:<br /><input type='text' name='headline'></p>
		<p>Text:<br /><textarea name='text'></textarea></p>
		<input type='submit' name='news' value='Insert'>";
	echo "</form>";
}

function news_show_latest_short($antal, $length=150, $show_auto=1, $headline_size=2, $offset=0, $extra_headline="", $extra_headline_size=2)
{
	$auto="";
	if(!$show_auto)
		$auto="WHERE autogenerated=0";
	// echo "<br />DEBUG1323: nyheter";
	$sql="SELECT 
		id, headline, time, author, SUBSTRING(`text`, 1, ".sql_safe( $length).") AS texten 
		FROM ".PREFIX."news ".$auto." 
		ORDER BY time DESC 
		WHERE time<'".date("YmdHis")."'
		LIMIT ".sql_safe($offset).",".sql_safe($antal).";";
	// echo "<br />DEBUG1323: $sql";
	if($dhp=mysql_query($sql)) //Hämta bara de senaste
	{
		if(mysql_affected_rows()>0 && $extra_headline!="")
			echo "<h$extra_headline_size>$extra_headline</h$extra_headline_size>";
		echo "<ul class=\"wdgtlist\">";
		if($news = mysql_fetch_array($dhp))
		{
			echo "<li class=\"first\">";
			echo "<h".$headline_size."><a href=\"".SITE_URL."?page=DHPost&amp;side=news&amp;id=".$news['id']."\">".$news['headline']."</a></h".$headline_size.">";
			if($news['author']!=NULL)
				echo "<p class=\"date\">".sprintf(_("Posted by %s at %s"),user_get_name($news['author']) ,date("Y-m-d H:i",strtotime($news['time'])))."</p>";
			else
				echo "<p class=\"date\">$news[time]</p>";
			$text=str_replace("\n","<br />",$news['texten']);
			$text=str_replace("<br /><br />","<br />",$text);
			
			$text=strip_tags($text, '<p><a><br />');
			
			$starters=substr_count($text, '<');
			$enders=substr_count($text, '>');
			for($i=0;$i<$starters-$enders;$i++)
				$text.=">";
			$starters=substr_count($text, '<p>');
			$enders=substr_count($text, '</p>');
			for($i=0;$i<$starters-$enders;$i++)
				$text.="</p>";
			$starters=substr_count($text, '<a');
			$enders=substr_count($text, '</a>');
			for($i=0;$i<$starters-$enders;$i++)
				$text.="</a>";
			
			echo "<p>$text<a href=\"".SITE_URL."?page=DHPost&amp;side=news&amp;id=".$news['id']."\">[...]</a></p>";
			echo "<div class=\"clearer\"></div></li>";
		}
		while($news = mysql_fetch_array($dhp))
		{
			echo "<li>";
			echo "<h".$headline_size."><a href=\"".SITE_URL."?page=DHPost&amp;side=news&amp;id=".$news['id']."\">".$news['headline']."</a></h".$headline_size.">";
			if($news['author']!=NULL)
				echo "<p class=\"date\">".sprintf(_("Posted by %s at %s"),user_get_name($news['author']) ,date("Y-m-d H:i",strtotime($news['time'])))."</p>";
			else
				echo "<p class=\"date\">$news[time]</p>";
			$text=str_replace("\n","<br />",$news['texten']);
			$text=str_replace("<br /><br />","<br />",$text);
			$text=strip_tags($text, '<p><a><br />');
			
			$starters=substr_count($text, '<');
			$enders=substr_count($text, '>');
			for($i=0;$i<$starters-$enders;$i++)
				$text.=">";
			$starters=substr_count($text, '<p>');
			$enders=substr_count($text, '</p>');
			for($i=0;$i<$starters-$enders;$i++)
				$text.="</p>";
			$starters=substr_count($text, '<a');
			$enders=substr_count($text, '</a>');
			for($i=0;$i<$starters-$enders;$i++)
				$text.="</a>";

			echo "<p>$text<a href=\"".SITE_URL."?page=DHPost&amp;side=news&amp;id=".$news['id']."\">[...]</a></p>";
			echo "<div class=\"clearer\"></div></li>";
		}
		echo "</ul>";
	}
}

function news_show($max_nr=10, $extra_headline="", $extra_headline_size=2)
{
	if(isset($_GET['id'])) //Om vi kommit till en direktlänk, och bara ska visa en nyhet
	{
		//Hämta bestämd nyhet
		$sql="SELECT id, headline, time, author, text 
			FROM ".PREFIX."news 
			WHERE id=".sql_safe($_GET['id'])."
			AND time<'".date("YmdHis")."';";
		// echo "<br />DEBUG1422: $sql";
		if($nn=mysql_query($sql)) 
		{
			if(mysql_affected_rows()>0 && $extra_headline!="")
				echo "<h$extra_headline_size>$extra_headline</h$extra_headline_size>";
			while($news = mysql_fetch_array($nn))
			{
				echo "<div class=\"news_post\">";
				echo "<h2>$news[headline]</h2>";
				if($news['author']!=NULL)
					echo "<p class=\"date\">".sprintf(_("Posted by %s at %s"),user_get_name($news['author']) ,date("Y-m-d H:i",strtotime($news['time'])))."</p>";
				else
					echo "<p class=\"date\">$news[time]</p>";
				$text=str_replace("\n","<br />",$news['text']);
				$text=str_replace("<br /><br />","</p><p>",$text);
				echo "<p>$text</p>";
				//visa kommentarer och om inloggad f? f??tt kommentera
				comments_show_comments_and_replies($news['id'], "news");
				echo "</div>";
			}
		}
	}
	else
	{
		$sql="SELECT * FROM ".PREFIX."news 
		WHERE time<'".date("YmdHis")."'
		ORDER BY time DESC LIMIT 0,".sql_safe($max_nr).";";
		// echo "<br />DEBUG1423: $sql";
		if($nn=mysql_query($sql)) //Hämta bara de senaste
		{
			//Hämta de senaste nyheterna
			while($news = mysql_fetch_array($nn))
			{
				echo "<div class=\"news_post\">";
				echo "<h2><a href=\"".SITE_URL."?page=DHPost&amp;side=news&amp;id=".$news['id']."\">$news[headline]</a></h2>";
				if($news['author']!=NULL)
					echo "<p class=\"date\">".sprintf(_("Posted by %s at %s"),user_get_name($news['author']) ,date("Y-m-d H:i",strtotime($news['time'])))."</p>";
				else
					echo "<p class=\"date\">$news[time]</p>";
				$text=str_replace("\n","<br />",$news['text']);
				$text=str_replace("<br /><br />","</p><p>",$text);
				echo "<p>$text</p>";
				//visa kommentarer och om inloggad f? f??tt kommentera
				comments_show_comments_and_replies($news['id'], "news");
				echo "</div>";
			}
		}
	}
}

?>