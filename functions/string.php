<?php

function sql_safe($str)
{
	$str=mysql_real_escape_string($str);
	return $str;
}

function password_generate($len)
{
	$str="abcdefghijklmnopqrstuvwxyz0123456789";
	$pass="";
	
	for($i=0;$i<$len;$i++)
	{
		$place=mt_rand(0,strlen($str));
		$ch=substr($str,$place,1);
		$pass.=$ch;
	}
	
	return $pass;
}

function curPageURL() {
 $pageURL = 'http';
 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return $pageURL;
}

function add_get_to_URL($get_name, $value, $url=NULL)
{
	if($url===NULL)
	{
		$pageURL = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
		{
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80")
		{
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
		}
		else
		{
			$pageURL .= $_SERVER["SERVER_NAME"];
		}
		$trailing_1=stristr($_SERVER["REQUEST_URI"],"/?",true);
		$trailing_2=stristr($_SERVER["REQUEST_URI"],"/?",false);
	}
	else
	{
		$t_url=explode("?",$url);
		$pageURL=rtrim($t_url[0], '/');
		$trailing_2=str_replace("&amp;","&",$t_url[1]);
	}
 
	if($trailing_2=="")
	{	
		$pageURL .= "/?".$get_name."=".$value;
	}
	else
	{
		$trailing_2=str_replace("/?","",$trailing_2);
		$tr=explode("&",$trailing_2);
		
		$add=array();
		foreach($tr as $t)
		{
			if(strcmp(stristr($t,"=",true),$get_name))
				$add[]=$t;
		}
		$pageURL .= "/?".implode("&amp;",$add)."&amp;".$get_name."=".$value;
	}
	
	return $pageURL;
}

function string_slugify($text)
{
  // replace non letter or digits by -
  $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

  // trim
  $text = trim($text, '-');

  // transliterate
  $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

  // lowercase
  $text = strtolower($text);

  // remove unwanted characters
  $text = preg_replace('~[^-\w]+~', '', $text);

  if (empty($text))
  {
    return 'n-a';
  }

  return $text;
}
function string_unslugify($text)
{
	return ucfirst(str_replace("_"," ",$text));
}

function preprint($value, $label="")
{
	echo $label.prestr($value);
}
function prestr($value)
{
	return "<pre>".print_r($value,1)."</pre>";
}

?>