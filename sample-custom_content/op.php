<?php

// this file is typically called by html_replace calls

switch($_REQUEST['f'])
{
	case "":
		echo _("No command");
		break;
	default:
		echo _("There is no action here");
		break;
}

?>