<div class="row">
	<div class="col-md-12 start-container jumbotron">
		<h1><?php echo sprintf(_("Welcome to %s"), SITE_NAME); ?></h1>
		<p class="lead"><?php echo SELLING_TEXT; ?></p>
		<h2><?php echo _("This is more text"); ?></h2>
		<p><?php echo _("It's just here for testing purposes."); ?></p>
	</div>
</div>
<div class="row" id="news">
	<div class="col-md-12">
		<?php news_show(1, _("Latest news"),2); ?>
		<?php news_show_latest_short(3, 150, 0, 3, 1, _("More news"), 2); ?>
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
					<h3>Block</h3>
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
