<?php

function api_call_handle()
{
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

}

//Function to retrieve data from api
function api_get($url)
{
	return json_decode(file_get_contents($url), true);
}


function api_feedback()
{
	//Calculate number per page
	$nr_per_page=5; //Default number
	if(isset($_REQUEST['to']))
	{
		if(isset($_REQUEST['from']))
		{
			$nr_per_page=$_REQUEST['to']-$_REQUEST['from'];
		}
		else
			$nr_per_page=$_REQUEST['to'];
	}
	
	//Get sql
	$sql=feedback_get_sql(SIZE_SUGGESTED, $nr_per_page, (isset($_REQUEST['from']) ? $_REQUEST['from'] : 0));
	
	//Fetch array
	$feedback=sql_get($sql, true);
	
	//Print json encoded
	echo json_encode($feedback);
}
?>