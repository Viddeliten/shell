<?php

function rss_get_feed($feed_url, $limit=5, $headline_size=4)
{
	$rss = new DOMDocument();
	$rss->load($feed_url);
	
	
	$feed = array();
	foreach ($rss->getElementsByTagName('item') as $node) {
		$item = array ( 
			'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
			'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
			'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
			'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
			);
		array_push($feed, $item);
	}


	$return=array();
	for($x=0;$x<$limit;$x++) {
		$title = str_replace(' & ', ' &amp; ', $feed[$x]['title']);
		$link = $feed[$x]['link'];
		$description = $feed[$x]['desc'];
		$date = date('l F d, Y', strtotime($feed[$x]['date']));
		
		$r=html_tag("h".$headline_size, html_link($link,$title)); //Headline title with link
		$r.=html_tag("p",html_tag("small",html_tag("em",sprintf(_("Posted on %s"),$date))));
		$r.=html_tag("p",$description);
		$return[]=$r;
	}
	return $return;
}