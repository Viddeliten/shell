<?php

function search_display_results($search_string)
{
	$results=search_search($search_string);

	$card_array=array();
	foreach($results as $r)
	{
		$card_array[]=array(   array(  "type"     =>  "title",
                                     "class"    =>  "",
                                     "content"  =>  html_tag("span", $r['author'] ,"author").$r['type'].": ".html_link($r['url'], $r['title'])
                                 ),
                             array(  "type"     =>  "body",
                                     "class"    =>  "",
                                     "content"  =>  $r['description']
                                 )
                        );
	}
	echo html_tag("h1",_("Search results"));
	if(!empty($card_array))
	{
		foreach($card_array as $c)
			echo html_card_from_array($c, "search_result");
	}
	else
	{
		echo message_warning(sprintf(_("Your search for '%s' did not render any results"), sql_safe($search_string)));
	}
}

function search_search($search_string)
{
	$results=array();
	
	//Search in feedback
	$feedback=feedback_search_results($search_string, 0, 10);
	// preprint($feedback);
	
	foreach($feedback as $key => $f)
	{
		$results[]=array( "title"	=>	$f['subject'],
						"author"	=>	user_get_name($f['user']),
						"type"	=>	"Feedback",
						"description"	=>	substr(string_remove_tags($f['text']), 0, 100),
						"url"	=>	feedback_get_url($f['id']),
						"sort_value"	=> $key
						);
	}
	
	//TODO: Search in comments ? Problem: only search in comments user is allowed to see
	
	//News
	$news=news_search_get($search_string);
	// preprint($news, "NEWS");
	foreach($news as $key => $val)
	{
		$results[]=array( "title"	=>	$val['headline'],
						"author"	=>	$val['author'],
						"type"	=>	"News",
						"description"	=>	substr(string_remove_tags($val['text']), 0, 100),
						"url"	=>	news_get_link_url($val['id']),
						"sort_value"	=> $key
						);
	}
	
	//user's profiles
	
	//Custom search results
	if(function_exists("search_custom_search_all"))
		$results=array_merge($results, search_custom_search_all($search_string));
	
	//Sort on sort_value
	string_array_multisort($results, "sort_value", SORT_ASC);
	
	return $results;
}
?>