<?php
/***
*	function search_custom_search_all
*	Used by search function that searches entire site (search form in top menu in BS4)
***/
function search_custom_search_all($search_string)
{
	$results=array();
	$search_string=sql_safe(strtolower($search_string)); // To make search case insensitive

	/* eXAMPLE CODE:
	//user's profiles
	$columns=array("username", "description");
	$user_profiles=search_general("user", $columns, $search_string);
	foreach($user_profiles as $key => $val)
	{
		$results[]=array( "title"	=>	$val['username'],
						"author"	=>	NULL,
						"type"	=>	"User",
						"description"	=>	substr(string_remove_tags($val['description']), 0, 100),
						"url"	=>	user_get_link_url($val['id']),
						"sort_value"	=> $key
						);
	}
	*/
	return $results;
}
?>