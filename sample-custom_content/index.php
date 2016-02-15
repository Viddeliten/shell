<?php

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