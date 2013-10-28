<?php
include 'zzzzzServer.php';
$article = isset($_GET['id']) ? intval(secure2id(trim($_GET['id']))) : 0;
$sql1 = "SELECT link FROM _news_articles WHERE id = ".$article;
$sql2 = mysql_query($sql1);
if (mysql_num_rows($sql2) == 1) {
	header('Location: '.mysql_result($sql2, 0));
	exit;
}
header('Location: /');
exit;
?>