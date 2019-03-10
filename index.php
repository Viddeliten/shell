<?php

//Check for api call first
require_once("functions/api.php");
if(isset($_REQUEST['p']) && isset($_REQUEST['s']) && !strcmp(strtolower($_REQUEST['p']),"api"))
{
	api_call_handle();
	return TRUE;
}
?><!DOCTYPE html>
<html lang="en">
<?php session_start(); ?>
<?php setlocale(LC_NUMERIC, 'en_US'); ?>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	 <meta name="author" content="">
	<?php require_once("config.php"); 

	require_once("functions/include.php");
	include_all_in_path("functions");

	language_setup();

	if(file_exists(CUSTOM_CONTENT_PATH."/globals.php"))
		require_once(CUSTOM_CONTENT_PATH."/globals.php");

	if(file_exists(CUSTOM_CONTENT_PATH."/functions/includer.php"))
		require_once(CUSTOM_CONTENT_PATH."/functions/includer.php");

	$connection=db_connect(db_host, db_name, db_user, db_pass);
	
	if(isset($_GET['p']) && isset($_GET['s']) && !strcmp($_GET['p'],"oauth"))
	{
		$login_oauth=unserialize(LOGIN_OAUTH);
		if(isset($login_oauth[$_GET['s']]))
		{			
			if(isset($_REQUEST['code']))
				login_oath($_GET['s'], $login_oauth[$_GET['s']]["base_uri"], $login_oauth[$_GET['s']]["auth_uri"], $login_oauth[$_GET['s']]["auth_parameters"]);
			else
				header('Location: '.$login_oauth[$_GET['s']]["302_uri"], true, 302);
		}
	}

	if(function_exists("flattr_custom_page_tag") && $flattr_tag=flattr_custom_page_tag())
		echo $flattr_tag;
	else if(defined('FLATTR_META_TAG'))
		echo FLATTR_META_TAG;

	if(function_exists('meta_title_and_description')) meta_title_and_description(); ?>
   
   <?php // Use https://realfavicongenerator.net to get favicons, and put them in img/icon under custom content folder
   echo '<link rel="apple-touch-icon" sizes="180x180" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/favicon-16x16.png">
<link rel="manifest" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/manifest.json">
<link rel="mask-icon" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/safari-pinned-tab.svg" color="#5bbad5">
<link rel="shortcut icon" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/favicon.ico">
<meta name="msapplication-config" content="/'.CUSTOM_CONTENT_PATH.'/img/icon/browserconfig.xml">
<meta name="theme-color" content="#ffffff">'; 

if(defined('BOOTSTRAP_VERSION'))
    $bootstrap_version=BOOTSTRAP_VERSION;
else
    $bootstrap_version="3.3.4";
?>
	<!-- OPENICONIC https://useiconic.com/open/ -->
	<link href="<?php echo SITE_URL; ?>/open-iconic/font/css/open-iconic-bootstrap.css" rel="stylesheet">
    
    <!-- Bootstrap core CSS -->
    <link href="<?php echo SITE_URL; ?>/bootstrap-<?php echo $bootstrap_version; ?>-dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Shell template style -->
    <?php if(!strcmp("4.1.0", $bootstrap_version)) { ?>
    <link href="<?php echo SITE_URL; ?>/style_bootstrap410.css" rel="stylesheet"> <?php } else { ?>
    <link href="<?php echo SITE_URL; ?>/style_bootstrap334.css" rel="stylesheet"><?php } ?>

	<!-- Custom style -->
    <link href="<?php echo SITE_URL."/".CUSTOM_CONTENT_PATH; ?>/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
   
  <?php  

  login_receive();
  
  
  //Include custom content
  if(file_exists(CUSTOM_CONTENT_PATH."/receive.php"))
	  require_once(CUSTOM_CONTENT_PATH."/receive.php");
  
  feedback_recieve();
  comment_receive();
  user_receive();
  version_receive(); 
  news_receive(); 
  usermessage_receive();
  notice_receive();
  privmess_receive();
  spam_receive();

  if(isset($_SESSION[PREFIX.'user_id']))
	usermessage_check_messages($_SESSION[PREFIX.'user_id']);

	if(defined('HEADER_CONTENT'))
		echo HEADER_CONTENT;
?>
	
  </head>

  <body <?php echo BODY_PROPERTIES; ?>>
    <?php 
	if(file_exists(CUSTOM_CONTENT_PATH."/index.php"))
	{
		include(CUSTOM_CONTENT_PATH."/index.php");
	}
    else
	{
        display_topline_menu();
        echo '<div class="container" id="main_container">
			<div id="content">';
        display_conditional_login();
        
        include("content.php");
        echo '<div class="clearfix"></div>
            </div>';
        //Footer
        display_footer();
        echo '</div><!-- /.container -->';
    }
       
	// usermessage_check_messages();
	db_close($connection); ?>
	
	<!-- Flattr-stuff: -->

<script type="text/javascript">
/* <![CDATA[ */
    (function() {
        var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
        s.type = 'text/javascript';
        s.async = true;
        s.src = '//api.flattr.com/js/0.6/load.js?mode=auto';
        t.parentNode.insertBefore(s, t);
    })();
/* ]]> */ 
</script>
<!-- Slut Flattr-stuff -->

<!-- Google Analytics -->
<?php echo GoogleAnalyticsCode; ?>
<!-- End Google Analytics -->

<?php //Pingdom script
if(defined('PINGDOM_SCRIPT'))
	echo PINGDOM_SCRIPT; ?>
	
	<script src="//code.jquery.com/jquery-2.1.0.min.js"></script>

	<script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/"; ?>functions/functions.js?v=<?php echo date("YmdHis"); ?>"></script>
	<script src="//viddewebb.se/_commons/js/basic.js"></script>

	<?php if(file_exists(CUSTOM_CONTENT_PATH.'/functions/java.js')) { ?>
		<script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/".CUSTOM_CONTENT_PATH; ?>/functions/java.js?v=<?php echo time(); ?>"></script> <?php } else echo "<!--".CUSTOM_CONTENT_PATH.'/functions/java.js does not exist -->'; ?>
	<script src='https://www.google.com/recaptcha/api.js'></script>
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/"; ?>bootstrap-<?php echo $bootstrap_version; ?>-dist/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <?php if(!strcmp("3.3.4", $bootstrap_version)) { ?>
    <script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/"; ?>bootstrap-<?php echo $bootstrap_version; ?>-dist/js/ie10-viewport-bug-workaround.js"></script> <?php } else { ?>

    <script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/"; ?>bootstrap-<?php echo $bootstrap_version; ?>-dist/js/bootstrap.bundle.js"></script>
    <script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/"; ?>bootstrap-<?php echo $bootstrap_version; ?>-dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/"; ?>bootstrap-<?php echo $bootstrap_version; ?>-dist/js/bootstrap.js"></script> <?php } ?>

    <?php if(file_exists(CUSTOM_CONTENT_PATH."/functions/functions.js")) { ?>
        <script src="<?php echo SITE_URL."/".CUSTOM_CONTENT_PATH."/functions/functions.js?v=".date("YmdHis"); ?>"></script>	
    <?php  }
    
 //Clearfix just in case
	echo '<div class="clearfix"></div>';
?>
</body>
</html>
