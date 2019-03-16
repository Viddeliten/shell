<?php

function language_setup($override_language=NULL)
{
	if($override_language!=NULL)
	{
		switch($override_language)
		{
			case "sv":
			case "se":
				$language="sv_SE";
				break;
			case "en":
			case "uk":
				$language="en_GB";
				break;
			default:
				$language="sv_SE";
		}
	}
	else if(isset($_GET['language']))
	{
		//User has selected language
		if(!strcmp($_GET['language'],"sv"))
			$language="sv_SE";
		else if(!strcmp($_GET['language'],"uk"))
			$language="en_GB";
	}
	else
	{
		//Try to fetch preffered
		if(isset($_SESSION['language']))
		{
			$language=$_SESSION['language'];
		}
		else
		{
			if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
				$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
			else
			{
				ini_set('default_socket_timeout', 1);
				$l=unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$_SERVER['REMOTE_ADDR']));
				ini_set('default_socket_timeout', 30);
				if(empty($l))
					$lang = "";
				else if(!strcmp($l['geoplugin_countryCode'],"SE"))
					$lang="sv";
				else if(!strcmp($l['geoplugin_countryCode'],"GB"))
					$lang="en";
			}
			switch ($lang){
				case "en":
					$language="en_GB";
					break;
				case "sv":
				case "no":
				case "dk":
					$language="sv_SE";
					break;        
				default:
					$language=DEFAULT_LANGUAGE;
					break;
			}
			// echo $language;
			ini_set('default_socket_timeout', 15);
		}
	}
	
	if(!isset($language))
	{
		$language=DEFAULT_LANGUAGE;
	}

	if(isset($language))
		language_save_selected($language);
		
	$locale=$language.".UTF-8";
	
  putenv("LC_ALL=" . $locale); 
  setlocale(LC_ALL, $locale);

  $domain = $language;
  bindtextdomain($domain, "./".CUSTOM_TRANSLATION_PATH); 
  bind_textdomain_codeset($domain, 'UTF-8');
  textdomain($domain);
}

function language_save_selected($language)
{
	//Save in session
	$_SESSION['language']=$language;
	
	//Save in db
}

function language_get_choice()
{
	if(isset($_SESSION['language']) && !strcmp($_SESSION['language'],"sv_SE"))
		return "se";
	else
		return "en";
}
?>