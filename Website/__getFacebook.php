<?php

include 'zzzzzServer.php';

$timeout = intval(time()-3600*24);
$sql1 = "SELECT link FROM _news_articles WHERE date > ".$timeout." ORDER BY last_facebook ASC LIMIT 0, 8";
$sql2 = mysql_query($sql1) or die(mysql_error());
$sqlWhere = "";
$counter = 0;
while ($sql3 = mysql_fetch_assoc($sql2)) {
	if ($counter == 0) {
		$sqlWhere .= "url = '".$sql3['link']."'";
	}
	else {
		$sqlWhere .= " OR url = '".$sql3['link']."'";
	}
	$counter++;
}

$fb_query = "SELECT url, share_count, like_count FROM link_stat WHERE ".$sqlWhere;
$url = 'http://api.facebook.com/method/fql.query?format=json&query='.urlencode($fb_query);
if ($data = @file_get_contents($url)) {
	$data = json_decode($data);
	if (!is_null($data)) {
		foreach ($data as $entry) {
			if (isset($entry->like_count) && isset($entry->share_count) && isset($entry->url)) {
				$up1 = "UPDATE _news_articles SET shared_facebook_like = ".intval($entry->like_count).", shared_facebook_share = ".intval($entry->share_count).", last_facebook = ".time()." WHERE link = '".mysql_real_escape_string($entry->url)."'";
				$up2 = mysql_query($up1) or die(mysql_error());
			}
		}
	}
}

?>