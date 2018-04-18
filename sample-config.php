<?php

/************************************************************************************************************/
/*	for swedish translation:																				*/
/* --------------------------																				*/
/* In root, outside of custom_content,																		*/
/* generate pot-file that can be merged with po-file to generate mo-file (2 commands):						*/
/*	xgettext --from-code=UTF-8 -o texts-sv.pot *.php														*/
/*	find . -iname "*.php" | xargs xgettext --from-code=UTF-8 -k_e -k_x -k__ -o custom_content/translations/default.pot
/*																											*/
/*	Download po and pot, update from POT file, upload po													*/
/*																											*/
/* Then to merge from shell translations, do (1 command):													*/
/*	msgcat sample-translations/sv_SE/LC_MESSAGES/sv_SE.po custom_content/translations/sv_SE/LC_MESSAGES/sv_SE.po -o custom_content/translations/sv_SE/LC_MESSAGES/sv_SE.po --use-first
/************************************************************************************************************/
// define('DEFAULT_LANGUAGE', 'sv_SE'); //Swedish
define('DEFAULT_LANGUAGE', 'en_GB');	//Brittish

if(!defined('ABS_PATH'))
	define('ABS_PATH',"/var/www/catalog_name");

/********************************/
/*		Other constants			*/
/********************************/
define('PREFIX',"");
define('CONTACT_EMAIL','your@email.se');
define('SITE_URL','http://your.site.se');
define('SITE_NAME','Example Site');

define('USER_MESSAGE_MAIL_SENDING', FALSE); //Set this to true if you have tested and want to send emails by user message

/********************************/
/*			database			*/
/********************************/
define("db_pass", "");
define("db_user", "");
define("db_host", "localhost"); //If database is on same server as web files, leave this line as it is.
define("db_name", "");

/********************************************************************************************/
/*		Globals for captcha																	*/
/*	Go to https://www.google.com/recaptcha/intro/index.html and get these codes	(v2)		*/
/********************************************************************************************/
define("ReCaptcha_privatekey",""); // Secret key
define("ReCaptcha_publickey",""); // Site key

/********************************/
/*			Flattr				*/
/********************************/
define('SITE_OWNER_FLATTR_ID', "");
define('FLATTR_META_TAG','<meta name="flattr:id" content="someflattrcode">');

/********************************/
/*		Google analytics		*/
/********************************/
//The code you get from Google Analytics to include in all pages ou want to track
define("GoogleAnalyticsCode","");

/********************************/
/*		Pingdom					*/
/********************************/
// If you have code from Pingdom, put it here
define('PINGDOM_SCRIPT', "");

/********************************/
/*		Some strings			*/
/********************************/
define('FIRST_TIME_LOGIN_TEXT', _("Welcome, new member!"));
define('SELLING_HEADLINE',_("Example template"));
define('SELLING_TEXT',_("Insert your selling text here.<br>It should be snappy."));

//////////////////////////////////////////
/*		That's it, stop editing!		*/
//////////////////////////////////////////

/********************************/
/*	path to custom content		*/
/********************************/
if(!defined('ROOT_PATH'))
	define('ROOT_PATH',"");

if(!defined('CUSTOM_CONTENT_PATH'))
{
	if(file_exists(ROOT_PATH."custom_content"))
		define('CUSTOM_CONTENT_PATH',ROOT_PATH."custom_content");
	else
		define('CUSTOM_CONTENT_PATH',ROOT_PATH."sample-custom_content");
}

if(file_exists(CUSTOM_CONTENT_PATH."/translations"))
	define('CUSTOM_TRANSLATION_PATH',CUSTOM_CONTENT_PATH."/translations");
else if(file_exists(ROOT_PATH."translations"))
	define('CUSTOM_TRANSLATION_PATH',ROOT_PATH."translations");
else
	define('CUSTOM_TRANSLATION_PATH',ROOT_PATH."sample-translations");

?>