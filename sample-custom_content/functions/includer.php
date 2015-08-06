<?php

$path=CUSTOM_CONTENT_PATH."/functions";

require_all_in_dir($path);

function require_all_in_dir($path)
{
	foreach (glob($path."/*") as $filename)
	{
		if(is_dir($filename) && strcmp(substr($filename,strlen($filename)-1),"."))
		{
			require_all_in_dir($filename);
		}
		else if(!strcmp(substr($filename,strlen($filename)-4),".php"))
		{
			if(strcmp(CUSTOM_CONTENT_PATH."/functions/includer.php",$filename))
			{
				require_once($filename);
				// echo "<br />filename: ".$filename;
			}
		}
	}
}
?>