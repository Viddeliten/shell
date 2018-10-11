<?php
/*	Function name:		link_get_custom_comment_link_url
/*	Purpose:			Provide links to specific comments under things that is not part of shell structure
/*	Parameters:			$comment_id		Id of the comment
/*						$comment_type	The type on wich the comment is on. This is just a string
/*						$comment_on		The id on the thing the comment is on
*/
function link_get_custom_comment_link($comment_id, $comment_type, $comment_on)
{
	$url=link_get_custom_comment_link_url($comment_id, $comment_type, $comment_on, $linktitle);

	if($url!==NULL)
		return html_link($url, $linktitle);

	return NULL;
}
function link_get_custom_comment_link_url($comment_id, $comment_type, $comment_on, &$linktitle)
{
	// This is just an example from Dreamhorse.se :
	if(!strcmp($comment_type,"stable"))
	{
		$linktitle=sprintf(_("Comment on stable %s"), stable_get_name($comment_on));
		return stable_get_link_url($comment_on)."&amp;comment#anchor_comment_".$comment_id;
	}
	return NULL;
}

?>