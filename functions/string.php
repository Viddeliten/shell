<?php

if(!function_exists("sql_safe"))
{
	function sql_safe($str)
	{
		$str=mysql_real_escape_string($str);
		$str = preg_replace('/\\x[a-f0-9][a-f0-9]/',"hex",$str); // Some weird things are going on on the internetz
		return $str;
	}
}
if(!function_exists("mysql_real_escape_string"))
{
    function mysql_real_escape_string($str)
    {
        return addslashes($str);
    }
}

function number_safe($number, $decimals=20)
{
    return number_format($number, $decimals, ".", "");
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

function string_get_tag_content_from_url($url, $tag="ul", $attribute=array("class"=>"post-tags"))
{
	$result=array();
	try
	{
		$html = string_curlurl($url, TRUE); // Using curl and gzip in case the site is zipped (like TSR fex)
	} catch (Exception $e) {
		trigger_error("Could not get html : ".prestr($e)); // Trigger warning for log
		preprint($e);
	}
	if(isset($html))
	{
		libxml_use_internal_errors(true);
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);

		// Find all images in img tags
		foreach($xpath->query('//'.$tag) as $item) 
		{
			$result[] =  $item->getAttribute($attribute);
		}
	}
	return $result;
}

function string_get_tag_attribute_from_url($url, $tag="meta", $required_attribute=array("name" => "keywords"), $attribute="content")
{
	$result=array();
	try
	{
		$html = string_curlurl($url, TRUE); // Using curl and gzip in case the site is zipped (like TSR fex)
	} catch (Exception $e) {
		trigger_error("Could not get html: ",$e); // Trigger warning for log
	}
	if(isset($html))
	{
		libxml_use_internal_errors(true);
		$dom = new DOMDocument();
		$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);

		// Find all images in img tags
		foreach($xpath->query('//'.$tag) as $item) 
		{
			foreach($required_attribute as $rq_attr => $rq_content)
			{
				$content =  $item->getAttribute($rq_attr);
				if(strcmp($content, $rq_content))
					continue(2);
			}
			$result_string=$item->getAttribute($attribute);
			if($result_string)
				$result[]=$result_string;
		}
	}
	return $result;
}


