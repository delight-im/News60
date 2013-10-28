<?php
/*// CONFIG ANFANG
include '/var/www/vhosts/lvps178-77-99-228.dedicated.hosteurope.de/config_twem.php'; // MySQL-Verbindung
header('content-type: application/rss+xml; charset=utf-8');
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Berlin');
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
// CONFIG ENDE
echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
echo '<channel>';
echo '<title>Twem News</title>';
echo '<link>http://www.twem.de/</link>';
echo '<description>Twem filtert News aus den wichtigsten deutschsprachigen Quellen - und zeigt die interessantesten Nachrichten des Tages auf einer einzigen Seite!</description>';
echo '<language>de-de</language>';
echo '<pubDate>'.date('D, d M Y H:i:s O').'</pubDate>';
echo '<atom:link href="http://www.twem.de/_rss" rel="self" type="application/rss+xml" />';
echo '<image>';
echo '<url>http://www.twem.de/images/icon_64.png</url>';
echo '<title>Twem News</title>';
echo '<link>http://www.twem.de/</link>';
echo '</image>';
$cacheData = file_get_contents('__cache.txt');
$data = unserialize($cacheData);
foreach ($data as $sql3) {
	echo '<item>';
	echo '<title>'.$sql3['title'].'</title>';
	echo '<link>http://www.twem.de/'.id2secure($sql3['stream_id']).'</link>';
	echo '<guid isPermaLink="true">http://www.twem.de/'.id2secure($sql3['stream_id']).'</guid>';
	echo '<pubDate>'.date('D, d M Y H:i:s O', $sql3['date']).'</pubDate>';
	echo '</item>';
}
echo '</channel>';
echo '</rss>';*/
?>