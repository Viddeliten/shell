<?php

define('ROOT_PATH',"./");
require_once("operation/op_includer.php");

if(isset($_REQUEST['p']) && isset($_REQUEST['s']) && !strcmp(strtolower($_REQUEST['p']),"api"))
{
	//This is an api call. Do stuff!

	language_setup();

	//Connecta till databasen
	$conn=db_connect(db_host, db_name, db_user, db_pass);

	header('Content-Type: application/json'); //We always send json type things, right?
	
	switch($_REQUEST['s'])
	{
		case "feedback":
			api_feedback();
		break;
		default:
			if(!function_exists("api_custom"))
				echo "Unknown command";
			else if(!api_custom())
				echo "Unknown command";
	}
	
	db_close($conn);
}

function api_feedback()
{
	$feedback=feedback_get_array((isset($_REQUEST['from']) ? $_REQUEST['from'] : 0), (isset($_REQUEST['to']) ? $_REQUEST['to'] : 5));
	echo json_encode($feedback);
}
?>