<?php

include 'zzzzzServer.php';
set_time_limit(0);
ignore_user_abort(true);

$sql1 = "SELECT a.id AS stream_id, a.time_origin, b.id, b.title, b.link, b.date, b.thumbnail_state, b.shared_facebook_like, b.shared_facebook_share, b.shared_twitter, c.title AS medium_title FROM _news_streams AS a JOIN _news_articles AS b ON a.featuredArticle = b.id JOIN _news_media AS c ON b.mediumID = c.id ORDER BY a.score DESC LIMIT 0, 10";
$sql2 = mysql_query($sql1) or die(mysql_error());
$data = array();
while ($sql3 = mysql_fetch_assoc($sql2)) {
	$totalShareCount = array('facebook_likes'=>0, 'facebook_shares'=>0, 'twitter'=>0);
	$totalShareCount['facebook_likes'] += $sql3['shared_facebook_like'];
	$totalShareCount['facebook_shares'] += $sql3['shared_facebook_share'];
	$totalShareCount['twitter'] += $sql3['shared_twitter'];
	// RELATED POSTS BEGIN
	$getRelated1 = "SELECT a.id, a.link, a.title, a.shared_facebook_like, a.shared_facebook_share, a.shared_twitter, b.title AS medium_title FROM _news_articles AS a JOIN _news_media AS b ON a.mediumID = b.id WHERE a.streamID = ".$sql3['stream_id']." ORDER BY date ASC";
	$getRelated2 = mysql_query($getRelated1) or die(mysql_error());
	$getRelatedRows = mysql_num_rows($getRelated2);
	$latestLink = 'keine';
	if ($getRelatedRows != 0) {
		$relatedAll = array();
		while ($getRelated3 = mysql_fetch_assoc($getRelated2)) {
			if ($getRelated3['id'] == $sql3['id']) { continue; }
			$totalShareCount['facebook_likes'] += $getRelated3['shared_facebook_like'];
			$totalShareCount['facebook_shares'] += $getRelated3['shared_facebook_share'];
			$totalShareCount['twitter'] += $getRelated3['shared_twitter'];
			if (!isset($relatedAll[$getRelated3['medium_title']])) {
				$relatedAll[$getRelated3['medium_title']] = array(); // Related Posts nach Medium gruppieren
			}
			$linkTarget = htmlspecialchars($getRelated3['link']);
			$linkData = ' href="'.$linkTarget.'" onclick="window.open(\''.$linkTarget.'\'); return false;"';
			$relatedAll[$getRelated3['medium_title']][] = '<a'.$linkData.'>'.htmlspecialchars($getRelated3['medium_title']).'</a>';
			$latestLink = '<a'.$linkData.'>'.htmlspecialchars(strip_tags($getRelated3['title'])).'</a>';
		}
		// HAUPTQUELLE ENTFERNEN UND HINTEN WIEDER ANHÄNGEN DAMIT SIE DIE LETZTE WAHL FÜR RELATED POSTS IST ANFANG
		if (isset($relatedAll[$sql3['medium_title']])) {
			$mainSource = $relatedAll[$sql3['medium_title']];
			unset($relatedAll[$sql3['medium_title']]);
			$relatedAll[$sql3['medium_title']] = $mainSource;
		}
		// HAUPTQUELLE ENTFERNEN UND HINTEN WIEDER ANHÄNGEN DAMIT SIE DIE LETZTE WAHL FÜR RELATED POSTS IST ENDE
		$relatedList = array();
		$relatedRound = 0;
		while (count($relatedList) < NUMBER_RELATED_LINKS) { // ein paar Related Posts auswählen
			$articlesThisRound = FALSE;
			foreach ($relatedAll as $relatedArticles) { // gruppiert nach Quellen
				if (isset($relatedArticles[$relatedRound])) {
					$articlesThisRound = TRUE;
					$relatedList[] = $relatedArticles[$relatedRound]; // schrittweise von jeder Quelle einen Artikel
					if (count($relatedList) >= NUMBER_RELATED_LINKS) { break; } // bis Liste voll ist
				}
			}
			if (!$articlesThisRound) { break; }
			$relatedRound++;
		}
		if ($getRelatedRows > NUMBER_RELATED_LINKS) { // wenn es mehr Related Posts gab als angezeigt werden
			$relatedList[] = '+'.intval($getRelatedRows-NUMBER_RELATED_LINKS);
		}
	}
	else {
		$relatedList = array('keine');
	}
	// RELATED POSTS END
	$sql3['related_links'] = $relatedList;
	$sql3['latest_article'] = $latestLink;
	foreach ($totalShareCount as $shareType => $shareCount) {
		$sql3[$shareType] = $shareCount;
	}
	unset($sql3['shared_facebook_like']);
	unset($sql3['shared_facebook_share']);
	unset($sql3['shared_twitter']);
	$data[] = $sql3;
}
$cacheData = json_encode($data);
file_put_contents('__cache.txt', $cacheData);

?>