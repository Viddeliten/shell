<div class="row">
	<div class="col-md-12 start-container jumbotron">
		<h1><?php echo sprintf(_("Welcome to %s"), SITE_NAME); ?></h1>
		<p class="lead"><?php echo SELLING_TEXT; ?></p>
		<h2><?php echo _("This is more text"); ?></h2>
		<p><?php echo _("It's just here for testing purposes."); ?></p>
	</div>
</div>
<div class="row well" id="news">
	<h2><?php echo sprintf(_("%s news"),SITE_NAME); ?></h2>
	<div class="col-md-4 latest-post">
		<?php news_show(1, "",2); ?>
	</div>
	<div class="col-md-8 latest-stream">
		<?php news_show_latest_short(2, 80, 0, 3, 1, "", 2); ?>
	</div>
	<div class="col-lg-12 center">
		<p><a class="btn btn-default page-link form-control" href="<?php echo news_get_link_url(); ?>"><?php echo _("All news"); ?></a></p>
	</div>
</div>
<div class="row">
	<div class="col-md-12">

		<div class="row">
			<div class="col-md-12">
				<h2><?php echo _("This is block two"); ?></h2>
				<p><?php echo _("Write some interesting stuff here. Or some boring stuff. That should work too."); ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="col-md-4 well">
					<h3><?php echo _("Diskussion"); ?></h3>
					<?php comments_show_latest_short(5); ?>
				</div>
				<div class="col-md-4 well">
					<h3>Block</h3>
				</div>
				<div class="col-md-4 well">
					<h3>Block</h3>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
	<h2><?php echo _("This is yet another a block"); ?></h2>
	<p><?php echo _("This content is last on this page."); ?></p>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="col-md-3 well">
			<h3>Block</h3>
		</div>
		<div class="col-md-3 well">
			<h3>Block</h3>
		</div>
		<div class="col-md-6 well">
			<h3>Block</h3>
		</div>
	</div>
</div>
<!--
<div id="container">
    <div id="dummy"></div>
    <div id="element">
        some text
    </div>
</div> -->
