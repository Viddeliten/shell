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
require_once(ROOT_PATH."functions/news.php");
require_once(ROOT_PATH."functions/rss_class.php");
require_once(ROOT_PATH."functions/privmess.php");
require_once(ROOT_PATH."functions/user.php");

require_once(ROOT_PATH."config.php");

if(file_exists(CUSTOM_CONTENT_PATH."/functions/includer.php"))
	require_once(CUSTOM_CONTENT_PATH."/functions/includer.php");
if(file_exists(CUSTOM_CONTENT_PATH."/globals.php"))
	require_once(CUSTOM_CONTENT_PATH."/globals.php");

language_setup();
?>