<?php
  /* header("Content-Type: application/xml; charset=UTF-8");*/
  echo '<?xml version="1.0" encoding="UTF-8" ?>';
  
  define('RSS',"");
  
  require_once("op_includer.php");
  language_setup();
  $connection=db_connect(db_host, db_name, db_user, db_pass);

  $rss = new RSS($connection, "news", SITE_URL."/operation/rss_news.php", "News on ".SITE_NAME);
  echo $rss->GetFeed();

  db_close($connection);
?>