<?php
$locale="sv_SE.UTF-8";
  putenv("LC_ALL=" . $locale); 
  setlocale(LC_ALL, $locale);

  $domain = "sv_SE";
  bindtextdomain($domain, "./translations"); 
  bind_textdomain_codeset($domain, 'UTF-8');
  textdomain($domain);


echo _("here is some text");
echo "<br />".gettext("here is some text");

echo "<br />";
if (!function_exists("gettext")){
    echo _("gettext is not installed");
}
else{
    echo _("gettext is supported");
}

?>