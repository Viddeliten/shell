<?php
	foreach (glob(CUSTOM_CONTENT_PATH."/functions/*.php") as $filename)
	{
		if(strcmp(CUSTOM_CONTENT_PATH."/functions/includer.php",$filename))
			require_once($filename);
			// echo "<br />filename: ".$filename;
	}
?>