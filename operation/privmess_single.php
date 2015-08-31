<?php
require_once("op_includer.php");

  $connection=db_connect(db_host, db_name, db_user, db_pass);
  
  privmess_receive();

  privmess_display_single_message($_GET['message_id']);

  db_close($connection);
?>