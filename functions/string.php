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
    $html = string_curlurl($url, true);
    if(strlen($html)>0)
    {
        // Look for meta tag with property og:title
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        // Find all <meta property="og:title" content="[title]"
        $title="";
        foreach($xpath->query('//meta') as $item) 
        {				
            $property=$item->getAttribute('property');
            if(!strcmp($property,"og:title"))
            {
                $title = $item->getAttribute('content');
            }
        }
        if($title!="")
            return $title;
        
        // Look for contents of title tag
        $html = trim(preg_replace('/\s+/', ' ', $html)); // supports line breaks inside <title>
        preg_match("/\<title\>(.*)\<\/title\>/i",$html,$title); // ignore case
        if(isset( $title[1]))
            return $title[1];
    }
    return string_get_url_title($url);
}

function string_get_url_title($url)
{
	$parts=explode("/",$url);
	for($i=count($parts)-1; $i>=0 ; $i--)
	{
		if(!is_numeric($parts[$i]))
			return string_unslugify($parts[$i]);
	}
	return NULL;
}

function string_curlurl($url, $zipped=FALSE) {
    $handle = curl_init();
	
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	$strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';
	
	session_write_close();

	if($zipped)
		curl_setopt($handle, CURLOPT_ENCODING , "gzip");
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_POST, false);
    curl_setopt($handle, CURLOPT_BINARYTRANSFER, false);
    curl_setopt($handle, CURLOPT_HEADER, true);
	curl_setopt($handle, CURLOPT_USERAGENT, $useragent);
	curl_setopt($handle, CURLOPT_COOKIE, $strCookie );
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, 0);

    $response = curl_exec($handle);
    $hlength  = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    $body     = substr($response, $hlength);

    // If HTTP response is not 200, throw exception
    if ($httpCode == 0) {
        throw new Exception("Host not found");
	}
    else if ($httpCode != 200) {
        throw new Exception($httpCode);
    }

    return $body;
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
		$str=print_r($value,1);
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
	$text=str_ireplace("<p>","",$text);
	$text=str_ireplace("</p>","",$text);
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

//https://stackoverflow.com/questions/4128323/in-array-and-multidimensional-array?answertab=votes#tab-top
function in_array_r($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}

function string_remove_tags($string)
{
	return preg_replace('/<(.?)*>/', "", $string);
}

?>