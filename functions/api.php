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
				else
                {
                    $custom_result=api_custom();
                    if(!$custom_result)
                        echo "Unknown command";
                    else
                    {
                        //Print json encoded
                        echo json_encode($custom_result);
                    }
                }
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
	$custom_pages=unserialize(CUSTOM_PAGES_ARRAY);
	$logged_in_level=login_check_logged_in_mini();
	$show_feedback=true;
	if(isset($custom_pages["Feedback"]))
	{
		if($custom_pages["Feedback"]['req_user_level']>0 && $custom_pages["Feedback"]['req_user_level']>$logged_in_level)
			$show_feedback=false;
	}
	if(!$show_feedback)
	{
		return 0;
	}

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
	// feedback_get_sql($size, $nr, $offset=0, $only_unresolved=TRUE, $no_merged=TRUE)
	$sql=feedback_get_sql(SIZE_SUGGESTED, 
						sql_safe($nr_per_page), 
						(isset($_REQUEST['from']) ? sql_safe($_REQUEST['from']) : 0), //offset
						FALSE, //only_unresolved
						FALSE); //no_merged
	
	//Fetch array
	$feedback=sql_get($sql, true);
	
	//Print json encoded
	echo json_encode($feedback);
}
?>