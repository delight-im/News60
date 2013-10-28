<?php

include 'zzzzzServer.php';

require 'lib/simplepie.php';
require 'lib/Encoding.php';
define('NUMBER_ITEMS_TO_PROCESS', 15);

$url_blacklist = array();
$url_blacklist[md5('http://www.sportschau.de/sp/fussball/bundesliga/')] = 1;
$url_blacklist[md5('http://www.sportschau.de/fussball/cl/')] = 1;
$url_blacklist[md5('http://www.zeit.de/vorabmeldungen/neu-in-der-aktuellen-zeit')] = 1;

$getSource1 = "SELECT id, feed FROM _news_media WHERE feed != '' ORDER BY time_crawled ASC LIMIT 0, 1";
$getSource2 = mysql_query($getSource1) or die(mysql_error());
$getSource3 = mysql_fetch_assoc($getSource2);

$feed = new SimplePie();
$feed->set_feed_url($getSource3['feed']);
$feed->set_timeout(10);
$feed->set_useragent('Mozilla/5.0 (Windows NT 6.1; WOW64; rv:20.0) Gecko/20100101 Firefox/20.0');
$feed->set_item_limit(NUMBER_ITEMS_TO_PROCESS);
$feed->enable_cache(false);
$feed->init();
$feed_items = $feed->get_items(0, NUMBER_ITEMS_TO_PROCESS);
$counter = 0;
foreach ($feed_items as $item) {
	if ($counter <= NUMBER_ITEMS_TO_PROCESS) {
		$link = trim(strip_tags($item->get_permalink()));
		$link = explode('#', $link, 2);
		$link = $link[0];
		if (isset($url_blacklist[md5($link)])) { continue; }
		$title = trim(strip_tags(html_entity_decode(ForceUTF8\Encoding::fixUTF8($item->get_title()))));
		$title = preg_replace('/[+]{2,}/', '', $title); // mehrere + hintereinander entfernen (Eilmeldungen usw.)
		$title = preg_replace('/[\s]{2,}/', ' ', $title); // mehrere Leerzeichen hintereinander durch eins ersetzen
		$description = trim(strip_tags(html_entity_decode(ForceUTF8\Encoding::fixUTF8($item->get_description()))));
		$description = explode('Mehr zum Thema', $description, 2);
		$description = $description[0];
		$article_timestamp = intval($item->get_date('U'));
		if ($article_timestamp > time() || $article_timestamp < (time()-3600*24)) {
			$article_timestamp = time();
		}
		$sql1 = "INSERT IGNORE INTO _news_articles (title, link, description, date, mediumID) VALUES ('".mysql_real_escape_string($title)."', '".mysql_real_escape_string(trim($link))."', '".mysql_real_escape_string(trim($description))."', ".$article_timestamp.", ".$getSource3['id'].")";
		mysql_query($sql1) or die(mysql_error());
		$counter++;
	}
}

if ($counter == 0) {
	$errorSQL = ", errors_occurred = errors_occurred+1";
}
else {
	$errorSQL = "";
}
$up1 = "UPDATE _news_media SET time_crawled = ".time().$errorSQL." WHERE id = ".$getSource3['id'];
mysql_query($up1) or die(mysql_error());

?>