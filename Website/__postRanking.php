<?php

include 'zzzzzServer.php';

require 'lib/twitteroauth/twitteroauth.php';
require 'config.php';
define('OAUTH_CALLBACK', '');

define('MAX_RANK_MENTION', 250);

$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, OAUTH_TOKEN, OAUTH_SECRET);
$content = $connection->get('account/verify_credentials');

$order = "ORDER BY fans DESC, user_id ASC";
$sql1 = "SELECT user_id, screen_name, twitteredThis FROM _news_twitterers WHERE location != '' ".$order." LIMIT 0, ".MAX_RANK_MENTION;
$sql2 = mysql_query($sql1) or die(mysql_error());
$counter = 0;
while ($sql3 = mysql_fetch_assoc($sql2)) {
	$counter++;
	if ($sql3['twitteredThis'] == 1) { continue; }
	if ($counter > MAX_RANK_MENTION) { continue; }
	$status_str = 'Platz '.$counter.' der deutschen Twitter-Charts: @'.$sql3['screen_name'].' (http://www.news60.de/charts)';
	$connection->post('statuses/update', array('status' => $status_str));
	$up1 = "UPDATE _news_twitterers SET twitteredThis = 1 WHERE user_id = ".$sql3['user_id'];
	$up2 = mysql_query($up1) or die(mysql_error());
	exit;
}

?>