<?php

require_once("op_includer.php");

if(isset($_GET[1]))
	$id_nr=$_GET[1];
else
	$id_nr=0;

// echo preprint($_GET);

if(isset($_GET['criteria_name']))
	usermessage_criterias_form($id_nr, $_GET['criteria_name']);
else
	usermessage_criterias_form($id_nr);
	
?>