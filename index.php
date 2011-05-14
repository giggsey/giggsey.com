<?php

$username = 'BLA';

$dsn = 'mysql:dbname=DBNAME;host=SERVER';
$user = 'user';
$password = '';

try {
    $dbh = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
	error($e);
	exit;
}

$sql = 'SELECT l.link_name, l.link_user, c.icon, c.link_url
    FROM links l
	LEFT JOIN links_config c ON (l.link_name = c.link_name)
    WHERE l.user = :username
	ORDER BY l.order ASC';
$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':username' => $username));

$networks = array();

while ($row = $sth->fetchObject()) {
	$url = str_replace('%user%',$row->link_user,$row->link_url);
	$networks[$row->link_name] = array($url,$row->link_user,$row->icon);
}


	$sql = 'SELECT l.link_name, l.text, l.link, UNIX_TIMESTAMP(l.timeposted) as timeposted, c.icon, c.link_url
    FROM feed l
	LEFT JOIN links_config c ON (l.link_name = c.link_name)
    WHERE l.user = :username
	ORDER BY l.timeposted DESC LIMIT 15';
$sth = $dbh->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
$sth->execute(array(':username' => $username));


$activity = array();
while ($row = $sth->fetch()) {
	$activity[] = activityline($row);
}
	
?><!DOCTYPE html>
<html>
<head>
<title>Activity</title>
<meta charset="utf-8" />
</head>
<body>
<h2>Links</h2>
<?php

foreach ($networks as $img => $details)
{
	echo "<a href='{$details[0]}'><img src='{$details[2]}' alt='{$img}' />
			<div class='network_name'>{$img}</div>
			<div class='network_url'>{$details[1]}</div>
		 </a>\n";

}
?>
<h2>Recent Activity from around the web</h2>
<ul><?php
foreach ($activity as $line)
{
	echo "<li>{$line}</li>\n";
}
?></ul>

</body>
</html><?php
// Functions

function error(Exception $e)
{
	header("HTTP/1.0 503 Service Unavailable");
	echo "<h1>Ooh 'eck</h1>";
    echo '<p>Something\'s gone wrong. Please hold on whilst I fix it</p>';
	echo '<pre style=\'padding: 10px; border: 1px solid red; background-color: #F2FAF3;\'>Connection failed: ' . $e->getMessage() . '</pre>';
	exit;
}

function activityline($row)
{
	$row['text'] = htmlspecialchars($row['text']);
	$date = date('c',$row['timeposted']);
	$friendlytime = date('r',$row['timeposted']);
	return "<img style='vertical-align: middle;' src='{$row['icon']}' alt='{$row['link_name']}' /> <a href='{$row['link']}'>{$row['text']}</a> <time datetime='{$date}'>{$friendlytime}</time>";
}

?>