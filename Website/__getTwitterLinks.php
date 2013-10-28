<?php

include 'zzzzzServer.php';
ignore_user_abort(true);
set_time_limit(300);

define('COUNTER_FILE', '__getTwitterLinksCount.txt');
define('SINCE_ID', file_get_contents(COUNTER_FILE));

function normalizeURL($url) {
	$parts = explode('#', $url);
	$parts = explode('?utm_', $parts[0]);
	return trim(strip_tags($parts[0]));
}

function resolveURL($shorturl, $round = 0) {
	if (stripos($shorturl, 'bit.ly') === FALSE &&
		stripos($shorturl, 'tinyurl.com') === FALSE &&
		stripos($shorturl, 'goo.gl') === FALSE &&
		stripos($shorturl, 'is.gd') === FALSE &&
		stripos($shorturl, 'fb.me') === FALSE &&
		stripos($shorturl, 'spon.de') === FALSE &&
		stripos($shorturl, 'trib.al') === FALSE &&
		stripos($shorturl, 'faz.net/-') === FALSE &&
		stripos($shorturl, 'news.okru.de') === FALSE &&
		stripos($shorturl, 'feeds.feedburner.com') === FALSE &&
		stripos($shorturl, 'dld.bz') === FALSE &&
		stripos($shorturl, 'j.mp') === FALSE &&
		stripos($shorturl, 'on-msn') === FALSE &&
		stripos($shorturl, 'spon.li') === FALSE &&
		stripos($shorturl, 'feedly.com') === FALSE &&
		stripos($shorturl, 'ow.ly') === FALSE &&
		stripos($shorturl, 'ht.ly') === FALSE &&
		stripos($shorturl, 'news.google.com/news/url?') === FALSE &&
		stripos($shorturl, 'google.com/url?') === FALSE &&
		stripos($shorturl, 'l.n24.de') === FALSE &&
		stripos($shorturl, 'dlvr.it') === FALSE &&
		stripos($shorturl, 'is.gd') === FALSE &&
		stripos($shorturl, 'on.welt.de') === FALSE) {
		return normalizeURL($shorturl);
	}
	if ($round >= 3) {
		return normalizeURL($shorturl);
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $shorturl);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_NOBODY, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	$result = curl_exec($ch);
	if ($result !== FALSE) {
		if (preg_match('/Location\:/', $result)) {
			$url = explode('Location: ', $result);
			if (count($url) > 1) {
				$reversed_url = preg_split('/\s/', $url[1], -1, PREG_SPLIT_NO_EMPTY);
				return resolveURL($reversed_url[0], $round+1);
			}
		}
	}
	return normalizeURL($shorturl);
}

function fullEscape($text) {
	return mysql_real_escape_string(trim(strip_tags($text)));
}

$keywords = array('spiegel.de', 'welt.de', 'n24.de', 'tagesschau.de', 'zeit.de', 'faz.net', 'msn.com', 'stern.de', 'focus.de', 'n-tv.de', 'fr-online.de', 'ftd.de', 'abendblatt.de', 'morgenpost.de', 'handelsblatt.com');
$url = 'http://search.twitter.com/search.json?q='.urlencode(implode(' OR ', $keywords).' filter:links').'&lang=de&include_entities=true&result_type=recent&rpp=100&since_id='.SINCE_ID;
if ($json = @file_get_contents($url)) {
	if ($data = json_decode($json)) {
		$tweet = array();
		foreach ($data->results as $entry) {
			$tweet['id'] = (isset($entry->id_str)) ? fullEscape($entry->id_str) : 0;
			$tweet['user_id'] = (isset($entry->from_user_id_str)) ? fullEscape($entry->from_user_id_str) : 0;
			$tweet['user_name'] = (isset($entry->from_user)) ? fullEscape($entry->from_user) : '';
			if (isset($entry->entities->urls)) {
				if (is_array($entry->entities->urls)) {
					if (count($entry->entities->urls) > 0) {
						foreach ($entry->entities->urls as $link) {
							if (!isset($link->expanded_url)) { continue; }
							$link = trim(strip_tags($link->expanded_url));
							if (mb_strlen($link) > 255 || mb_strlen($link) < 8) { continue; }
							$cleanLink = mysql_real_escape_string(resolveURL($link));
							$sql1 = "INSERT IGNORE INTO _news_tweets (tweet_id, user_id, user_screenname, link) VALUES ('".$tweet['id']."', '".$tweet['user_id']."', '".$tweet['user_name']."', '".$cleanLink."')";
							$sql2 = mysql_query($sql1) or die(mysql_error());
							if ($sql2 != FALSE) {
								$sql3 = "UPDATE _news_articles SET shared_twitter = shared_twitter+1 WHERE link = '".$cleanLink."'";
								mysql_query($sql3) or die(mysql_error());
							}
						}
					}
				}
			}
		}
		if (isset($data->max_id_str)) {
			file_put_contents(COUNTER_FILE, $data->max_id_str);
		}
	}
}

?>