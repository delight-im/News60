<?php

include 'zzzzzServer.php';

define('MEDIUM_FACTOR', 1.5); // the higher the less does it pull down big media (must be greater than 1.0)
define('TIME_OFFSET', 1.2); // the lower the more to articles lose importance after time (>= 0.0)
define('TIME_DECAY', 1.6); // the higher the more to articles lose importance after time (>= 1.0)
define('BASIS_COUNT', 0.85); // give every item a basis count so that more items mean more importance regardless of share count

$timeout = intval(time()-3600*24);
$sql1 = "SELECT a.id, a.date, a.shared_facebook_like, a.shared_facebook_share, a.shared_twitter, b.avg_facebook_like, b.avg_facebook_share, b.avg_twitter FROM _news_articles AS a JOIN _news_media AS b ON a.mediumID = b.id WHERE a.date > ".$timeout." ORDER BY a.last_score ASC LIMIT 0, 50";
$sql2 = mysql_query($sql1) or die(mysql_error());
while ($sql3 = mysql_fetch_assoc($sql2)) {
	$hours_passed = (time()-$sql3['date'])/3600;
	// CALCULATE SHARE COUNT IN RELATION TO AVERAGE OF THIS MEDIUM ANFANG
	$fb_likes_relative = (($sql3['shared_facebook_like']+BASIS_COUNT)/log(MEDIUM_FACTOR+$sql3['avg_facebook_like'], MEDIUM_FACTOR))*12;
	$fb_shares_relative = (($sql3['shared_facebook_share']+BASIS_COUNT)/log(MEDIUM_FACTOR+$sql3['avg_facebook_share'], MEDIUM_FACTOR))*10;
	$twitter_relative = (($sql3['shared_twitter']+BASIS_COUNT)/log(MEDIUM_FACTOR+$sql3['avg_twitter'], MEDIUM_FACTOR))*10;
	$votes_total = $fb_likes_relative+$fb_shares_relative+$twitter_relative;
	// CALCULATE SHARE COUNT IN RELATION TO AVERAGE OF THIS MEDIUM ENDE
	$new_score = $votes_total/pow(($hours_passed+TIME_OFFSET), TIME_DECAY);
	$up1 = "UPDATE _news_articles SET score = ".$new_score.", last_score = ".time()." WHERE id = ".$sql3['id'];
	$up2 = mysql_query($up1) or die(mysql_error());
}
$up1 = "UPDATE _news_articles SET score = (shared_facebook_like+shared_facebook_share+shared_twitter)/10000000 WHERE date <= ".intval(time()-3600*24)." AND date >= ".intval(time()-3600*24*7);
$up2 = mysql_query($up1) or die(mysql_error());
$streamScore1 = "UPDATE _news_streams SET score = (SELECT SUM(score) FROM _news_articles WHERE streamID = _news_streams.id) WHERE time_created > ".intval(time()-3600*24*4);
$streamScore2 = mysql_query($streamScore1) or die(mysql_error());

?>