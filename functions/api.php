<?php
require_once("config.php"); 
require_once("functions/feedback/func.php");
	require_once("functions/db_connect.php");
	require_once("functions/string.php");
	require_once("functions/language.php");

if(file_exists(CUSTOM_CONTENT_PATH."/globals.php"))
	require_once(CUSTOM_CONTENT_PATH."/globals.php");

if(isset($_REQUEST['p']) && isset($_REQUEST['s']) && !strcmp(strtolower($_REQUEST['p']),"api"))
{
	//This is an api call. Do stuff!

	language_setup();

	//Connecta till databasen
	$conn=db_connect(db_host, db_name, db_user, db_pass);

	
	switch($_REQUEST['s'])
	{
		case "feedback":
			api_feedback();
		break;
		default:
			echo "Unknown command";
	}
	
	db_close($conn);
}

function api_feedback()
{
	$feedback=feedback_get_array((isset($_REQUEST['from']) ? $_REQUEST['from'] : 0), (isset($_REQUEST['to']) ? $_REQUEST['to'] : 10));
	echo serialize($feedback);
}
?>