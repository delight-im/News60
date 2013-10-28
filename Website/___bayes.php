<?php
/*include 'zzzzzServer.php';

$nTopics = array();
$pWordPerTopic = array();

$nWordPerTopic1 = "SELECT thema, wort, anzahl FROM ".$prefix."bayes";
$nWordPerTopic2 = mysql_abfrage($nWordPerTopic1);
$nWordPerTopic = array();
while ($nWordPerTopic3 = mysql_fetch_assoc($nWordPerTopic2)) { // Get word counts from database
	if (isset($stopWords[$nWordPerTopic3['wort']])) { continue; }
	if (!isset($nWordPerTopic[$nWordPerTopic3['thema']])) { $nWordPerTopic[$nWordPerTopic3['thema']] = array(); }
	$nWordPerTopic[$nWordPerTopic3['thema']][$nWordPerTopic3['wort']] = $nWordPerTopic3['anzahl'];
}

foreach($nWordPerTopic as $topic => $wordCounts) { // Calculate p(word|topic) = nWord / sum(nWord for every word)
    $nTopic = array_sum($wordCounts); // Get total word count in topic
    $pWordPerTopic[$topic] = array();
    foreach($wordCounts as $word => $count) { $pWordPerTopic[$topic][$word] = $count / $nTopic; } // Calculate p(word|topic)
    $nTopics[$topic] = $nTopic; // Save $nTopic for next step
}

// Calculate p(topic)
$nTotal = array_sum($nTopics);
$pTopics = array();
foreach($nTopics as $topic => $nTopic) { $pTopics[$topic] = $nTopic / $nTotal; }

// BAYES-KLASSIFIKATOR ANFANG
$sql1 = "SELECT id, title FROM ".$prefix."news ORDER BY RAND() LIMIT 0, 10";
$sql2 = mysql_abfrage($sql1);
while ($sql3 = mysql_fetch_assoc($sql2)) {
    $tokens = tokenizer($sql3['title']);
    $pMax = -1;
    $selectedTopic = '';
    foreach($pTopics as $topic => $pTopic) {
        $p = $pTopic;
        foreach($tokens as $word) {
            if (!array_key_exists($word, $pWordPerTopic[$topic])) { continue; }
            $p *= $pWordPerTopic[$topic][$word];
        }
        if ($p > $pMax) {
            $selectedTopic = $topic;
            $pMax = $p;
        }
    }
	echo '<li>'.$sql3['title'].' &raquo; <strong>'.$selectedTopic.'</strong> ('.$pMax.')</li>';
}
// BAYES-KLASSIFIKATOR ENDE*/
?>