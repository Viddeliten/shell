<?php
require_once("op_includer.php");

$connection=db_connect(db_host, db_name, db_user, db_pass);

privmess_receive();

if(login_check_logged_in_mini()>0)
	privmess_display_inbox($_SESSION[PREFIX.'user_id']);

db_close($connection);
?>