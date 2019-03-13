<?php

function nightbot_get_playlist($user_id=NULL)
{
	// if no user id was sent to function, fetch user id of current logged in user
	if($user_id==NULL)
		$user_id=login_get_user();
	
	// Just stop if we have no user id at this point
	if($user_id==NULL)
		return FALSE;
	
	// rest_api_integration
	$api=new rest_api_integration("nightbot", TRUE);
	
	$current_playlist=$api->get(array("1","song_requests","playlist"));
	
	$playlist=array();
	foreach($current_playlist->playlist as $pl)
	{
		$pl_temp=array();
		$pl_temp['_id']=$pl->_id;
		foreach($pl->track as $key => $val)
		{
			$pl_temp[$key]=$val;
		}
		$pl_temp['createdAt']=$pl->createdAt;
		$pl_temp['updatedAt']=$pl->updatedAt;
		$playlist[]=$pl_temp;
	}
	
	return $playlist;
}

function nightbot_clear_playlist($user_id=NULL)
{
	// if no user id was sent to function, fetch user id of current logged in user
	if($user_id==NULL)
		$user_id=login_get_user();
	
	// Just stop if we have no user id at this point
	if($user_id==NULL)
		return FALSE;
	
	// Clear playlist
	$api=new rest_api_integration("nightbot", TRUE);
	$api->_delete(array("1","song_requests","playlist"));
}
?>