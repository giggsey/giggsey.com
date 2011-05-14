<?php

// Feed Bot for giggsey.com
// Designed to run every few minutes



$dsn = 'mysql:dbname=DBNAME;host=SERVER';
$user = 'user';
$password = '';

try {
    $dbh = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
	print_r($e);
	exit;
}
$sql = "SELECT l.id, l.link_name, l.user, l.link_user, UNIX_TIMESTAMP(l.feed_lastaccessed) as feed_lastaccessed, c.update_frequency, l.feed_lastid, REPLACE(c.feed_url,'%user%',l.link_user) as feed
FROM links l
LEFT JOIN links_config c ON (l.link_name = c.link_name)
WHERE c.feed_url != 'NULL' AND l.feed_lastaccessed < CURRENT_TIMESTAMP()
ORDER BY l.order ASC";

try {
$sth = $dbh->prepare($sql);
$sth->execute();
}
catch (Exception $e)
{
	print_r($e);
	exit;
}
while ($row = $sth->fetch())
{
	if ((time() - $row['update_frequency']) < $row['feed_lastaccessed'])
	{
		echo 'Skipping ' . $row['link_name'] . " as it ran recently...<br />\n";
		continue;
	}
	$info = getcustom($row['feed'],$row['link_name'],$row['feed_lastid']);
	
	$lastid = $info[0];
	foreach ($info[1] as $i)
	{
		$title = $i[1];
		$link = $i[2];
		$date = $i[0];
		
		$sql = "INSERT INTO `feed` (`link_name`, `text`, `link`, `timeposted`, `user`)
		VALUES (:link_name, :text, :link, FROM_UNIXTIME(:time), :who);";
		$sths = $dbh->prepare($sql);
		$sths->execute(array(':link_name' => $row['link_name'],
							':text' => $title,
							':link' => $link,
							':time' => $date,
							':who' => $row['user'],
							)
					  );
		echo 'Inserted ' . $row['link_name'] . ' - ' . $title . "<br>\n";
	}
	
		$sql = "UPDATE links SET feed_lastaccessed = CURRENT_TIMESTAMP(), feed_lastid = :lastid WHERE id = :id";
		$sths = $dbh->prepare($sql);
		$sths->execute(array(':lastid' => $lastid,
							':id' => $row['id'],
							)
					  );	
		echo 'Updated link #' . $row['id'] . "<br>\n";
}




function getcustom($url,$link,$lastid)
{
	$ret = @file_get_contents($url);
	if ($ret == false) return array();
	$xml = new SimpleXMLElement($ret);

	// Pass us to the handler function
	$link = str_replace('.','',$link);
	$link = str_replace(' ','',$link);
	if (!function_exists('parse_' . $link)) return array();
	
	return call_user_func('parse_' . $link, $xml, $lastid);
}

function parse_Twitter(SimpleXMLElement $xml,$last)
{
	$custom = array();
	$skiprest = false;
	$fid = null;
	foreach( $xml->status as $status )
	{
		if ($skiprest == true)
			continue;
		$id = (string)$status->id;
		if ($fid == null)
			$fid = $id;
		if ($id == $last) {
			$skiprest = true;
			continue;
		}
		$title = (string)$status->text;
		$date = strtotime( $status->created_at );
		$link = 'http://twitter.com/' . $status->user->screen_name . '/status/' . $status->id;
		$custom[] = array($date,$title,$link);
	}
	return array($fid,$custom);
}


function parse_Lastfm(SimpleXMLElement $xml,$last)
{
	$custom = array();
	$skiprest = false;
	$fid = null;
	$limit = 5;
	$iiii = 0;
	foreach( $xml->channel->item as $status )
	{
		$iiii++;
		if ($skiprest == true)
			continue;
		if ($iiii == $limit)
			$skiprest = true;
		$id = (string)$status->link;
		if ($fid == null)
			$fid = $id;
		if ($id == $last) {
			$skiprest = true;
			continue;
		}
		$title = (string)$status->title;
		$date = strtotime( $status->pubDate );
		$link = (string)$status->link;
		$custom[] = array($date,$title,$link);
	}
	return array($fid,$custom);
}

function parse_Youtube(SimpleXMLElement $xml,$last)
{
	$custom = array();
	$skiprest = false;
	$fid = null;
	foreach( $xml->channel->item as $status )
	{
		if ($skiprest == true)
			continue;
		$id = (string)$status->guid;
		if ($fid == null)
			$fid = $id;
		if ($id == $last) {
			$skiprest = true;
			continue;
		}
		$title = (string)$status->title;
		$date = strtotime( $status->pubDate );
		$link = (string)$status->link;
		$custom[] = array($date,$title,$link);
	}
	return array($fid,$custom);
}

function parse_Picasa(SimpleXMLElement $xml,$last)
{
	$custom = array();
	$skiprest = false;
	$fid = null;
	foreach( $xml->channel->item as $status )
	{
		if ($skiprest == true)
			continue;
		$id = (string)$status->guid;
		if ($fid == null)
			$fid = $id;
		if ($id == $last) {
			$skiprest = true;
			continue;
		}
		$title = (string)$status->title;
		$date = strtotime( $status->pubDate );
		$link = (string)$status->link;
		$custom[] = array($date,$title,$link);
	}
	return array($fid,$custom);
}