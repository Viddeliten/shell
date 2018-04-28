<?php

function include_all_in_path($path)
{
	require_all_in_path($path);

}

function require_all_in_path($path)
{
	foreach (glob($path."/*") as $filename)
	{
		if(is_dir($filename) && strcmp(substr($filename,strlen($filename)-1),"."))
		{
			require_all_in_path($filename);
		}
		else if(!strcmp(substr($filename,strlen($filename)-4),".php"))
		{
			$not_to_include=array(	"functions/include.php",
									"functions/feedback/operation.php");
			if(!in_array($filename, $not_to_include))
			{
				require_once($filename);
				// echo "<br />filename: ".$filename;
			}
		}
	}
}

?>