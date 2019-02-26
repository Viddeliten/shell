<?php
 
class RSS
{
	private $type;
	private $feed_link;
	private $description;
	  
	public function RSS($db_connection, $type, $feed_link, $description)
	{
		DEFINE ('LINK', $db_connection);
		$this->type = $type;
		$this->feed_link = $feed_link;
		$this->description = $description;
	}
  
 public function GetFeed()
 {
  return $this->getDetails() . $this->getItems();
 }
  
 private function getDetails()
 {
  /* $details = '<?xml version="1.0" encoding="UTF-8" ?> */
    $details = '<rss version="2.0">
     <channel>
      <title>'. SITE_NAME." ".$this->type.'</title>
      <link>'.  $this->feed_link .'</link>
      <description>'.  $this->description .'</description>
      <language>'. 'en-us'.'</language>';
      // <image>
       // <title>'. $row['image_title'] .'</title>
       // <url>'. $row['image_url'] .'</url>
       // <link>'. $row['image_link'] .'</link>
       // <width>'. $row['image_width'] .'</width>
       // <height>'. $row['image_height'] .'</height>
      // </image>';

  return $details;
 }
  
 private function getItems()
 {
     $db=new db_class();
  $itemsTable = PREFIX."news";
  $query = "SELECT * FROM ". $itemsTable."
	WHERE published<NOW()
	ORDER BY published DESC, id DESC
	LIMIT 30;";
  $result = $db->select($query);
  $items = '';
    foreach($result as $row)
  {
   $items .= '<item>
    <title>'. $row["headline"] .'</title>
    <link>'. news_get_link_url($row["id"]) .'</link>
    <guid>'. news_get_link_url($row["id"]) .'</guid>
	<pubDate>'.date(DATE_RSS, strtotime($row['published'])).'</pubDate>
    <description><![CDATA['. $row["text"] .']]></description>
   </item>';
  }
  $items .= '</channel>
    </rss>';
  return $items;
 }
 
}
 
?>