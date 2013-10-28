<?php
/*exit;
include 'zzzzzServer.php';

require 'lib/twitteroauth/twitteroauth.php';
require 'config.php';
define('OAUTH_CALLBACK', '');

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);
$content = $connection->get('account/verify_credentials');

$sql1 = "SELECT id AS stream_id, tweeted, score AS stream_score FROM _news_streams ORDER BY score DESC LIMIT 0, 5";
$sql2 = mysql_query($sql1);
while ($sql3 = mysql_fetch_assoc($sql2)) {
	if ($sql3['tweeted'] != 0) { continue; }
	$sql4 = "SELECT a.title, b.title AS medium_title FROM _news_articles AS a JOIN _news_media AS b ON a.mediumID = b.id WHERE a.streamID = ".$sql3['stream_id']." AND a.tweeted = 0 ORDER BY a.date DESC LIMIT 0, 1";
	$sql5 = mysql_query($sql4);
	if (mysql_num_rows($sql5) == 1) {
		$sql6 = mysql_fetch_assoc($sql5);
		$permalink = 'http://twem.de/'.id2secure($sql3['stream_id']);
		$mediumTitle = $sql6['medium_title'];
		$charsLeft = 140-mb_strlen($permalink)-mb_strlen($mediumTitle)-7;
		$status_str = mb_substr($sql6['title'], 0, $charsLeft).'... ('.$mediumTitle.') '.$permalink;
		$connection->post('statuses/update', array('status' => $status_str));
		$up1 = "UPDATE _news_streams SET tweeted = 1 WHERE id = ".$sql3['stream_id'];
		$up2 = mysql_query($up1);
		$up1 = "UPDATE _news_articles SET tweeted = 1 WHERE streamID = ".$sql3['stream_id'];
		$up2 = mysql_query($up1);
	}
}
*/
?>