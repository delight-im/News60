<?php
include 'zzzzzServer.php';
set_time_limit(0);
ignore_user_abort(true);

/**
 * Um den besten Artikel innerhalb eines Streams auszuwählen, werden die Scores innerhalb des Streams anders bewertet.
 * Sofort nach Ursprung des Streams (erster Artikel) nehmen die Scores mit der Zeit ab, bis die Scores auf 0 sinken.
 * Nach einigen Stunden, sofort nach dem Tiefpunkt, steigen die Scores dann wieder an, da vermehrt neue Informationen enthalten sind.
 */
function in_stream_score($absolute_score, $time_self, $time_first) {
	$min_score_after = 4; // nach dieser Stundenanzahl wird das Minimum erreicht, danach geht es wieder bergauf
	$hours_after_first = ($time_self-$time_first)/3600;
	if ($hours_after_first <= 0) { // Artikel ist so alt wie der Stream und wird deshalb nicht abgeschwächt
		return $absolute_score;
	}
	elseif ($hours_after_first >= ($min_score_after*2)) { // Artikel ist ganz aktuell und wird deshalb nicht abgeschwächt
		return $absolute_score;
	}
	else { // Artikel ist zeitlich in der Mitte angeordnet, also weder ganz alt noch ganz aktuell, und wird deshalb abgeschwächt
		$percentage = 0.5+0.5*cos($hours_after_first*M_PI/$min_score_after); // scale cosine => [0, 1] with minimum at $min_score_after
		return $absolute_score*$percentage;
	}
}

$minScore1 = "SELECT score FROM _news_streams ORDER BY score DESC LIMIT 30, 1"; // Minimum-Score für Zugehörigkeit zur Top-30 ermitteln
$minScore2 = mysql_query($minScore1) or die(mysql_error());
$minScore3 = mysql_result($minScore2, 0);

$sql1 = "SELECT id, time_origin FROM _news_streams WHERE score > ".$minScore3." ORDER BY time_updated ASC LIMIT 0, 30"; // Top-30 updaten
$sql2 = mysql_query($sql1) or die(mysql_error());
while ($sql3 = mysql_fetch_assoc($sql2)) {
	$sql4 = "SELECT id, link, date, score FROM _news_articles WHERE streamID = ".$sql3['id']." LIMIT 0, 250";
	$sql5 = mysql_query($sql4) or die(mysql_error());
	$featuredArticle = NULL;
	$featuredBest = 0;
	while ($sql6 = mysql_fetch_assoc($sql5)) {
		$sql6['score'] = in_stream_score($sql6['score'], $sql6['date'], $sql3['time_origin']);
		if ($sql6['score'] > $featuredBest) {
			$featuredArticle = $sql6;
			$featuredBest = $sql6['score'];
		}
	}
	if (isset($featuredArticle)) {
		mysql_query("UPDATE _news_streams SET featuredArticle = ".$featuredArticle['id'].", time_updated = ".time()." WHERE id = ".$sql3['id']) or die(mysql_error());
	}
}
?>