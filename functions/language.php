<?php

function language_setup()
{
	if(isset($_GET['language']))
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
			ini_set('default_socket_timeout', 1);
			$l=unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$_SERVER['REMOTE_ADDR']));
			ini_set('default_socket_timeout', 30);
			// echo "<pre>".print_r($l,1)."</pre>";
			if(empty($l))
			{
				if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
					$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
				else
					$lang = "";
				switch ($lang){
					case "sv":
					case "no":
					case "dk":
						$language="sv_SE";
						break;        
					default:
						$language=DEFAULT_LANGUAGE;
						break;
				}
			}
			else if(!strcmp($l['geoplugin_countryCode'],"SE"))
				$language="sv_SE";
			else if(!strcmp($l['geoplugin_countryCode'],"GB"))
				$language="en_GB";
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
?>