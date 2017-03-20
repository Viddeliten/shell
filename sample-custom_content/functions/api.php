<?php

function api_custom()
{
	switch($_REQUEST['s'])
	{
		case "custom":
			echo "custom api function";
		break;
		default:
			return 0;
	}
	return TRUE;
}

?>