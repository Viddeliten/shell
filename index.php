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
	
	if(defined('FLATTR_META_TAG'))
		echo FLATTR_META_TAG;
	
	require_once("functions/login.php");
	require_once("functions/db_connect.php");
	require_once("functions/string.php");
	require_once("functions/message.php");
	require_once("functions/user.php");
	require_once("functions/flattr.php");
	require_once("functions/spam.php");
	require_once("functions/version.php");
	require_once("functions/admin.php");
	require_once("functions/language.php");
	require_once("functions/display.php");
	require_once("functions/feedback/func.php");
	require_once("functions/comment/func.php");
	require_once("functions/news.php");
	require_once("functions/mailer.php");
	require_once("functions/usermessage.php");
	require_once("functions/privmess.php");
	require_once("functions/notice.php");
	require_once("functions/html.php");
	require_once("functions/rss.php");
	
	if(file_exists(CUSTOM_CONTENT_PATH."/globals.php"))
		require_once(CUSTOM_CONTENT_PATH."/globals.php");

	if(file_exists(CUSTOM_CONTENT_PATH."/functions/includer.php"))
		require_once(CUSTOM_CONTENT_PATH."/functions/includer.php");

	if(function_exists('meta_title_and_description')) meta_title_and_description(); ?>
   
   <?php // Use https://realfavicongenerator.net to get favicons, and put them in img/icon under custom content folder
   echo '<link rel="apple-touch-icon" sizes="180x180" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/favicon-16x16.png">
<link rel="manifest" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/manifest.json">
<link rel="mask-icon" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/safari-pinned-tab.svg" color="#5bbad5">
<link rel="shortcut icon" href="/'.CUSTOM_CONTENT_PATH.'/img/icon/favicon.ico">
<meta name="msapplication-config" content="/'.CUSTOM_CONTENT_PATH.'/img/icon/browserconfig.xml">
<meta name="theme-color" content="#ffffff">'; ?>

    
    <!-- Bootstrap core CSS -->
    <link href="<?php echo SITE_URL; ?>/bootstrap-3.3.4-dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Shell template style -->
    <link href="<?php echo SITE_URL; ?>/style.css" rel="stylesheet">

	<!-- Custom style -->
    <link href="<?php echo SITE_URL."/".CUSTOM_CONTENT_PATH; ?>/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
   
  <?php  
  language_setup();
  $connection=db_connect(db_host, db_name, db_user, db_pass);

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

?>
	
  </head>

  <body <?php echo BODY_PROPERTIES; ?>>
    <? 
	if(file_exists(CUSTOM_CONTENT_PATH."/index.php"))
	{
		include(CUSTOM_CONTENT_PATH."/index.php");
	}
	else
	{
		display_topline_menu(); ?>
		
		<div class="container" id="main_container">
			<div id="content">
				<?php 
					display_conditional_login();
				?>
				<?php 
				
				include("content.php"); ?>
				<div class="clearfix"></div>
			</div>

			<?php //Footer
			display_footer(); ?>
			
		</div><!-- /.container -->
	
	<?php 
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
/* ]]> */</script>
<!-- Slut Flattr-stuff -->

<!-- Google Analytics -->
<?php echo GoogleAnalyticsCode; ?>
<!-- End Google Analytics -->

<?php //Pingdom script
if(defined('PINGDOM_SCRIPT'))
	echo PINGDOM_SCRIPT; ?>
	
	<script src="//code.jquery.com/jquery-2.1.0.min.js"></script>

	<script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/"; ?>functions/functions.js?v=161113"></script>
	<script src="//viddewebb.se/_commons/js/basic.js"></script>

	<?php if(file_exists(CUSTOM_CONTENT_PATH.'/functions/java.js')) { ?>
		<script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/".CUSTOM_CONTENT_PATH; ?>/functions/java.js?v=<?php echo time(); ?>"></script> <?php } else echo "<!--".CUSTOM_CONTENT_PATH.'/functions/java.js does not exist -->'; ?>
	<script src='https://www.google.com/recaptcha/api.js'></script>
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/"; ?>bootstrap-3.3.4-dist/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?php echo str_ireplace("http:","",str_ireplace("https:","",SITE_URL))."/"; ?>bootstrap-3.3.4-dist/js/ie10-viewport-bug-workaround.js"></script>

<?php
 //Clearfix just in case
	echo '<div class="clearfix"></div>';
?>
</body>
</html>
