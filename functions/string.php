<?php

if(!function_exists("sql_safe"))
{
	function sql_safe($str)
	{
		$str=mysql_real_escape_string($str);
		return $str;
	}
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

function string_get_link_from_url($url, $get_title=true)
{
	$link_text="link";
	if($get_title)
		$link_text=string_get_title_from_url($url);
	if($link_text=="")
		$link_text=_("link");
	if(strlen($link_text>100))
		$link_text=substr($link_text,0,100);
	return '<a href="'.trim($url).'">'.$link_text.'</a>';
}

function string_get_title_from_url($url)
{
  $str = file_get_contents($url);
  if(strlen($str)>0){
    $str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
    preg_match("/\<title\>(.*)\<\/title\>/i",$str,$title); // ignore case
    return $title[1];
  }
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

function add_get_to_current_URL($get_name, $value)
{
	$base_url=string_get_base_url(curPageURL());

	$gets=array($get_name	=>	$value);
	foreach($_GET as $name => $val)
	{
		if(strcmp($name,$get_name))
			$gets[$name]=$val;
	}
	$return=$base_url;
	if(isset($gets['p']))
	{
		$return.="/".$gets['p'];
		unset($gets['p']);
		if(isset($gets['s']))
		{
			$return.="/".$gets['s'];
			unset($gets['s']);
			if(isset($gets['id']))
			{
				$return.="/".$gets['id'];
				unset($gets['id']);
			}
		}
	}
	if(!empty($gets))
	{
		$return.="?";
		$r=array();
		foreach($gets as $name => $val)
		{
			$r[]=$name."=".$val;
		}
		$return.=implode("&amp;",$r);
	}
	return $return;
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
	$text=str_replace("-"," ",$text);
	return ucfirst(str_replace("_"," ",$text));
}

if(!function_exists("preprint"))
{
	function preprint($value, $label="")
	{
		echo prestr($value, $label);
	}
}
if(!function_exists("prestr"))
{
	function prestr($value, $label="")
	{
		// return $label."<pre>".str_replace("\n","<br />",print_r($value,1))."</pre>";
		$str=print_r($array,1);
		$str=str_replace("<","&lt;",$str);
		$str=str_replace(">","&gt;",$str);
		$str="<pre>".$str."</pre>";
		if($label!==NULL)
			$str=$label.$str;
		return str_replace("\n","<br />", $str);
	}
}
//Get user defined constants
function string_get_defined_constants()
{
	$const=get_defined_constants(true);
	$r=array();
	foreach($const['user'] as $key => $val)
		$r[]=$key;
	return $r;
}

function string_get_base_url($adress)
{
	if(preg_match_all("/^[a-zA-Z]+:\/\/[a-zA-Z0-9-_]*[\.[a-zA-Z0-9-_]*]*$/", $adress, $matches))
		return $matches[0];
	return NULL;
}

function string_replace_urls_with_links(&$the_text, $get_link_titles=false)
{
	if(preg_match_all("/(\s|^|\n|\r)[a-zA-Z]+:\/\/[a-zA-Z0-9-_]*[\.[a-zA-Z0-9-_]*]*[a-zA-Z0-9-_\?=&\/\#]*($|\b)/", $the_text, $matches))
	{
		foreach($matches[0] as $m)
		{
			$the_text=str_replace(trim($m) ,string_get_link_from_url(trim($m), $get_link_titles),$the_text);
		}
	}
}

function string_break_long_words(&$text)
{
	if(preg_match_all("/[\S]{32}/",$text, $matches))
	{
		foreach($matches[0] as $m)
		{
			$new_word=$m;
			for($i=1;$i*32+$i-1<strlen($m);$i++)
			{
				$new_word=substr($new_word,($i-1)*32,32)."\n".substr($new_word,$i*32,strlen($new_word)-$i*32);
			}
			$text=str_replace($m,$new_word,$text);
		}		
	}
}

function string_html_to_text($text)
{
	$text=str_ireplace("</p><p>","<br /><br />",$text);
	$text=str_ireplace("<br />","\n",$text);
	return $text;

}

function string_array_multisort(&$array, $sort_by, $order=SORT_ASC)
{
	// array_multisort($sort1, SORT_DESC,
			// $sort2, SORT_DESC, 
			// $horses);
	$sortarr=array();
	foreach($array as $a)
	{
		$sortarr[]=$a[$sort_by];
	}
	array_multisort($sortarr, $order,
			$array);
}


?>