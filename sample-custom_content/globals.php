<?php

/********************************/
/*		Available pages			*/
/********************************/
//Slugs need to be untranslated or coder will have hell in case of many languages in translation!
define('CUSTOM_PAGES_ARRAY',serialize(array(
	//name	=>	content
	_("Page without sub pages")			=>	array(	"slug"				=>	"head_page",
													"req_user_level"	=>	0 //If 1, only logged in users can see this. If >=2 only users with this admin level can see this link.
																				//NOTE: This number only affects the visibility of the link. The contents has to be governed separately
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
	_("Stuff")							=>	array(	"slug"	=>	"stuff",
													"req_user_level"	=>	0,
													"subpages"		=>	array(
																				_("Active users")	=>	array(	"slug"	=>	"users",
																											"req_user_level"	=>	0	//This will be seen by all
																										),
																				_("Stuff 2")	=>	array(	"slug"	=>	"stuff2",
																											"req_user_level"	=>	2	//This will be seen by admins
																										),
																				_("Stuff 3")	=>	array(	"slug"	=>	"stuff3",
																											"req_user_level"	=>	0	
																										)
																			)
												)
)));

/********************************/
/*		Extra settings			*/
/********************************/
define('CUSTOM_SETTINGS',serialize(array(
	"flattr"	=>	array(	"some_object" => _("Some object"))
	)));
?>