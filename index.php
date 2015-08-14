<!DOCTYPE html>
<html lang="en">
<?php setlocale(LC_NUMERIC, 'en_US'); ?>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
	<?php require_once("config.php"); ?>

    <link rel="icon" href="<?php echo CUSTOM_CONTENT_PATH; ?>/favicon.ico">
	
    <title><?php echo SITE_NAME; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap-3.3.4-dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Shell template style -->
    <link href="style.css" rel="stylesheet">

	<!-- Custom style -->
    <link href="<?php echo SITE_URL."/".CUSTOM_CONTENT_PATH; ?>/style.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
	
  </head>

  <body>
  
  <?php session_start(); ?>
   
  <?php
  
    if(file_exists(CUSTOM_CONTENT_PATH."/functions/includer.php"))
	  require_once(CUSTOM_CONTENT_PATH."/functions/includer.php");

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
  
  language_setup();

  $connection=db_connect(db_host, db_name, db_user, db_pass);
  
  //Include custom content
  if(file_exists(CUSTOM_CONTENT_PATH."/globals.php"))
	  require_once(CUSTOM_CONTENT_PATH."/globals.php");
  
  if(file_exists(CUSTOM_CONTENT_PATH."/receive.php"))
	  require_once(CUSTOM_CONTENT_PATH."/receive.php");
  

  login_receive();
  feedback_recieve();
  comment_receive();
  user_receive();
  version_receive(); 
  news_receive(); 
  usermessage_receive();
  notice_receive();
  privmess_receive();
  
  if(isset($_SESSION[PREFIX.'user_id']))
	usermessage_check_messages($_SESSION[PREFIX.'user_id']);

  ?>
  
 

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php echo SITE_URL; ?>"><?php echo SITE_NAME; ?></a>
		  <?php version_show_linked_number("v", 'navbar-brand'); ?>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li <?php if(!isset($_GET['p'])) echo 'class="active"'; ?>><a href="<?php echo SITE_URL; ?>"><?php echo _("Home"); ?></a></li>
			<?php admin_menu_dropdown(); ?>
           <!-- <li <?php if(isset($_GET['p']) && !strcmp($_GET['p'],"about")) echo 'class="active"'; ?>><a href="<?php echo SITE_URL; ?>?p=about" ><?php echo _("About"); ?></a></li> -->
		   <?php display_custom_pages_menu(); ?>
            <li <?php if(isset($_GET['p']) && !strcmp($_GET['p'],"feedback")) echo 'class="active"'; ?>><a href="<?php echo SITE_URL; ?>?p=feedback"><?php echo _("Feedback"); ?></a></li>
			<li><?php flattr_button_show(SITE_OWNER_FLATTR_ID, SITE_URL, SITE_NAME, "", 'compact', "sv"); ?></li>
          </ul>
		  <ul class="nav navbar-nav navbar-right">
			<li><?php login_display_link('data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar"'); ?></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
	
    
	<div class="container" id="main_container">
		<div id="content">
		<?php 
			if(!isset($_SESSION[PREFIX."username"]))
			{
				echo '<div id="main_login_form" class="row" style="display: none;">
						<div class="col-lg-12">';
							login_form_login_inline();
				echo '</div>
				</div>';
			}
		?>
		<?php 
		
		// $custom_pages=unserialize(CUSTOM_PAGES_ARRAY);
		// echo "<pre>".print_r($custom_pages,1)."</pre>";
		
		include("content.php"); ?>
		<div class="clearfix"></div>
		</div>
		<div id="footer">
			<div class="row">
				<div class="col-md-8 center">
					<p><?php echo _("This site is presented by"); ?></p>
					<a href="http://viddewebb.se"><img src="img/ViddeWebb-footer.png" alt="Vidde Webb"></a>
				</div>
				<div class="col-md-4 right">
					<p><?php echo _("Select language:"); ?>
						<a href="<?php echo add_get_to_URL("language", "sv"); ?>"><img src="img/flag/sv.png"></a>
						<a href="<?php echo add_get_to_URL("language", "uk"); ?>"><img src="img/flag/uk.png"></a>
					</p>
				</div>
			</div>
		</div>
    </div><!-- /.container -->
	
	<?php db_close($connection); ?>
	
	<!-- Flattr-stuff: -->

<script type="text/javascript">
/* <![CDATA[ */
    (function() {
        var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];
        s.type = 'text/javascript';
        s.async = true;
        s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
        t.parentNode.insertBefore(s, t);
    })();
/* ]]> */</script>
<!-- Slut Flattr-stuff -->

<!-- Google Analytics -->
<?php echo GoogleAnalyticsCode; ?>
<!-- End Google Analytics -->
	
	<script src="//code.jquery.com/jquery-2.1.0.min.js"></script>

	<script src="functions/functions.js?v=150615_6"></script>
	<script src="//viddewebb.se/_commons/js/basic.js"></script>

	<?php if(file_exists(CUSTOM_CONTENT_PATH.'/functions/java.js')) { ?>
		<script src="<?php echo CUSTOM_CONTENT_PATH; ?>/functions/java.js?v=<?php echo time(); ?>"></script> <?php } else echo "<!--".CUSTOM_CONTENT_PATH.'/functions/java.js does not exist -->'; ?>
	<script src='https://www.google.com/recaptcha/api.js'></script>
	
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="bootstrap-3.3.4-dist/js/bootstrap.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="bootstrap-3.3.4-dist/js/ie10-viewport-bug-workaround.js"></script>

<?php
 //Clearfix just in case
	echo '<div class="clearfix"></div>';
?>
</body>
</html>
