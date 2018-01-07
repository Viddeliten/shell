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

		if(isset($_POST['published']) && $_POST['published']!="" && strtotime($_POST['published'])>=time())
			$time=date("YmdHis",strtotime($_POST['published']));
		else
			$time=date("YmdHis");

		$sql="INSERT INTO ".PREFIX."news set headline='".sql_safe($_POST['headline'])."', 
		author='".sql_safe($_SESSION[PREFIX.'user_id'])."', text='".sql_safe($_POST['text'])."', 
		published='".$time."', autogenerated=0;";
		if(mysql_query($sql))
			message_add_success_message(_("News post inserted"));
		else
			add_error(sprintf(_("News post could not be inserted. Error: %s"), mysql_error()));
	}
}

function news_form()
{
	$r='<form method="post">';
	$r.=html_tag("h2",_("News"));
	$r.=html_form_input("published_text", _("Published:"), "text", 'published', date("Y-m-d H:i"));
	$r.=html_form_input("headline_text", _("Headline:"), "text", 'headline', "", _("Headline for the news"),NULL, NULL, NULL, NULL, NULL, TRUE);
	$r.=html_form_textarea("text_textarea", _("Text:"), "text", "", _("The news content"));
	$r.=html_form_button("news", "insert", "success");
	$r.="</form>";
	return $r;
}

function news_show_latest_short($antal, $length=150, $show_auto=1, $headline_size=2, $offset=0, $extra_headline="", $extra_headline_size=2)
{
	$auto="";
	if(!$show_auto)
		$auto="AND autogenerated=0";
	// echo "<br />DEBUG1323: nyheter";
	$sql="SELECT 
		id, headline, published, author, SUBSTRING(`text`, 1, ".sql_safe( $length).") AS texten 
		FROM ".PREFIX."news 
		WHERE published<'".date("YmdHis")."' ".$auto." 
		ORDER BY published DESC 
		LIMIT ".sql_safe($offset).",".sql_safe($antal).";";
	// echo "<br />DEBUG1323: $sql";
	if($dhp=mysql_query($sql)) //Hämta bara de senaste
	{
		if(mysql_affected_rows()>0 && $extra_headline!="")
			echo "<h$extra_headline_size>$extra_headline</h$extra_headline_size>";
		while($news = mysql_fetch_array($dhp))
		{
			news_display_post($news['id'], $news['headline'], $news['published'], $news['author'], 
		$news['texten']."<a href=\"".news_get_link_url($news['id'])."\">[...]</a>", 
				$extra_headline_size+1, TRUE);
			// echo "<li class=\"first\">";
			// echo "<h".$headline_size."><a href=\"".news_get_link_url($news['id'])."\">".$news['headline']."</a></h".$headline_size.">";
			// if($news['author']!=NULL)
				// echo "<p class=\"date\">".sprintf(_("Posted by %s at %s"),user_get_link($news['author']) ,date("Y-m-d H:i",strtotime($news['time'])))."</p>";
			// else
				// echo "<p class=\"date\">$news[time]</p>";
			// $text=str_replace("\n","<br />",$news['texten']);
			// $text=str_replace("<br /><br />","<br />",$text);
			
			// $text=strip_tags($text, '<p><a><br />');
			
			// $starters=substr_count($text, '<');
			// $enders=substr_count($text, '>');
			// for($i=0;$i<$starters-$enders;$i++)
				// $text.=">";
			// $starters=substr_count($text, '<p>');
			// $enders=substr_count($text, '</p>');
			// for($i=0;$i<$starters-$enders;$i++)
				// $text.="</p>";
			// $starters=substr_count($text, '<a');
			// $enders=substr_count($text, '</a>');
			// for($i=0;$i<$starters-$enders;$i++)
				// $text.="</a>";
			
			// echo "<p>$text<a href=\"".SITE_URL."?page=DHPost&amp;side=news&amp;id=".$news['id']."\">[...]</a></p>";
			// echo "<div class=\"clearer\"></div></li>";
		}
		// while($news = mysql_fetch_array($dhp))
		// {
			// echo "<li>";
			// echo "<h".$headline_size."><a href=\"".SITE_URL."?page=DHPost&amp;side=news&amp;id=".$news['id']."\">".$news['headline']."</a></h".$headline_size.">";
			// if($news['author']!=NULL)
				// echo "<p class=\"date\">".sprintf(_("Posted by %s at %s"),user_get_link($news['author']) ,date("Y-m-d H:i",strtotime($news['time'])))."</p>";
			// else
				// echo "<p class=\"date\">$news[time]</p>";
			// $text=str_replace("\n","<br />",$news['texten']);
			// $text=str_replace("<br /><br />","<br />",$text);
			// $text=strip_tags($text, '<p><a><br />');
			
			// $starters=substr_count($text, '<');
			// $enders=substr_count($text, '>');
			// for($i=0;$i<$starters-$enders;$i++)
				// $text.=">";
			// $starters=substr_count($text, '<p>');
			// $enders=substr_count($text, '</p>');
			// for($i=0;$i<$starters-$enders;$i++)
				// $text.="</p>";
			// $starters=substr_count($text, '<a');
			// $enders=substr_count($text, '</a>');
			// for($i=0;$i<$starters-$enders;$i++)
				// $text.="</a>";

			// echo "<p>$text<a href=\"".SITE_URL."?page=DHPost&amp;side=news&amp;id=".$news['id']."\">[...]</a></p>";
			// echo "<div class=\"clearer\"></div></li>";
		// }
		// echo "</ul>";
	}
}

