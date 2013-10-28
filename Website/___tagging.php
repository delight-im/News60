<?php
/*include 'zzzzzServer.php';
$get1 = "SELECT id, title FROM ".$prefix."news WHERE thema = '' AND id = 443 LIMIT 0, 150";
$get2 = mysql_query($get1);
// pTOPICS BEGIN
$pTopics1 = "SELECT thema, SUM(anzahl) AS anzahl FROM ".$prefix."bayes WHERE thema != '' GROUP BY thema";
$pTopics2 = mysql_query($pTopics1);
$pTopics = array();
while ($pTopics3 = mysql_fetch_assoc($pTopics2)) {
	$pTopics[$pTopics3['thema']] = $pTopics3['anzahl'];
}
// pTOPICS END
// pWORDS BEGIN
$pWords1 = "SELECT wort, thema, anzahl FROM ".$prefix."bayes";
$pWords2 = mysql_query($pWords1);
$pWords = array();
while ($pWords3 = mysql_fetch_assoc($pWords2)) {
	if (!isset($pWords[$pWords3['thema']])) {
		$pWords[$pWords3['thema']] = array();
	}
	$pWords[$pWords3['thema']][$pWords3['wort']] = $pWords3['anzahl'];
}
// pWORDS END
while ($get3 = mysql_fetch_assoc($get2)) {
	echo '<h1>'.$get3['title'].'</h1>';
	$pTextInTopics = array();
	$tokens = tokenizer($get3['title']);
	foreach ($pTopics as $topic=>$documentsInTopic) {
		echo '<p>#'.$topic.'<br />';
		if (!isset($pTextInTopics[$topic])) { $pTextInTopics[$topic] = 1; }
		foreach ($tokens as $token) {
			echo '....'.$token;
			if (isset($pWords[$topic][$token])) {
				echo ' OK';
				$pTextInTopics[$topic] *= $pWords[$topic][$token]/array_sum($pWords[$topic]);
			}
			echo '<br />';
		}
		$pTextInTopics[$topic] *= $pTopics[$topic]/array_sum($pTopics); // #documentsInTopic / #allDocuments
		echo '</p>';
	}
	asort($pTextInTopics); // pick topic with lowest value
	if ($chosenTopic = each($pTextInTopics)) {
		echo '<p>The text belongs to topic '.$chosenTopic['key'].' with a likelihood of '.$chosenTopic['value'].'</p>';
	}
}*/
?>