function string_get_title_from_url($url)
{
    try
    {
        $html = string_curlurl($url, TRUE); // Using curl and gzip in case the site is zipped (like TSR fex)
        // echo $html;
    } catch (Exception $e) {
        message_trigger_warning(461616, $url, $e); // Trigger warning for log
    }
    if(isset($html))
    {
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

            preg_match("/<title>([^<]*)<\/title>/i",$html,$title); // ignore case
            if(isset( $title[1]))
                return $title[1];
            preg_match("/<h1([^>]*?)>([^<]*)<\/h1>/i",$html,$title); // ignore case

            if(isset( $title[2]))
                return $title[2];
            preg_match("/<h2([^>]*?)>([^<]*)<\/h2>/i",$html,$title); // ignore case

            if(isset( $title[2]))
                return $title[2];
			}
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

function string_url_is_tumblr($url)
{
	if(preg_match('/^http[^.]*.tumblr.com/',$url))
		return TRUE;
	return FALSE;
}

/***
 * function string_curlurl
 * Parameters:
 **		$url				- The webb address to be processed
 **		$zipped				- If the target is zipped. Default: FALSE 
 **		$follow_redirects	- How many redirects to follow. Default: 3 
 **		$referer			- url to tell the target we came from. Default: SITE_URL 
 **		$send_cookie		- If we should send current cookie to the target. Default: FALSE
 * Returns: Body of url
 ***/
function string_curlurl($url, $zipped=FALSE, $follow_redirects=3, $referer=SITE_URL, $send_cookie=FALSE, &$redirected_url = NULL)
{
    $handle = curl_init();
	
	// $useragent = "Mozilla/".mt_rand(1,5).".0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/".mt_rand(1,66).".0.3359.139 Safari/537.36";
	// In case cron is running this, some pages don't like bots and this is what I had in my access log :)
	$useragent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36";
	if(isset($_SERVER['HTTP_USER_AGENT']))
		$useragent = $_SERVER['HTTP_USER_AGENT'];
	if(string_url_is_tumblr($url))
		$useragent='Mozilla/5.0 (compatible; Baiduspider; +http://www.baidu.com/search/spider.html)';
	
	if(isset($_COOKIE['PHPSESSID']))
		$strCookie = 'PHPSESSID=' . $_COOKIE['PHPSESSID'] . '; path=/';
	
	session_write_close();

	if($zipped)
		curl_setopt($handle, CURLOPT_ENCODING , "gzip");
		
    curl_setopt($handle, CURLOPT_URL, $url);
    curl_setopt($handle, CURLOPT_HEADER, true); // Needed if we want to find title tags and such (and we do most of the time)
    curl_setopt($handle, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($handle, CURLOPT_MAXREDIRS, $follow_redirects);
    curl_setopt($handle, CURLOPT_REFERER, $referer);
	curl_setopt($handle, CURLOPT_USERAGENT, $useragent);
	if($send_cookie && isset($strCookie))
		curl_setopt($handle, CURLOPT_COOKIE, $strCookie );
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 10);

    $response = curl_exec($handle);
    $hlength  = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    $redirected_url = curl_getinfo($handle, CURLINFO_EFFECTIVE_URL );
    $body     = substr($response, $hlength);

    // If HTTP response is not 200, throw exception
    if ($httpCode == 0) {
		throw new Exception(curl_error($handle));
	}
	else if($httpCode == 404) {
        throw new Exception("Host not found");
	}
    else if ($httpCode != 200) {
        throw new Exception($httpCode);
    }

    return $body;
}

function string_get_images_from_url($url)
{
	//https://stackoverflow.com/questions/43598781/scraping-images-from-url-using-php
	try
	{
		$html = string_curlurl($url, TRUE); // Using curl and gzip in case the site is zipped (like TSR fex)
		// echo $html;
	} catch (Exception $e) {
		message_trigger_warning(2491613, $url, $e); // Trigger warning for log
		return NULL;
	}
	$new_images=array();
	
	$main_site_url=string_get_base_url($url);
	
	libxml_use_internal_errors(true);
	$dom = new DOMDocument();
	$dom->loadHTML($html);
	$xpath = new DOMXPath($dom);

	// Find all <meta property="og:image" content="imgurl"  // Tumblr has this on my page at least (apparently it is some kind of web standard)
	foreach($xpath->query('//meta') as $item) 
	{				
		$property=$item->getAttribute('property');
		if(!strcmp($property,"og:image"))
		{
			$img_src =  $item->getAttribute('content');
			//If first char is "/", the add type url
			if(substr($img_src,0,2)=="//")
				$img_src="https:".$img_src;
			
			//If first char is "/", the add type url
			if(substr($img_src,0,1)=="/")
				$img_src=$main_site_url.$img_src;

			$img_alt="image";

			if(!in_array_r($img_src, $new_images))
			{
				$size=getimagesize($img_src);
				$new_images[]=array('src' => $img_src, 'alt' => $img_alt, 'size' => ($size[0]*$size[1]));
			}
		}
	}

	// Find all images in img tags
	foreach($xpath->query('//img') as $item) 
	{
		$img_src =  $item->getAttribute('src');
		$img_alt = $item->getAttribute('alt');
		if(!stristr($img_src,"avatar") && !stristr($img_src, "logo.png") && !stristr($img_src, "mariadb-badge") && !stristr($img_src, "icon") && !stristr($img_src, "magnifyglass.gif"))
		{
			//If first char is "/", the add type url
			if(substr($img_src,0,2)=="//")
				$img_src="https:".$img_src;
			
			//If first char is "/", the add type url
			if(substr($img_src,0,1)=="/")
				$img_src=$main_site_url.$img_src;
				
			if(!in_array_r($img_src, $new_images))
			{
				$size=getimagesize($img_src);
				$new_images[]=array('src' => $img_src, 'alt' => $img_alt, 'size' => ($size[0]*$size[1]));
			}
		}
	}
	// Wixsite hides images in code like "src":"\/\/static.wixstatic.com\/media\/81457a_670d51090f0e46aab578dfdaa22a8db8~mv2.jpg"
	if(stristr($url,"wixsite.com"))
	{
		preg_match_all('/"src":"([^"]*)"/i',$html,$wiximages);
		if(!empty($wiximages[1]))
		{
			foreach($wiximages[1] as $wim)
			{
				if(stristr($wim, "static.wixstatic.com"))
				{
					$img_src=str_replace("\\","",$wim);
				
					//If first char is "/", the add https:
					if(substr($img_src,0,2)=="//")
						$img_src="https:".$img_src;
													
					if(!in_array_r($img_src, $new_images))
					{
						$size=getimagesize($img_src);
						$new_images[]=array('src' => $img_src, 'size' => ($size[0]*$size[1]));
					}
				}
			}
		}
	}
	
	//Sort by size
	string_array_multisort($new_images, "size", SORT_DESC);
	return $new_images;
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
		if(!defined("DEBUG_PRINTS") || DEBUG_PRINTS==TRUE)
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
	preg_match("/^[a-zA-Z]+:\/\/[a-zA-Z0-9-_]*[\.[a-zA-Z0-9-_]*]*/", $adress, $matches);
	if(!empty($matches))
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

function string_replace_urls_with_word(&$the_text, $word="link")
{
	if(preg_match_all("/(\s|^|\n|\r)[a-zA-Z]+:\/\/[a-zA-Z0-9-_]*[\.[a-zA-Z0-9-_]*]*[a-zA-Z0-9-_\?=&\/\#]*($|\b)/", $the_text, $matches))
	{
		foreach($matches[0] as $m)
		{
			$the_text=str_replace(trim($m) ,$word, $the_text);
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

function array_merge_indexed($original_array, $added)
{
	foreach($added as $key => $add)
	{
		if(!isset($original_array[$key]))
			$original_array[$key]=$add;
	}
	return $original_array;
}

function string_remove_tags($string)
{
	$string=preg_replace('/<(.?)*>/', "", $string);
	
	$replacement = "";
    $pattern = "/<(.*?)$/";
    $string=preg_replace($pattern, $replacement, $string);
    $pattern = "/^(.*?)>/";
    $string=preg_replace($pattern, $replacement, $string);
	
	return $string;
}

function string_file_get_output($filepath, $return_html=true)
{
    ob_start();
    
    include($filepath);
    
    $contents = ob_get_contents();
	ob_end_clean();
	
	if($return_html)
		return $contents;
	else
		echo $contents;
}



?>