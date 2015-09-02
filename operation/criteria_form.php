<?php

require_once("op_includer.php");
$connection=db_connect(db_host, db_name, db_user, db_pass);

usermessage_criteria_form();

db_close($connection);
?>