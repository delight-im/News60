<?php

exit;

include 'zzzzzServer.php';
define('EXPORT_FILENAME', 'chartsExport.csv');
define('DATE_START', '2013-06-01');
define('DATE_END', '2013-07-07');

$dateStart = strtotime(DATE_START);
$dateEnd = strtotime(DATE_END);
$datePoints = array();
while ($dateStart <= $dateEnd) {
	$datePoints[] = mysql_real_escape_string(date('Y-m-d', $dateStart));
	$dateStart += 86400;
}
if (count($datePoints) < 1) { exit; }

$datePointsSQL = "'".implode("', '", $datePoints)."'";

$sql1 = "SELECT a.measure_day, b.screen_name, a.followers, a.following, (a.followers-a.following) FROM _news_twitterers_history AS a JOIN _news_twitterers AS b ON a.user_id = b.user_id WHERE a.measure_day IN (".$datePointsSQL.") AND b.location != '' ORDER BY measure_day ASC, b.screen_name ASC";
$sql2 = mysql_query($sql1);
$out = 'Datum,UserName,Followers,Following,Differenz';
$out .= "\n";
while ($sql3 = mysql_fetch_assoc($sql2)) {
	$out .= implode(',', $sql3);
	$out .= "\n";
}
file_put_contents(EXPORT_FILENAME, $out);

?>