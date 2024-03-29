<?php

// this file is typically called by html_replace calls

/* File Created 2016-11-26

This is a more general files than the ones in the operation folder. 
And it includes op.php in custom content if it exists, wich saves the time of copying include code.
Also, the call url will be shorter. */

if(!defined('ROOT_PATH'))
    define('ROOT_PATH',"./");
// define('FUNC_PATH',"../dev.common.viddewebb.se/");
require_once(ROOT_PATH."operation/op_includer.php");
if(!isset($connection))
    $connection=db_connect(db_host, db_name, db_user, db_pass);

// preprint($_REQUEST);

switch($_REQUEST['f'])
{
	case "":
		echo _("No command");
		break;
	case "switch":
		$parameters=(array) json_decode($_REQUEST['parameters']);
		echo call_user_func_array("html_ajax_div_switcher", $parameters);
		break;
	default:
		if(file_exists(CUSTOM_CONTENT_PATH."/op.php"))
			include(CUSTOM_CONTENT_PATH."/op.php");
		else
			echo _("There is no action here");
		break;
}

db_close($connection);

?>