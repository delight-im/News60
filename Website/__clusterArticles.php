<?php

include 'zzzzzServer.php';
define('RELATED_THRESHOLD', 0.265);

$getStopwords1 = "SELECT word FROM _news_stopwords";
$getStopwords2 = mysql_query($getStopwords1);
$stopwords = array();
while ($getStopwords3 = mysql_fetch_assoc($getStopwords2)) {
	$stopwords[md5($getStopwords3['word'])] = 1;
}

$timeout = intval(time()-3600*24);
$getRecentStreams1 = "SELECT id FROM _news_streams WHERE time_created > ".$timeout;
$getRecentStreams2 = mysql_query($getRecentStreams1);
$recentStreams = array();
while ($getRecentStreams3 = mysql_fetch_assoc($getRecentStreams2)) {
	$recentStreams[$getRecentStreams3['id']] = 1;
}

$dokumente = array();
$sql1 = "SELECT id, streamID, title, description, date FROM _news_articles ORDER BY randomSortID DESC LIMIT 0, 140";
$sql2 = mysql_query($sql1) or die(mysql_error());
while ($sql3 = mysql_fetch_assoc($sql2)) {
	$tags = tokenizer($sql3['title'].' '.$sql3['description'], $stopwords);
	$dokumente[] = array($sql3['id'], $tags, $sql3['streamID'], $sql3['date']);
}
for ($i = 0; $i < count($dokumente); $i++) {
	for ($k = $i+1; $k < count($dokumente); $k++) {
		if (count($dokumente[$i][1]) < 3 OR count($dokumente[$k][1]) < 3) { continue; } // zu wenige Wörter
		if ($dokumente[$i][2] == $dokumente[$k][2] && $dokumente[$i][2] != 0) { continue; } // schon im selben Stream
		$relation = cosineSimilarity($dokumente[$i][1], $dokumente[$k][1]);
		if ($relation >= RELATED_THRESHOLD) {
			// BEIDE ARTIKEL IN EINEM STREAM ZUSAMMENFASSEN ANFANG
			$newTime = min(intval($dokumente[$i][3]), intval($dokumente[$k][3]));
			if (!isset($recentStreams[$dokumente[$i][2]]) && !isset($recentStreams[$dokumente[$k][2]])) {
				// beide noch in keinem aktuellen Stream
				$createStream1 = "INSERT INTO _news_streams (time_created, time_origin) VALUES (".time().", ".$newTime.")";
				$createStream2 = mysql_query($createStream1) or die(mysql_error());
				if ($createStream2 !== FALSE) {
					$newStreamID = intval(mysql_insert_id());
					$updateStream1 = "UPDATE _news_articles SET streamID = ".$newStreamID." WHERE id = ".$dokumente[$i][0]." OR id = ".$dokumente[$k][0];
					$updateStream2 = mysql_query($updateStream1) or die(mysql_error());
				}
				else {
					$newStreamID = 0;
				}
			}
			elseif (isset($recentStreams[$dokumente[$i][2]]) && !isset($recentStreams[$dokumente[$k][2]])) {
				// nur i schon in einem aktuellen Stream
				$newStreamID = intval($dokumente[$i][2]);
				$updateStream1 = "UPDATE _news_articles SET streamID = ".$newStreamID." WHERE id = ".$dokumente[$k][0];
				$updateStream2 = mysql_query($updateStream1) or die(mysql_error());		
			}
			elseif (!isset($recentStreams[$dokumente[$i][2]]) && isset($recentStreams[$dokumente[$k][2]])) {
				// nur k schon in einem aktuellen Stream
				$newStreamID = intval($dokumente[$k][2]);
				$updateStream1 = "UPDATE _news_articles SET streamID = ".$newStreamID." WHERE id = ".$dokumente[$i][0];
				$updateStream2 = mysql_query($updateStream1) or die(mysql_error());					
			}
			else {
				// beide schon in einem aktuellen Stream
				$newStreamID = min(intval($dokumente[$i][2]), intval($dokumente[$k][2]));
				$updateStream1 = "UPDATE _news_articles SET streamID = ".$newStreamID." WHERE id = ".$dokumente[$i][0]." OR id = ".$dokumente[$k][0];
				$updateStream2 = mysql_query($updateStream1) or die(mysql_error());
			}
			// BEIDE ARTIKEL IN EINEM STREAM ZUSAMMENFASSEN ENDE
			// STREAM-URSPRUNGSZEIT AKTUALISIEREN ANFANG
			$streamOrigin1 = "UPDATE _news_streams SET time_origin = LEAST(time_origin, ".$newTime.") WHERE id = ".$newStreamID;
			$streamOrigin2 = mysql_query($streamOrigin1) or die(mysql_error());
			// STREAM-URSPRUNGSZEIT AKTUALISIEREN ENDE
		}
	}
}
$timeout = intval(time()-3600*18);
$in3 = "UPDATE _news_articles SET randomSortID = FLOOR(1+RAND()*100) WHERE date > ".$timeout;
$in4 = mysql_query($in3) or die(mysql_error());
$in3 = "UPDATE _news_articles SET randomSortID = 0 WHERE date <= ".$timeout;
$in4 = mysql_query($in3) or die(mysql_error());

?>