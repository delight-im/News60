<?php

include 'zzzzzServer.php';

$timeout = intval(time()-3600*24*7);
$sql1 = "SELECT id FROM _news_media LIMIT 0, 100";
$sql2 = mysql_query($sql1) or die(mysql_error());
while ($sql3 = mysql_fetch_assoc($sql2)) {
	$get1 = "SELECT AVG(shared_facebook_like), AVG(shared_facebook_share), AVG(shared_twitter) FROM _news_articles WHERE date > ".$timeout." AND mediumID = ".$sql3['id'];
	$get2 = mysql_query($get1) or die(mysql_error());
	if ($get2 !== FALSE) {
		$get3 = mysql_fetch_assoc($get2);
		if (isset($get3['AVG(shared_facebook_like)']) && isset($get3['AVG(shared_facebook_share)']) && isset($get3['AVG(shared_twitter)'])) {
			$up1 = "UPDATE _news_media SET avg_facebook_like = ".$get3['AVG(shared_facebook_like)'].", avg_facebook_share = ".$get3['AVG(shared_facebook_share)'].", avg_twitter = ".$get3['AVG(shared_twitter)']." WHERE id = ".$sql3['id'];
			$up2 = mysql_query($up1) or die(mysql_error());
		}
	}
}

?>