<?php

// define('BOOTSTRAP_VERSION', "4.1.0"); // Uncomment to switch to newer bootstrap (experimental)

/********************************/
/*		Available pages			*/
/********************************/
//Slugs need to be untranslated or coder will have hell in case of many languages in translation!
define('CUSTOM_PAGES_ARRAY',serialize(array(
	//name	=>	content
	_("Page without sub pages")			=>	array(	"slug"				=>	"head_page",
													"req_user_level"	=>	0, //If 1, only logged in users can see this. If >=2 only users with this admin level can see this link.
																				//NOTE: This number only affects the visibility of the link. The contents has to be governed separately
													"meta_description"	=> "This page is an example of page without sub pages"
												),
	_("Admin tools")					=>	array(	"slug"				=>	"admin", //This will only affect the admin-menu! Subpages will be added to the regular admin menu.
																						// For this condition to be true, slug needs to be exactly "admin". req_user_level is not nessessary
													"subpages"		=>	array(
																				_("Things 1")	=>	array(	"slug"	=>	"things1",
																											"req_user_level"	=>	5	//This will be seen by only highest level of admin
																										),
																				_("Things 2")	=>	array(	"slug"	=>	"things2",
																											"req_user_level"	=>	2	//This will be seen by admins
																										)
																			)
												),
	_("Things")							=>	array(	"slug"	=>	"things",
													"req_user_level"	=>	1,
													"subpages"		=>	array(
																				_("Things 1")	=>	array(	"slug"	=>	"things1",
																											"req_user_level"	=>	1	//This will be seen by logged in people
																										),
																				_("Things 2")	=>	array(	"slug"	=>	"things2",
																											"req_user_level"	=>	2	//This will be seen by admins
																										)
																			)
												),
	_("Users")							=>	array(	"slug"	=>	"users",
													"req_user_level"	=>	0,
													"subpages"		=>	array(
																				_("Active users")	=>	array(	"slug"	=>	"active",
																											"req_user_level"	=>	0	//This will be seen by all
																										),
																				_("Friends")	=>	array(	"slug"	=>	"friends",
																											"req_user_level"	=>	1	//This will be seen by logged in users
																										)
																			)
												)
)));

/********************************/
/*		Extra settings			*/
/********************************/
define('CUSTOM_SETTINGS',serialize(array(
	"flattr"	=>	array(	"some_object" => _("Some object")),
	"Notifications on"	=>	array(	"comments" => _("Comments")), // If you keep this, make sure to add a message under Admin tools messages for new comments!
	"My custom setting"	=>	array(	"some_setting" => _("Some setting you might want to have"),
									"some_other_setting" => _("Some OTHER setting")
								)
)));
define('NUMBER_OF_EMAIL_NOTIFY',100); //How many emails usermessage system is allowed to sent each hour

//uncomment to remove "This site is presented by" (but not the ViddeWebb logo)
// define('NotPresented',"yes");

/****************************************************/
/*		Optional properties to add to body tag		*/	
/****************************************************/
define('BODY_PROPERTIES', "");
?>