<?php
session_start();

//These files should never be included, so we know where we are.
define('ROOT_PATH',"../");

require_once(ROOT_PATH."functions/db_connect.php");
require_once(ROOT_PATH."functions/login.php");
require_once(ROOT_PATH."functions/string.php");
require_once(ROOT_PATH."functions/message.php");
require_once(ROOT_PATH."functions/usermessage.php");
require_once(ROOT_PATH."functions/language.php");

if(file_exists(ROOT_PATH."custom_content"))
	define('CUSTOM_CONTENT_PATH',ROOT_PATH."custom_content");
else
	define('CUSTOM_CONTENT_PATH',ROOT_PATH."sample-custom_content");

if(file_exists(CUSTOM_CONTENT_PATH."/functions/includer.php"))
	require_once(CUSTOM_CONTENT_PATH."/functions/includer.php");
if(file_exists(CUSTOM_CONTENT_PATH."/globals.php"))
	require_once(CUSTOM_CONTENT_PATH."/globals.php");

require_once(ROOT_PATH."config.php");
language_setup();
  
?>