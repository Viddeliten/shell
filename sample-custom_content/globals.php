<?php

/********************************/
/*		Some strings			*/
/********************************/
define('FIRST_TIME_LOGIN_TEXT', _("Welcome, new member!"));

//The following texts will only be used if there is no index.php in custom content folder
define('SELLING_HEADLINE',_("Shell template"));
define('SELLING_TEXT',_("Shell website for use as base when creating awesome stuff.<br />Download, upload and fill with awesomeness!"));

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
																				_("Stuff 1")	=>	array(	"slug"	=>	"stuff1",
																											"req_user_level"	=>	1	//This will be seen by logged in people
																										),
																				_("Stuff 2")	=>	array(	"slug"	=>	"stuff2",
																											"req_user_level"	=>	2	//This will be seen by admins
																										),
																				_("Stuff 3")	=>	array(	"slug"	=>	"stuff3",
																											"req_user_level"	=>	0	//This will be seen by admins
																										)
																			)
												)
)));
?>