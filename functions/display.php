<?php

function display_topline_menu($navbar_type="navbar-inverse", $show_home_link=true, $icon_path=NULL, $site_uses_flattr=TRUE)
{
    
	$custom_pages=unserialize(CUSTOM_PAGES_ARRAY);
	$show_feedback=true;
	if(isset($custom_pages[_("Feedback")]))
	{
		$show_feedback=false;
	}
    
    if(defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0"))
    {
        $menu=array();
        $request_choser="p";
        $brand_text=($icon_path!==NULL ? '<img src="'.SITE_URL.'/'.$icon_path.'"/>' : SITE_NAME);
        $brand_link=SITE_URL;
        $class="navbar ".$navbar_type;
        
        echo html_menu($menu, $request_choser, $brand_text, $brand_link, $class, $show_home_link, $show_feedback);
        return true;
    }

	?>
	<nav class="navbar <?php echo $navbar_type; ?> navbar-fixed-top">
      <div class="container">
		<div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a id="navbar-site-name" class="navbar-brand" href="<?php echo SITE_URL; ?>"><?php echo ($icon_path!==NULL? '<img src="'.SITE_URL.'/'.$icon_path.'"/>' : SITE_NAME); ?></a>
		  <?php version_show_linked_number("v", 'navbar-brand'); ?>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <?php if( $show_home_link) { ?><li <?php if(!isset($_GET['p'])) echo 'class="active"'; ?>><a href="<?php echo SITE_URL; ?>"><?php echo _("Home"); ?></a></li><?php } ?>
			<?php admin_menu_dropdown(); ?>
           <!-- <li <?php if(isset($_GET['p']) && !strcmp($_GET['p'],"about")) echo 'class="active"'; ?>><a href="<?php echo SITE_URL; ?>/about" ><?php echo _("About"); ?></a></li> -->
		   <?php display_custom_pages_menu(); ?>
            <?php if($show_feedback) { ?><li <?php if(isset($_GET['p']) && !strcmp($_GET['p'],"feedback")) echo 'class="active"'; ?>><a href="<?php echo SITE_URL; ?>/feedback"><?php echo _("Feedback"); ?></a></li><?php } ?>
			<?php if(defined('SITE_OWNER_FLATTR_ID')) { ?><li><?php flattr_button_show(SITE_OWNER_FLATTR_ID, SITE_URL, SITE_NAME, "", 'compact', "sv"); ?></li><?php } ?>
          </ul>
		  <ul class="nav navbar-nav navbar-right">
				<?php display_friend_request_drop_menu(); ?>
			<li><?php login_display_link('data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar"'); ?></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>
	<?php 
}

/********************************************************************/
/*		Function: display_menu_vertical								*/
/*		Inspired by https://codepen.io/j_holtslander/pen/XmpMEp		*/
/********************************************************************/
function display_menu_vertical($menu_items, $menu_header_name=SITE_NAME)
{
	?>
	<nav class="navbar vertical">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#vertical-nav" aria-expanded="false" aria-controls="navbar">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?php echo SITE_URL; ?>"><?php echo $menu_header_name; ?></a>
			</div>
			 <div id="vertical-nav" class="collapse navbar-collapse">
				 <ul class="vertical-nav">
					<?php display_menu_pages($menu_items); ?>
				</ul>
			</div>
	</nav>
	<?php	
}

function display_friend_request_drop_menu($return_html=FALSE)
{
	ob_start();
    
	if(login_check_logged_in_mini()<1)
		return FALSE;
	
	if(isset($_SESSION[PREFIX.'user_id']))
		$requests=user_friend_get_requests($_SESSION[PREFIX.'user_id']);
	
	if(!empty($requests))
	{
		$r=array();
		foreach($requests as $request)
		{
			$r[sprintf("Friend request from %s", user_get_name($request['requested_by']))]=array("slug" => "profile/?user=".$request['requested_by']);
		}
		if(!empty($r))
		{
			$nr=count($r);
            if(defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0"))
                $r_text=html_tag("span",$nr,"badge badge-info");
            else
                $r_text=html_tag("span",$nr,"badge");
		}
		else
			$r_text="";
		
		echo (defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0") ? '<ul class="navbar-nav user-menu-dropdown">' : "");
		display_dropdown_menu('<span class="glyphicon glyphicon-user oi oi-person" aria-hidden="true"></span>'.$r_text,
									"user",
									$r);
		echo (defined('BOOTSTRAP_VERSION') && !strcmp(BOOTSTRAP_VERSION,"4.1.0") ? '</ul>' : "");
	}
    
    $contents = ob_get_contents();
	ob_end_clean();
	
	if(!$return_html)
		echo $contents;
	else
		return $contents;

}

function display_conditional_login()
{
	if(!isset($_SESSION[PREFIX."username"]))
	{
		echo '<div id="main_login_form" class="row" style="display: none;">
				<div class="col-lg-12">';
					login_form_login_inline();
		echo '</div>
		</div>';
	}
}

if(!function_exists("display_footer"))
{
    function display_footer()
    {
        ?>
        <div id="footer">
            <div class="row">
                <div class="col-md-8 center">
                    <?php if(!defined('NotPresented')) { ?>
                    <p><?php echo _("This site is presented by"); ?></p> <?php } ?>
                    <a href="http://viddewebb.se"><img src="<?php echo SITE_URL."/"; ?>img/ViddeWebb-footer.png" alt="Vidde Webb"></a>
                </div>
                <div class="col-md-4 right">
                    <p><?php echo _("Select language:"); ?>
                        <a href="<?php echo add_get_to_URL("language", "sv"); ?>"><img src="<?php echo SITE_URL."/"; ?>img/flag/sv.png"></a>
                        <a href="<?php echo add_get_to_URL("language", "uk"); ?>"><img src="<?php echo SITE_URL."/"; ?>img/flag/uk.png"></a>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
}

function display_custom_pages_menu($return_html=FALSE)
{
    ob_start();
    
	$custom_pages=unserialize(CUSTOM_PAGES_ARRAY);
	display_menu_pages($custom_pages);
    
    $contents = ob_get_contents();
	ob_end_clean();
	
	if($return_html)
		return $contents;
	else
		echo $contents;

}

function display_menu_pages($custom_pages)
{
	$logged_in_level=login_check_logged_in_mini();
	
	// echo "<pre>".print_r($custom_pages,1)."</pre>";
	
	foreach($custom_pages as $name => $content)
	{
		if((!isset($content['req_user_level']) || $content['req_user_level']<1 || $logged_in_level>=$content['req_user_level']) && (!isset($content['slug']) || strcmp($content['slug'],"admin")))
		{
			if(isset($content['url']))
			{
				echo html_tag("li",html_link($content['url'], _($name), "nav-link"),"nav-item".(isset($_GET['p']) && !strcmp($content['slug'], $_GET['p']) ? " active" : ""));
			}
			else if(!isset($content['subpages']) || empty($content['subpages']))
			{
				echo '<li class="nav-item'.(isset($_GET['p']) && !strcmp($content['slug'], $_GET['p']) ? " active" : "").'"><a class="nav-link" href="'.SITE_URL.'/'.$content['slug'].'" >'._($name).'</a></li>';
			}
            else
                display_dropdown_menu($name, $content['slug'], $content['subpages']);
		}
	}
}

function display_dropdown_menu($name, $slug, $subpages)
{
	$logged_in_level=login_check_logged_in_mini();
    
    if(!defined("BOOTSTRAP_VERSION") || substr(BOOTSTRAP_VERSION, 0,1)=="3") // v3 (old) type dropdown
    {
		if(!empty($subpages))
		{
        echo '<li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">'.$name.'<span class="caret"></span></a>
                          <ul class="dropdown-menu" role="menu">';
                          foreach($subpages as $s_name => $s_content)
                          {
                                if(isset($s_content['slug']) && (!isset($s_content['req_user_level']) || $s_content['req_user_level']<1 || $logged_in_level>=$s_content['req_user_level']))
                                    echo '<li ><a href="'.SITE_URL.'/'.$slug.'/'.$s_content['slug'].'" >'.$s_name.'</a></li>';
                          }
                    echo '</ul>
        </li>';
		}
    }
    else // version 4.1.0 type dropdown
    {
        echo '<li class="nav-item dropdown'.(isset($_GET['p']) && !strcmp($slug, $_GET['p']) ? " active" : "").'"> 
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown_'.string_slugify($name).'" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                '.$name.'
                </a>
                <div class="dropdown-menu" aria-labelledby="navbarDropdown_'.string_slugify($name).'">';
        foreach($subpages as $s_name => $s_content)
        {
			if(!strcmp($s_name,"dropdown-divider"))
				echo '<div class="dropdown-divider"></div>';
			else if(!strcmp($s_content['slug'],"logout"))
				echo '<a class="dropdown-item" href="'.SITE_URL.'/?'.$s_content['slug'].'">'._($s_name).'</a>';
			else if(!isset($s_content['req_user_level']) || $s_content['req_user_level']<1 || $logged_in_level>=$s_content['req_user_level'])
				echo '<a class="dropdown-item" href="'.SITE_URL.'/'.$slug.'/'.$s_content['slug'].'">'._($s_name).'</a>';
        }
        echo '
                </div>
            </li>';
    }
}

?>