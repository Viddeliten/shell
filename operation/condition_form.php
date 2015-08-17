<?php

require_once("op_includer.php");

if(isset($_GET[1]))
	$id_nr=$_GET[1];
else
	$id_nr=0;

usermessage_criterias_form($id_nr);
	
?>