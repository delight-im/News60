<?php include 'zzzzzServer.php'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="de" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="content-style-type" content="text/css" />
<meta name="robots" content="index,follow" />
<link rel="stylesheet" type="text/css" media="all" href="<?php echo MEDIA_BASE_URL; ?>/css/style.css" />
<link rel="icon" href="<?php echo MEDIA_BASE_URL; ?>/images/news60_32.png" type="image/png" />
<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=no" />
<title>News 60</title>
</head>
<body>
<?php
$page = isset($_GET['page']) ? trim($_GET['page']) : '';
echo '<div id="container">';
echo '<div id="title">';
echo '<a id="question" href="/about"><img src="'.MEDIA_BASE_URL.'/images/question_24.png" width="24" alt="Über News 60" title="Über News 60" /></a>';
echo '<span id="home"><a id="homeLink" href="/">News</a> <span id="countdown">60</span></span>';
echo '<a id="twitter" href="http://twitter.com/news_60" onclick="window.open(\'http://twitter.com/news_60\'); return false;"><img src="'.MEDIA_BASE_URL.'/images/twitter_24.png" width="24" alt="Twitter" title="Twitter" /></a>';
echo '</div>';
if ($page == 'about') {
	echo '<div class="center">';
	echo '<h1 class="headline">Über &laquo;News 60&raquo;</h1>';
	echo '<div class="content">';
	echo '<p><strong>Du brauchst viel Zeit, um täglich alle Nachrichten zu verfolgen?</strong><br />&laquo;News 60&raquo; zeigt alles Wichtige vom Tag &mdash; auf einen Blick und in 60 Sekunden! So verpasst Du nichts und brauchst täglich nur 60 Sekunden, um informiert zu bleiben.</p>';
	echo '<p class="inner"><strong>Du bist es leid, die Nachrichten immer auf mehreren Webseiten, im TV und im Radio verfolgen zu müssen?</strong><br />&laquo;News 60&raquo; fasst aktuelle Entwicklungen und Diskussionen zusammen und bietet Dir alle wichtigen Schlagzeilen auf einer Seite &mdash; nach Aktualität und Wichtigkeit geordnet.</p>';
	echo '<p class="inner"><strong>Du hast genug von einseitiger Berichterstattung und möchtest immer mal wieder alternative Darstellungen lesen?</strong><br />&laquo;News 60&raquo; zeigt Dir zu jeder Nachricht verschiedene Quellen, sodass Du wählen kannst, wo Du mehr Details lesen möchtest!</p>';
	echo '<p><strong>Tastaturkürzel:</strong><br />Pfeiltaste links &rarr; vorherigen Artikel zeigen<br />Pfeiltaste rechts &rarr; nächsten Artikel zeigen</p>';
	echo '</div>';
	echo '</div>';
	echo '</div>';
}
elseif ($page == 'contact') {
	echo '<div class="center">';
	echo '<h1 class="headline">Kontakt</h1>';
	echo '<div class="content"><p><img src="/contact.png" width="300" alt="Kontakt" /></p></div>';
	echo '</div>';
	echo '</div>';
}
elseif ($page == 'media') {
	echo '<div class="center">';
	echo '<h1 class="headline">Online-Medien nach Popularität</h1>';
	echo '<div class="content"><p>Die erfolgreichsten deutschen Online-Medien, gemessen an ihrer Popularität auf Facebook und Twitter. Die &quot;Shares&quot; und &quot;Likes&quot; auf Facebook und die &quot;Tweets&quot; auf Twitter jeweils werden pro Artikel gemessen.</p></div>';
	$getSources1 = "SELECT title, host, avg_facebook_like, avg_facebook_share, avg_twitter FROM _news_media ORDER BY (avg_facebook_like+avg_facebook_share+avg_twitter) DESC LIMIT 0, 50";
	$getSources2 = mysql_query($getSources1);
	echo '<table class="large">';
	echo '<thead><tr><th>&nbsp;</th><th>Medium</th><th>Likes (&#216;)</th><th>Shares (&#216;)</th><th>Tweets (&#216;)</th><th>URL</th></tr></thead>';
	echo '<tbody>';
	$rankCounter = 1;
	while ($getSources3 = mysql_fetch_assoc($getSources2)) {
		if (($getSources3['avg_facebook_like']+$getSources3['avg_facebook_share']+$getSources3['avg_twitter']) == 0) { continue; }
		$media_url = htmlspecialchars('http://'.$getSources3['host'].'/');
		echo '<tr>';
		echo '<td class="number">'.$rankCounter.'.</td>';
		echo '<td><a href="'.$media_url.'" onclick="window.open(\''.$media_url.'\'); return false;">'.$getSources3['title'].'</a></td>';
		echo '<td class="number">'.number_format($getSources3['avg_facebook_like'], 1, ',', '.').'</td>';
		echo '<td class="number">'.number_format($getSources3['avg_facebook_share'], 1, ',', '.').'</td>';
		echo '<td class="number">'.number_format($getSources3['avg_twitter'], 1, ',', '.').'</td>';
		echo '<td><a href="'.$media_url.'" onclick="window.open(\''.$media_url.'\'); return false;">'.$getSources3['host'].'</a></td>';
		echo '</tr>';
		$rankCounter++;
	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	echo '</div>';
}
elseif ($page == 'charts') {
	echo '<div class="center">';
	include 'index_twitter_inc.php';
	echo '</div>';
}
else {
	$cache = file_get_contents('__cache.txt');
	if ($cache !== FALSE && $cache != '') {
		$data = json_decode($cache);
		if ($data === FALSE) {
			$data = NULL;
		}
	}
	else {
		$data = NULL;
	}
	if (is_null($data)) {
		echo '<div class="center">';
		echo '<h1 class="headline">Kurze Pause &#8230;</h1>';
		echo '<div class="content"><p style="text-align:center;">&laquo;News 60&raquo; ist für kurze Zeit nicht verfügbar.</p></div>';
		echo '</div>';
	}
	else {
		$additional_html = '';
		$articleCounter = 0;
		foreach ($data as $article) {
			$tweet_link = 'https://twitter.com/intent/tweet?source=tweetbutton&amp;text='.urlencode(mb_substr($article->title, 0, 100).'...').'&amp;url='.urlencode('http://news60.de/_'.id2secure($article->id)).'&amp;via=news_60&amp;original_referer='.urlencode('http://www.news60.de/');
			$output = '<div class="center">';
			$output .= '<h1 class="headline"><a href="'.htmlspecialchars($article->link).'" onclick="window.open(\''.htmlspecialchars($article->link).'\'); return false;">'.htmlspecialchars(strip_tags($article->title)).'</a></h1>';
			if (isset($article->thumbnail_state) && $article->thumbnail_state == THUMBNAIL_READY) {
				$output .= '<div class="content">';
				$output .= '<p><img class="center_full" src="/static/thumbnails/'.id2secure($article->stream_id).'.jpg" alt="Vorschau" width="400" /></p>';
				$output .= '</div>';
			}
			$output .= '<table class="bars">';
			$output .= '<tr><td class="left">Berichterstattung</td><td class="right">seit '.time_rel($article->time_origin).'</td></tr>';
			$output .= '<tr><td class="left">Twitter</td><td class="right">'.intval($article->twitter).' Tweets &mdash; <a rel="nofollow" href="'.$tweet_link.'" onclick="window.open(\''.$tweet_link.'\'); return false;">Jetzt twittern</a></td></tr>';
			$output .= '<tr><td class="left">Facebook</td><td class="right">'.intval($article->facebook_likes).' Likes und '.intval($article->facebook_shares).' Shares</td></tr>';
			$output .= '<tr><td class="left">Weitere Quellen</td><td class="right">'.implode(', ', $article->related_links).'</td></tr>';
			$output .= '<tr><td class="left">Aktuelle Entwicklung</td><td class="right">'.$article->latest_article.'</td></tr>';
			$output .= '</table>';
			$output .= '</div>';
			$output .= '<div class="pagination">';
			if ($articleCounter > 0) {
				$output .= '<span class="previous active" onclick="showEntry(\'entry_'.$articleCounter.'\', -1);">&laquo;</span>';
			}
			else {
				$output .= '<span class="previous inactive">&laquo;</span>';
			}
			if (($articleCounter+1) < ENTRIES_PER_PAGE) {
				$output .= '<span class="next active" onclick="showEntry(\'entry_'.$articleCounter.'\', 1);">&raquo;</span>';
			}
			else {
				$output .= '<span class="next inactive">&raquo;</span>';
			}
			$output .= '</div>';
			if ($articleCounter == 0) {
				echo '<div class="wrapper visible" id="entry_'.$articleCounter.'">'.$output.'</div>';
			}
			elseif ($articleCounter < ENTRIES_PER_PAGE) {
				$additional_html .= '<div class="wrapper invisible" id="entry_'.$articleCounter.'">'.$output.'</div>';
			}
			$articleCounter++;
		}
		echo $additional_html.'</div>';
	}
}
echo '<div id="footer">';
echo '<p><a href="/">Startseite</a> &middot; <a href="/charts">Twitter-Charts</a> &middot; <a href="/media">Leitmedien</a> &middot; <a href="/contact">Kontakt</a> &middot; <a href="https://github.com/delight-im/News60">Open Source</a></p>';
echo '</div>';
echo '</div>';
?>
<script type="text/javascript" src="<?php echo MEDIA_BASE_URL; ?>/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo MEDIA_BASE_URL; ?>/js/scripts.js"></script>
</body>
</html>