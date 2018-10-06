<?php
/***
/*	function custom_page_display 
*			should try to display a page based on page $_GET['p'] and subpage $_GET['s'] and return TRUE on success or FALSE on fail
*			If this function returns FALSE, Shell code will try to display a page, otherwise it will assume a page has already been displayed.
*/
function custom_page_display()
{
	if(isset($_GET['p']))
	{
		if(!strcmp($_GET['p'],"about"))
		{
			page_display_about();
			return TRUE;
		}
		else if(!strcmp($_GET['p'],"users"))
		{
			if(isset($_GET['s']))
			{
				if(!strcmp($_GET['s'],"active"))
				{
					echo "<h1>"._("Active users")."</h1>";
					user_display_active_users(FALSE);
					return TRUE;
				}
				else if(!strcmp($_GET['s'],"friends"))
				{
					echo "<h1>"._("Friends")."</h1>";
					user_display_friends();
					return TRUE;
				}
			}
		}
	}
	return FALSE;
}

function page_display_about()
{
	?>
	<div class="row">
		<div class="col-md-12 start-container jumbotron">
			<h1><?php echo sprintf(_("About this site"), SITE_NAME); ?></h1>
			<p class="lead"><?php echo SELLING_TEXT; ?></p>
			<h2><?php echo _("This is more text"); ?></h2>
			<p><?php echo _("It's just here for testing purposes."); ?></p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12 start-container">
			<h2><?php echo _("More text outside the jumbotron"); ?></h2>
			<p><?php echo _("This can for example be an informative text."); ?></p>
		</div>
	</div>
	<div class="row">
		<div class="col-md-6">
			<h2><?php echo _("Some useful information"); ?></h2>
			<p><?php echo _("This is a half column."); ?></p>
		</div>
		<div class="col-md-6">
			<h2><?php echo _("Other stuff"); ?></h2>
			<p><?php echo _("This can for example be an informative text."); ?></p>
		</div>
	</div>
<?php
}
?>