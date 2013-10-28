<?php
error_reporting(E_ALL);
include '/var/www/vhosts/lvps178-77-99-228.dedicated.hosteurope.de/config_twem.php'; // MySQL-Verbindung
header('Content-type: text/html; charset=utf-8');
header('Cache-Control: no-cache');
mb_internal_encoding('utf-8');
date_default_timezone_set('Europe/Berlin');
@session_start();
define('THUMBNAIL_NONE', 0);
define('THUMBNAIL_REQUESTED', 1);
define('THUMBNAIL_CREATED', 2);
define('THUMBNAIL_READY', 3);
define('NUMBER_RELATED_LINKS', 2);
define('ENTRIES_PER_PAGE', 10);
define('MEDIA_BASE_URL', 'http://d1fioj6kcl2k6i.cloudfront.net');
// FEHLERMELDUNGEN ANFANG
function fehlermeldung($errfehler, $errbeschreibung, $errdatei, $errzeile) {
	$php_fehler1 = "INSERT INTO php_fehler (datei, zeile, beschreibung, zeit) VALUES ('".mysql_real_escape_string($errdatei)."', '".mysql_real_escape_string($errzeile)."', '".mysql_real_escape_string($errbeschreibung)."', ".time().")";
	mysql_query($php_fehler1);
}
set_error_handler('fehlermeldung');
// FEHLERMELDUNGEN ENDE
function id2secure($old_number) {
	$alphabet = '23456789bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';
	// no 0, 1, a, e, i, o, u in alphabet to avoid offensive words (which need vowels)
	$new_number = '';
	while ($old_number > 0) {
		$rest = $old_number%33;
		if ($rest >= 33) { return FALSE; }
		$new_number .= $alphabet[$rest];
		$old_number = floor($old_number/33);
	}
	$new_number = strrev($new_number);
	return $new_number;
}
function secure2id($new_number) {
	$alphabet = '23456789bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';
	// no 0, 1, a, e, i, o, u in alphabet to avoid offensive words (which need vowels)
	$old_number = 0;
	$new_number = strrev($new_number);
	$len = strlen($new_number);
	$n = 0;
	$base = 1;
	while($n < $len) {
		$c = $new_number[$n];
		$index = strpos($alphabet, $c);
		if ($index === FALSE) { return FALSE; }
		$old_number += $base*$index;
		$base *= 33;
		$n++;
	}
	return $old_number;
}
function time_rel($zeitstempel) {
	$ago = time()-$zeitstempel;
    if ($ago < 60) { $agos = 'kurzem'; }
    elseif ($ago < 3600) { $ago1 = round($ago/60, 0); if ($ago1 == 1) { $agos = '1 Minute'; } else { $agos = $ago1.' Minuten'; } }
    elseif ($ago < 86400) { $ago1 = round($ago/3600, 0);  if ($ago1 == 1) { $agos = '1 Stunde'; } else { $agos = $ago1.' Stunden'; } }
    else { $ago1 = round($ago/86400, 0);  if ($ago1 == 1) { $agos = '1 Tag'; } else { $agos = $ago1.' Tagen'; } }
	return $agos;
}
function Chance_Percent($chance, $universe = 100) {
	$chance = abs(intval($chance));
	$universe = abs(intval($universe));
	if (mt_rand(1, $universe) <= $chance) {
		return true;
	}
	return false;
}
function getPhrases($words, $maxTerms = 2) {
	$compositions = array();
    for ($start = 0; $start < count($words); $start++) {
       for ($len = 1; $len <= $maxTerms && $len <= count($words)-$start; $len++) {
          $compositions[] = implode(" ", array_slice($words, $start, $len));
       }
    }
	return $compositions;
}
function tokenizer($text, $stopwords = array()) {
    $text = trim(mb_strtolower($text, 'UTF-8'));
    $text = preg_replace('((mailto\:|(news|(ht|f)tp(s?))\://){1}\S+)', '', $text); // Links entfernen
    $result = preg_split('/[^a-z0-9äöüß]/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $ergebnis = array();
    for ($i = 0; $i < count($result); $i++) {
    	$aktuell = trim($result[$i]);
    	if (mb_strlen($aktuell) < 3) { continue; }
    	if (mb_strlen($aktuell) > 30) { continue; }
		if (isset($stopwords[md5($aktuell)])) { continue; }
        $ergebnis[] = $aktuell;
    }
    return $ergebnis; // contains the single words
}
function link_extractor($s) {
    $gefundene_links = array();
    $treffer = array();
    preg_match_all('((mailto\:|(news|(ht|f)tp(s?))\://){1}\S+)', $s, $treffer);
    $treffer = $treffer[0];
    if (count($treffer) == 0) { return FALSE; }
    foreach ($treffer as $treff) {
        $treff = trim(strip_tags($treff));
        if (substr($treff, -1) == '.') {
            $treff = substr($treff, 0, -1);
        }
        $treff = split('#', $treff);
        $treff = $treff[0];
        $gefundene_links[] = unshorten_url($treff);
    }
    return $gefundene_links;
}
function in_blacklist($text, $list) {
	$meldung = FALSE;
	foreach ($list as $eintrag) {
		if (strpos($text, $eintrag) !== FALSE) {
			$meldung = TRUE;
		}
	}
	return $meldung;
}
function cosineSimilarity($tokensA, $tokensB) {
    $a = $b = $c = 0;
    $uniqueTokensA = $uniqueTokensB = array();
    $uniqueMergedTokens = array_unique(array_merge($tokensA, $tokensB));
    foreach ($tokensA as $token) $uniqueTokensA[$token] = 0;
    foreach ($tokensB as $token) $uniqueTokensB[$token] = 0;
    foreach ($uniqueMergedTokens as $token) {
        $x = isset($uniqueTokensA[$token]) ? 1 : 0;
        $y = isset($uniqueTokensB[$token]) ? 1 : 0;
        $a += $x * $y;
        $b += $x;
        $c += $y;
    }
    return $b * $c != 0 ? $a / sqrt($b * $c) : 0;
}
function cosineSimilarity2($terms_in_article1, $terms_in_article2) { // cosine similarity
    $counts1 = array_count_values($terms_in_article1);
    $counts2 = array_count_values($terms_in_article2);
    $totalScore = 0;
    $unique_terms = array_unique($terms_in_article2);
    foreach ($unique_terms as $term) {
        if (isset($counts1[$term])) $totalScore += $counts1[$term] * $counts2[$term];
    }
    $relation = $totalScore/(count($terms_in_article1)*count($terms_in_article2));
    return $relation;
}
function bigintval($value) {
    $value = trim($value);
    if (ctype_digit($value)) {
    	return $value;
    }
    $value = preg_replace("/[^0-9](.*)$/", '', $value);
    if (ctype_digit($value)) {
    	return $value;
    }
    return 0;
}
// LISTE DER STÄDTE ANFANG
$cities_list = array();
// Städte in Deutschland
$cities_list['Mainz'] = array(array('Mainz'), 'Deutschland');
$cities_list['Essen'] = array(array('Essen'), 'Deutschland');
$cities_list['Berlin'] = array(array('Berlin', 'Kreuzberg', 'Neukölln'), 'Deutschland');
$cities_list['München'] = array(array('München', 'Munich', 'Muenchen', 'Unterföhring'), 'Deutschland');
$cities_list['Frankfurt'] = array(array('Frankfurt'), 'Deutschland');
$cities_list['Freiburg'] = array(array('Freiburg'), 'Deutschland');
$cities_list['Hamburg'] = array(array('Hamburg'), 'Deutschland');
$cities_list['Bonn'] = array(array('Bonn'), 'Deutschland');
$cities_list['Köln'] = array(array('Köln', 'Cologne', 'Koeln'), 'Deutschland');
$cities_list['Stuttgart'] = array(array('Stuttgart'), 'Deutschland');
$cities_list['Wiesbaden'] = array(array('Wiesbaden'), 'Deutschland');
$cities_list['Karlsruhe'] = array(array('Karlsruhe'), 'Deutschland');
$cities_list['Ingolstadt'] = array(array('Ingolstadt'), 'Deutschland');
$cities_list['Erfurt'] = array(array('Erfurt'), 'Deutschland');
$cities_list['Münster'] = array(array('Münster', 'Muenster'), 'Deutschland');
$cities_list['Aachen'] = array(array('Aachen', 'Aix-la-Chapelle'), 'Deutschland');
$cities_list['Leipzig'] = array(array('Leipzig'), 'Deutschland');
$cities_list['Potsdam'] = array(array('Potsdam'), 'Deutschland');
$cities_list['Gelsenkirchen'] = array(array('Gelsenkirchen', 'Schalke'), 'Deutschland');
$cities_list['Düsseldorf'] = array(array('Düsseldorf', 'Duesseldorf'), 'Deutschland');
$cities_list['Hannover'] = array(array('Hannover'), 'Deutschland');
$cities_list['Baden-Baden'] = array(array('Baden-Baden'), 'Deutschland');
$cities_list['Bad Kreuznach'] = array(array('Bad Kreuznach'), 'Deutschland');
$cities_list['Kassel'] = array(array('Kassel'), 'Deutschland');
$cities_list['Oldenburg'] = array(array('Oldenburg'), 'Deutschland');
$cities_list['Rostock'] = array(array('Rostock'), 'Deutschland');
$cities_list['Duisburg'] = array(array('Duisburg'), 'Deutschland');
$cities_list['Bremen'] = array(array('Bremen'), 'Deutschland');
$cities_list['Nürnberg'] = array(array('Nürnberg'), 'Deutschland');
$cities_list['Ratingen'] = array(array('Ratingen'), 'Deutschland');
$cities_list['Usedom'] = array(array('Usedom'), 'Deutschland');
$cities_list['Sylt'] = array(array('Sylt'), 'Deutschland');
$cities_list['Ulm'] = array(array('Ulm'), 'Deutschland');
$cities_list['Dortmund'] = array(array('Dortmund'), 'Deutschland');
$cities_list['Dresden'] = array(array('Dresden'), 'Deutschland');
$cities_list['Bochum'] = array(array('Bochum'), 'Deutschland');
$cities_list['Wuppertal'] = array(array('Wuppertal'), 'Deutschland');
$cities_list['Bielefeld'] = array(array('Bielefeld'), 'Deutschland');
$cities_list['Mannheim'] = array(array('Mannheim'), 'Deutschland');
$cities_list['Augsburg'] = array(array('Augsburg'), 'Deutschland');
$cities_list['Mönchengladbach'] = array(array('Mönchengladbach'), 'Deutschland');
$cities_list['Braunschweig'] = array(array('Braunschweig'), 'Deutschland');
$cities_list['Chemnitz'] = array(array('Chemnitz'), 'Deutschland');
$cities_list['Kiel'] = array(array('Kiel'), 'Deutschland');
$cities_list['Offenbach'] = array(array('Offenbach'), 'Deutschland');
$cities_list['Ludwigshafen'] = array(array('Ludwigshafen'), 'Deutschland');
$cities_list['Markt Schwaben'] = array(array('Markt Schwaben'), 'Deutschland');
$cities_list['Lübeck'] = array(array('Lübeck'), 'Deutschland');
$cities_list['Bergisch Gladbach'] = array(array('Bergisch Gladbach'), 'Deutschland');
$cities_list['Oberhausen'] = array(array('Oberhausen'), 'Deutschland');
$cities_list['Krefeld'] = array(array('Krefeld'), 'Deutschland');
$cities_list['Heidelberg'] = array(array('Heidelberg'), 'Deutschland');
$cities_list['Hofheim am Taunus'] = array(array('Hofheim am Taunus'), 'Deutschland');
$cities_list['Ruhrgebiet'] = array(array('Ruhrgebiet', 'Ruhrpott', 'Pott'), 'Deutschland');
$cities_list['Marienheide'] = array(array('Marienheide'), 'Deutschland');
// Städte in Österreich
$cities_list['Wien'] = array(array('Wien', 'Vienna'), 'Österreich');
$cities_list['Graz'] = array(array('Graz'), 'Österreich');
$cities_list['Linz'] = array(array('Linz'), 'Österreich');
$cities_list['Salzburg'] = array(array('Salzburg'), 'Österreich');
$cities_list['Innsbruck'] = array(array('Innsbruck'), 'Österreich');
$cities_list['Klagenfurt'] = array(array('Klagenfurt', 'Vienna'), 'Österreich');
// Städte in der Schweiz
$cities_list['Winterthur'] = array(array('Winterthur', 'Winterthour'), 'Schweiz');
$cities_list['St. Gallen'] = array(array('St. Gallen', 'Saint-Gall'), 'Schweiz');
$cities_list['Luzern'] = array(array('Luzern'), 'Schweiz');
$cities_list['Zürich'] = array(array('Zürich', 'Zurich', 'Zuerich'), 'Schweiz');
$cities_list['Basel'] = array(array('Basel', 'Bâle'), 'Schweiz');
$cities_list['Bern'] = array(array('Bern'), 'Schweiz');
$cities_list['Lausanne'] = array(array('Lausanne'), 'Schweiz');
$cities_list['Genf'] = array(array('Genf', 'Geneva', 'Genève'), 'Schweiz');
// Länder
$cities_list['Deutschland'] = array(array('Deutschland', 'Germany', 'Nordrhein-Westfalen', 'Sachsen-Anhalt', 'Bayern', 'Schleswig-Holstein', 'Baden-Württemberg'), 'Deutschland');
$cities_list['Österreich'] = array(array('Österreich', 'Austria'), 'Österreich');
$cities_list['Schweiz'] = array(array('Schweiz', 'Switzerland', 'Suisse'), 'Schweiz');
// LISTE DER STÄDTE ENDE
?>