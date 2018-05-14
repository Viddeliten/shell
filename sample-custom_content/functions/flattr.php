<?php

/***
*	This function should decide flattr tag based on GET or return FALSE
***/
function flattr_custom_page_tag()
{
	return FALSE;
}

/***
*   Return the text you want to show on your flattr information page
***/
function flattr_custom_info_page()
{
    $content=html_tag("h2", _("I am a creator, does this mean you will pay me?"));
    $content.=html_tag("p", sprintf(_('Well, it actually means you can get paid by the visitors to the site through Flattr.
    What you need to do is:')));
    $steps=array();
    $steps[]=sprintf(_('<a href="%s">Sign up for Flattr</a>'), "https://flattr.com/#signup");
    $steps[]=sprintf(_('Find your Flattr id. Your Flattr-id can be found in the <a href="%s">Flattr-settings</a> once you have your Flattr account.
    You will see something like &lt;meta name="flattr:id" content="8dpk18"&gt; in wich case \'8dpk18\' is your id (it is not, that is my id).'), "https://flattr.com/settings/profile");
    $steps[]=sprintf(_('<a href="%s">Sign up for %s</a>'), SITE_URL."?reg", SITE_NAME);
    $steps[]=sprintf(_('Enter your Flattr-id in <a href="%s">your settings</a> and check what should be flattred'), SITE_URL."/user/settings");
    $content.=html_tag("ol", html_tag("li", implode("</li><li>", $steps)));

    $content.=html_tag("h2", _("I want to support creators, how do I do that?"));
    $steps=array();
    $steps[]=sprintf(_('<a href="%s">Sign up for Flattr</a>'), "https://flattr.com/#signup");
    $steps[]=_('Add the Flattr extension to your browser. See <a href="https://flattr.com/faq">Flattr FAQ for details</a>');
    $steps[]=sprintf(_("Make sure you enable the extension to flattr %s!"), SITE_NAME);
    $content.=html_tag("ol", html_tag("li", implode("</li><li>", $steps)));
    
    return $content;
}