<?php
function formatLocation($text) {
	if (preg_match('/[0-9]+\.[0-9]+, [0-9]+\.[0-9]+/i', $text)) {
		$url = 'https://maps.google.de/maps?q='.urlencode($text);
		return '<a href="'.$url.'" onclick="window.open(\''.$url.'\'); return false;">&raquo; Karte</a>';
	}
	else {
		return '<a href="/charts/c/'.urlencode($text).'">'.htmlspecialchars($text).'</a>';
	}
}
$user_count = 312152+floor((time()-1340804624)/43200)*1621;
$tw_ranking_message = 'Die <strong>Top-5000</strong> der Twitterer aus <strong>Deutschland, Österreich und der Schweiz</strong>! Für die Charts wurden <strong>'.number_format($user_count, 0, ',', '.').' Twitter-Accounts</strong> untersucht und nach der Anzahl der <abbr title="Fans = Follower - Following">Fans</abbr> sortiert.';
$tw_pagination_prev = '';
$tw_pagination_next = '';
if (isset($_GET['filter_city'])) {
	$tw_ranking_message = '';
	$mark_user = '';
	// PAGINATION ANFANG
	$currentPage = isset($_GET['p']) ? intval($_GET['p']) : 0;
	$limit_length = 50;
	$limit_start = $currentPage*$limit_length;
	if ($currentPage == 1) {
		$tw_pagination_prev = '/charts/c/'.urlencode($_GET['filter_city']);
	}
	elseif ($currentPage > 1) {
		$tw_pagination_prev = '/charts/c/'.urlencode($_GET['filter_city']).'/'.intval($currentPage-1);
	}
	if ($currentPage < 9) {
		$tw_pagination_next = '/charts/c/'.urlencode($_GET['filter_city']).'/'.intval($currentPage+1);
	}
	// PAGINATION ENDE
    if ($_GET['filter_city'] == 'Deutschland' || $_GET['filter_city'] == 'Österreich' || $_GET['filter_city'] == 'Schweiz') {
        $filterWhere = " WHERE country = '".mysql_real_escape_string(trim(strip_tags($_GET['filter_city'])))."'";
    }
    else {
        $filterWhere = " WHERE location = '".mysql_real_escape_string(trim(strip_tags($_GET['filter_city'])))."'";
    }
	$tweetButtonAddition = 'der Twitter-Charts für den Raum '.htmlspecialchars($_GET['filter_city']);
	$tweetLinkAddition = '/c/'.urlencode($_GET['filter_city']);
	$mBodyTitle = 'Twitter-Charts für '.htmlspecialchars($_GET['filter_city']);
}
elseif (isset($_GET['filter_name'])) {
	$nFansHigher1 = "SELECT user_id, fans FROM _news_twitterers WHERE screen_name = '".mysql_real_escape_string(trim(strip_tags($_GET['filter_name'])))."' AND location != '' LIMIT 0, 1";
	$nFansHigher2 = mysql_query($nFansHigher1);
	if ($nFansHigher2 == FALSE) {
		$nFansHigherRows = 0;
	}
	else {
		$nFansHigherRows = mysql_num_rows($nFansHigher2);
	}
	if ($nFansHigherRows == 0) {
		$tw_ranking_message = 'Der User &quot;'.htmlspecialchars($_GET['filter_name']).'&quot; konnte leider nicht gefunden werden. Erwähne <a href="http://twitter.com/news_60">@news_60</a> in einem Tweet, um ins Ranking aufgenommen zu werden! Oder schlage andere User für das Ranking vor, indem Du sie zusammen mit <a href="http://twitter.com/news_60">@news_60</a> erwähnst.';
		$mark_user = '';
		// PAGINATION ANFANG
		$currentPage = isset($_GET['p']) ? intval($_GET['p']) : 0;
		$limit_start = $currentPage*50;
		if ($currentPage == 1) {
			$tw_pagination_prev = '/charts';
		}
		elseif ($currentPage > 1) {
			$tw_pagination_prev = '/charts/'.intval($currentPage-1);
		}
		if ($currentPage < 9) {
			$tw_pagination_next = '/charts/'.intval($currentPage+1);
		}
		// PAGINATION ENDE
		$limit_length = 50;
		$filterWhere = " WHERE location != ''";
		$tweetButtonAddition = 'der deutschen Twitter-Charts';
		$tweetLinkAddition = '';
		$mBodyTitle = 'Deutsche Twitter-Charts';
	}
	else {
		$tw_ranking_message = '';
		$nFansHigher3 = mysql_fetch_assoc($nFansHigher2);
		$nThisFans = intval($nFansHigher3['fans']);
		$nThisID = intval($nFansHigher3['user_id']);
		$getPos1 = "SELECT COUNT(*) FROM _news_twitterers WHERE location != '' AND (fans > ".$nThisFans." OR (fans = ".$nThisFans." AND user_id > ".$nThisID."))";
		$getPos2 = mysql_query($getPos1);
		$getPos3 = mysql_result($getPos2, 0);
		$mark_user = htmlspecialchars(mb_strtolower($_GET['filter_name']));
		$limit_start = (($getPos3-10) > 0) ? $getPos3-10 : 0;
		$limit_length = 20;
		$filterWhere = " WHERE location != ''";
		$tweetButtonAddition = 'der deutschen Twitter-Charts';
		$tweetLinkAddition = '';
		$mBodyTitle = 'Twitter-Charts – Position von '.htmlspecialchars($_GET['filter_name']);
	}
}
else {
	$mark_user = '';
	// PAGINATION ANFANG
	$currentPage = isset($_GET['p']) ? intval($_GET['p']) : 0;
	$limit_start = $currentPage*50;
	if ($currentPage == 1) {
		$tw_pagination_prev = '/charts';
	}
	elseif ($currentPage > 1) {
		$tw_pagination_prev = '/charts/'.intval($currentPage-1);
	}
	if ($currentPage < 99) {
		$tw_pagination_next = '/charts/'.intval($currentPage+1);
	}
	// PAGINATION ENDE
	$limit_length = 50;
	$filterWhere = " WHERE location != ''";
	$tweetButtonAddition = 'der deutschen Twitter-Charts';
	$tweetLinkAddition = '';
	$mBodyTitle = 'Deutsche Twitter-Charts';
}
echo '<h1 class="headline">'.$mBodyTitle.'</h1>';
echo '<div class="content">';
echo '<span id="head_filterName" class="expander"><a href="#" onclick="toggleExpander(this); return false;">&raquo; Person im Ranking suchen</a></span>';
echo '<div id="body_filterName" class="expandableContent"><form action="" method="get" accept-charset="utf-8" onsubmit="window.location.href = \'/charts/n/\'+document.getElementById(\'filter_name\').value; return false;"><p class="inner"><input class="inputHint" type="text" name="filter_name" id="filter_name" value="Nutzername auf Twitter ..." onfocus="inputHintHide(this);" onblur="inputHintShow(this);" /> <input type="submit" value="Position zeigen" onclick="window.location.href = \'/charts/n/\'+document.getElementById(\'filter_name\').value; return false;" /></p></form></div>';
echo '<span id="head_filterCity" class="expander"><a href="#" onclick="toggleExpander(this); return false;">&raquo; Lokale Rankings</a></span>';
echo '<div id="body_filterCity" class="expandableContent"><form action="/" method="get" accept-charset="utf-8" onsubmit="window.location.href = \'/charts/c/\'+document.getElementById(\'filter_city\').value; return false;"><p class="inner"><select name="filter_city" id="filter_city" size="1">';
setlocale(LC_COLLATE, 'de_DE.utf8'); // damit Umlaute korrekt im Alphabet einsortiert werden
ksort($cities_list);
$city_names = array_keys($cities_list);
foreach ($city_names as $city_name) {
	echo '<option>'.$city_name.'</option>';
}
echo '</select> <input type="submit" value="Ranking zeigen" onclick="window.location.href = \'/charts/c/\'+document.getElementById(\'filter_city\').value; return false;" /></p></form></div>';
echo '<span id="head_participate" class="expander"><a href="#" onclick="toggleExpander(this); return false;">&raquo; Fehlende Nutzer melden</a></span>';
echo '<div id="body_participate" class="expandableContent"><p class="inner">Es fehlt noch jemand im Ranking, der wichtig wäre? Schreibe einfach einen Tweet an <a href="http://twitter.com/news_60">@news_60</a>, in dem Du diese Person erwähnst.</p><p class="inner">Der Nutzer musst allerdings als &quot;Standort&quot; in seinem Profil entweder Deutschland, Österreich oder die Schweiz eingetragen haben oder eine Stadt aus der Liste unter &quot;Lokale Rankings&quot;. Ohne diese Angabe können Nutzer nicht aufgenommen werden.</p></div>';
if ($tw_ranking_message != '' && $limit_start == 0) {
	echo '<p class="inner">'.$tw_ranking_message.'</p>';
}
$getTwitterers1 = "SELECT screen_name, real_name, image_url, followers, following, fans, location FROM _news_twitterers".$filterWhere." ORDER BY fans DESC, user_id DESC LIMIT ".$limit_start.", ".$limit_length;
$getTwitterers2 = mysql_query($getTwitterers1);
if (mysql_num_rows($getTwitterers2) < 50) {
	$tw_pagination_next = '';
}
echo '</div>';
echo '<table class="large">';
echo '<thead><tr><th colspan="3">Name</th><th>Fans</th><th>Follower</th><th>Folgt</th><th colspan="2">Ort</th></tr></thead>';
echo '<tbody>';
$rankCounter = $limit_start+1;
while ($getTwitterers3 = mysql_fetch_assoc($getTwitterers2)) {
	$twitter_profile_link = 'http://twitter.com/'.urlencode($getTwitterers3['screen_name']);
	echo '<tr';
	if (mb_strtolower($getTwitterers3['screen_name']) == $mark_user) {
		echo ' class="markedRow"';
	}
	echo '>'; // gehört zu <tr>
	echo '<td class="number">'.$rankCounter.'.</td>';
	echo '<td class="image">';
	if ($getTwitterers3['image_url'] != '') {
		echo '<img src="'.$getTwitterers3['image_url'].'" alt="'.$getTwitterers3['real_name'].'" width="48" onclick="window.open(\''.$twitter_profile_link.'\'); return false;" />';
	}
	echo '</td>';
	echo '<td><a href="'.$twitter_profile_link.'" onclick="window.open(\''.$twitter_profile_link.'\'); return false;">'.$getTwitterers3['real_name'].'</a><br /><span class="light">@'.$getTwitterers3['screen_name'].'</span></td>';
	$getTwitterers3['fans'] = ($getTwitterers3['fans'] < 0) ? 0 : $getTwitterers3['fans'];
	echo '<td class="number">'.number_format($getTwitterers3['fans'], 0, ',', '.').'</td>';
	echo '<td class="number">'.number_format($getTwitterers3['followers'], 0, ',', '.').'</td>';
	echo '<td class="number">'.number_format($getTwitterers3['following'], 0, ',', '.').'</td>';
	echo '<td>'.formatLocation($getTwitterers3['location']).'</td>';
	$tweet_link = 'https://twitter.com/intent/tweet?source=tweetbutton&amp;text='.urlencode('Platz '.number_format($rankCounter, 0, ',', '.').' '.$tweetButtonAddition.': @'.$getTwitterers3['screen_name']).'&amp;url='.urlencode('http://www.news60.de/charts'.$tweetLinkAddition).'&amp;original_referer='.urlencode('http://www.news60.de/');
	echo '<td class="shareIcon"><img src="http://s3.amazonaws.com/news_60/images/twitter_24.png" alt="Teilen auf Twitter" width="16" onclick="window.open(\''.$tweet_link.'\'); return false;" /></a></td>';
	echo '</tr>';
	$rankCounter++;
}
echo '</tbody>';
echo '</table>';
echo '</div>';
echo '<div class="pagination">';
if ($tw_pagination_prev != '') {
	echo '<span class="previous active" onclick="window.location.href = \''.$tw_pagination_prev.'\';">&laquo;</span>';
}
else {
	echo '<span class="previous inactive">&laquo;</span>';
}
if ($tw_pagination_next != '') {
	echo '<span class="next active" onclick="window.location.href = \''.$tw_pagination_next.'\';">&raquo;</span>';
}
else {
	echo '<span class="next inactive">&raquo;</span>';
}
echo '</div>';
?>