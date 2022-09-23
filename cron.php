<?php

// stolen from op.php
define('ROOT_PATH',"./");
require_once(ROOT_PATH."operation/op_includer.php");
if(!isset($connection))
    $connection=db_connect(db_host, db_name, db_user, db_pass);


// Wrap custom content cron if exists
if(file_exists(CUSTOM_CONTENT_PATH."/cron.php"))
{
    include(CUSTOM_CONTENT_PATH."/cron.php");
}

?>