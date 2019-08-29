<?php
@session_start(); // Ignore warnings about session already started when this is included by functions includer script

//Globals
// require_once("../../globals/db_info.php");
// require_once("../../globals/values.php");
// require_once("../../globals/path.php");
if(defined('ABS_PATH'))
	require_once(ABS_PATH."/config.php");
else
	require_once("../../config.php");

//From functions
require_once(ABS_PATH."/functions"."/include.php");
require_all_in_path(ABS_PATH."/functions");
// require_once(ABS_PATH."/functions"."/user.php");
// require_once(ABS_PATH."/functions"."/login.php");
// require_once(ABS_PATH."/functions"."/flattr.php");
// require_once(ABS_PATH."/functions"."/spam.php");
// require_once(ABS_PATH."/functions"."/string.php");
// require_once(ABS_PATH."/functions"."/language.php");
// require_once(ABS_PATH."/functions"."/html.php");
// require_once(ABS_PATH."/functions"."/comment/func.php");
// require_once(ABS_PATH."/functions"."/db_connect.php");
//From Feedback
require_once(ABS_PATH."/functions"."/feedback/func.php");

if(file_exists("../../".CUSTOM_CONTENT_PATH."/globals.php"))
	require_once("../../".CUSTOM_CONTENT_PATH."/globals.php");

language_setup();

//Connecta till databasen
$conn=db_connect(db_host, db_name, db_user, db_pass);
// echo $_GET['operation']." - ".$_GET['id'];

/**
 * Operations that can be run safely even if user is not logged in or has admin
**/
if($_GET['operation']=="assign")
{
	$feedback=new feedback($_GET['id']);
	if(!$feedback->assign_role($_GET['role'], $_GET['user_id']))
		preprint($feedback->db->error);
	feedback_assigned_show($_GET['id']);
}
/**
 * Operations that can ONLY be run safely if user IS logged in AND has admin
**/
else if(isset($_SESSION[PREFIX.'user_id']) && isset($_SESSION[PREFIX."inloggad"]) && $_SESSION[PREFIX."inloggad"]>=3)
{
	if(isset($_GET['operation']) && isset($_GET['id']))
	{
		if($_GET['operation']=="uncheckin")
		{
			feedback_set_not_checked_in($_GET['id']);
			f_op_display_new_feedback($_GET['id'],$_GET['div_id']);
		}
		else if($_GET['operation']=="unresolve")
		{
			feedback_set_unresolved($_GET['id']);
			f_op_display_new_feedback($_GET['id'],$_GET['div_id']);
		}
		else if($_GET['operation']=="not_implemented")
		{
			feedback_set_not_implemented($_GET['id']);
			f_op_display_new_feedback($_GET['id'],$_GET['div_id']);
		}
		else if($_GET['operation']=="feedback_accept")
		{
			feedback_set_accepted($_GET['id']);
			f_op_display_new_feedback($_GET['id'],$_GET['div_id']);
		}
		else if($_GET['operation']=="feedback_check_in")
		{
			feedback_set_checked_in($_GET['id']);
			f_op_display_new_feedback($_GET['id'],$_GET['div_id']);
		}
		else if($_GET['operation']=="feedback_resolve")
		{
			feedback_set_resolved($_GET['id']);
			f_op_display_new_feedback($_GET['id'],$_GET['div_id']);
		}
		else if($_GET['operation']=="feedback_unaccept")
		{
			feedback_set_unaccepted($_GET['id']);
			f_op_display_new_feedback($_GET['id'],$_GET['div_id']);
		}
		else if($_GET['operation']=="bugfix")
		{
			$sql="UPDATE ".PREFIX."feedback SET size=1 WHERE id=".sql_safe($_GET['id']).";";
			mysql_query($sql);
			feedback_display_size_buttons($_GET['id'], $_GET['div_id']);
		}
		else if($_GET['operation']=="required")
		{
			$sql="UPDATE ".PREFIX."feedback SET size=2 WHERE id=".sql_safe($_GET['id']).";";
			mysql_query($sql);
			feedback_display_size_buttons($_GET['id'], $_GET['div_id']);
		}
		else if($_GET['operation']=="small_improvement")
		{
			$sql="UPDATE ".PREFIX."feedback SET size=3 WHERE id=".sql_safe($_GET['id']).";";
			mysql_query($sql);
			feedback_display_size_buttons($_GET['id'], $_GET['div_id']);
		}
		else if($_GET['operation']=="big_change")
		{
			$sql="UPDATE ".PREFIX."feedback SET size=4 WHERE id=".sql_safe($_GET['id']).";";
			mysql_query($sql);
			feedback_display_size_buttons($_GET['id'], $_GET['div_id']);
		}
		else if($_GET['operation']=="merge" && isset($_GET['extra']))
		{
			$sql="UPDATE ".PREFIX."feedback SET merged_with=".sql_safe($_GET['extra'])." WHERE id=".sql_safe($_GET['id']).";";
			mysql_query($sql);
			if(feedback_get_is_accepted($_GET['id']))
				feedback_set_accepted($_GET['id']);
			feedback_display_merge_form($_GET['id'], $_GET['div_id']);
		}
		else if($_GET['operation']=="unmerge")
		{
			$sql="UPDATE ".PREFIX."feedback SET merged_with=NULL WHERE id=".sql_safe($_GET['id']).";";
			mysql_query($sql);
			feedback_display_merge_form($_GET['id'], $_GET['div_id']);
		}

	}
}	

if(isset($_GET['operation']) && isset($_GET['id']))
{
	if(isset($_GET['parent']))
		$parent=$_GET['parent'];
	else
		$parent=NULL;

	if($_GET['operation']=="expand")
	{

		feedback_display_specific_headline($_GET['id'], $_GET['div_id'], $_GET['parent'], TRUE);
	}
	else if($_GET['operation']=="colapse")
	{
		feedback_display_specific_headline($_GET['id'], $_GET['div_id'], $_GET['parent'], FALSE);
	}
}

mysql_close($conn);

function f_op_get_div_size($div_id)
{
	$parts=explode("_",$div_id);
	if($parts[1]=="big")
		return "big";
}

function f_op_get_display_id($div_id)
{
	$parts=explode("_",$div_id);
	return $parts[2];
}

function f_op_display_new_feedback($feedback_id, $target_div)
{
	if(f_op_get_display_id($target_div)!=$feedback_id)
		$the_new_id=feedback_get_main_parent($feedback_id);
	else
		$the_new_id=$feedback_id;

	$size=f_op_get_div_size($target_div);
	if($size=="big")
	{
		$ff=feedback_get_list_specific($the_new_id);
		feedback_list_print($ff, $feedback_id);
	}
	else
		feedback_display_specific_headline($the_new_id, $target_div, $target_div, TRUE);
}
 ?>