function news_display_post($id, $headline, $published, $author, $text, $headline_size=2, $linked=FALSE)
{
	echo "<div class=\"news_post\">";
	if($linked)
		echo "<h$headline_size><a href=\"".news_get_link_url($id)."\">$headline</a></h$headline_size>";
	else
		echo "<h$headline_size>$headline</h$headline_size>";
	if($author!=NULL)
		echo "<p class=\"date\">".sprintf(_("Published by %s at %s"),user_get_link($author) ,date("Y-m-d H:i",strtotime($published)))."</p>";
	else
		echo "<p class=\"date\">$published</p>";
	$text=str_replace("\n","<br />",$text);
	$text=str_replace("<br /><br />","</p><p>",$text);
	echo "<p>$text</p>";
	
	//visa kommentarer och om inloggad f? f??tt kommentera
	comments_show_comments_and_replies($id, "news");
	echo "</div>";

}

function news_show($max_nr=10, $extra_headline="", $extra_headline_size=2)
{
	if(isset($_GET['id'])) //Om vi kommit till en direktlänk, och bara ska visa en nyhet
	{
		//Hämta bestämd nyhet
		$sql="SELECT id, headline, published, author, text 
			FROM ".PREFIX."news 
			WHERE id=".sql_safe($_GET['id'])."
			AND published<'".date("YmdHis")."';";
		// echo "<br />DEBUG1422: $sql";
		if($nn=mysql_query($sql)) 
		{
			while($news = mysql_fetch_array($nn))
			{
				news_display_post($news['id'], $news['headline'], $news['published'], $news['author'], $news['text'], $extra_headline_size);
				// echo "<div class=\"news_post\">";
				// echo "<h$extra_headline_size>$news[headline]</h$extra_headline_size>";
				// if($news['author']!=NULL)
					// echo "<p class=\"date\">".sprintf(_("Posted by %s at %s"),user_get_link($news['author']) ,date("Y-m-d H:i",strtotime($news['time'])))."</p>";
				// else
					// echo "<p class=\"date\">$news[time]</p>";
				// $text=str_replace("\n","<br />",$news['text']);
				// $text=str_replace("<br /><br />","</p><p>",$text);
				// echo "<p>$text</p>";
				// visa kommentarer och om inloggad f? f??tt kommentera
				// comments_show_comments_and_replies($news['id'], "news");
				// echo "</div>";
			}
		}
	}
	else
	{
		$sql="SELECT * FROM ".PREFIX."news 
		WHERE published<'".date("YmdHis")."'
		ORDER BY published DESC LIMIT 0,".sql_safe($max_nr).";";
		// echo "<br />DEBUG1423: $sql";
		if($nn=mysql_query($sql)) //Hämta bara de senaste
		{
			if(mysql_affected_rows()>0 && $extra_headline!="")
				echo "<h$extra_headline_size>$extra_headline</h$extra_headline_size>";
	
			//Hämta de senaste nyheterna
			while($news = mysql_fetch_array($nn))
			{
				news_display_post($news['id'], $news['headline'], $news['published'], $news['author'], $news['text'], $extra_headline_size+1, TRUE);
				// echo "<div class=\"news_post\">";
				// echo "<h".($extra_headline_size+1)."><a href=\"".news_get_link_url($news['id'])."\">$news[headline]</a></h".($extra_headline_size+1).">";
				// if($news['author']!=NULL)
					// echo "<p class=\"date\">".sprintf(_("Posted by %s at %s"),user_get_link($news['author']) ,date("Y-m-d H:i",strtotime($news['time'])))."</p>";
				// else
					// echo "<p class=\"date\">$news[time]</p>";
				// $text=str_replace("\n","<br />",$news['text']);
				// $text=str_replace("<br /><br />","</p><p>",$text);
				// echo "<p>$text</p>";
				// visa kommentarer och om inloggad f? f??tt kommentera
				// comments_show_comments_and_replies($news['id'], "news");
				// echo "</div>";
			}
		}
		
		//RSS-link
		echo '<a href="'.SITE_URL.'/operation/rss_news.php">rss</a>';
	}
}

function news_get_link_url($id=NULL)
{
	if($id==NULL)
		return SITE_URL."?p=news";
	return SITE_URL."?p=news&amp;id=".$id;
}

?>