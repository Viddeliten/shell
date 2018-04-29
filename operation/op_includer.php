<?php
session_start();

//These files should never be included, so we know where we are.
if(!defined('ROOT_PATH'))
	define('ROOT_PATH',"../");

require_once(ROOT_PATH."config.php");
if(defined("FUNC_PATH"))
	require_once(FUNC_PATH."config.php");

require_once(ROOT_PATH."functions/include.php");
include_all_in_path(ROOT_PATH."functions");

if(file_exists(CUSTOM_CONTENT_PATH."/functions/includer.php"))
	require_once(CUSTOM_CONTENT_PATH."/functions/includer.php");
if(file_exists(CUSTOM_CONTENT_PATH."/globals.php"))
	require_once(CUSTOM_CONTENT_PATH."/globals.php");

language_setup();
?>