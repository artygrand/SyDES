<?php
/**
* Rss plugin, which get data from posts
*
* version 1.3
* author ArtyGrand
*/

if (issetTable('posts')){
	$stream = properUri($_GET['rss']);
	$stmt = $db -> query("SELECT * FROM posts WHERE stream = '$stream' AND status = '1' ORDER BY id DESC;");
	$data = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	$stmt = $db -> query("SELECT value FROM config_meta WHERE key = 'site_title';");
	$site_title = $stmt -> fetchColumn();
	$stmt = $db -> query("SELECT value FROM config_meta WHERE key = 'site_description';");
	$site_description = $stmt -> fetchColumn();
	$rss = '<?xml version="1.0" encoding="UTF-8"?>
	<rss version="2.0"
		xmlns:content="http://purl.org/rss/1.0/modules/content/"
		xmlns:atom="http://www.w3.org/2005/Atom"
		xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
		>
	<channel>
		<title>' . $site_title . '</title>
		<atom:link href="http://' . $_SERVER['SERVER_NAME'] . '/rss" rel="self" type="application/rss+xml" />
		<link>http://' . $_SERVER['SERVER_NAME'] . '</link>
		<description>' . $site_description . '</description>
		<lastBuildDate>' . $data[0]['mdate'] . '</lastBuildDate>
		<generator>http://sydes.artygrand.ru</generator>
		<language>ru</language>
		<sy:updatePeriod>weekly</sy:updatePeriod>
		<sy:updateFrequency>1</sy:updateFrequency>
	';
	foreach ($data as $item) {
		$rss = $rss.'	<item>
			<title>' . $item['data'] . '</title>
			<link>' . $_SERVER['SERVER_NAME'] . '</link>
			<pubDate>' . $item['mdate'] . '</pubDate>
			<guid isPermaLink="false">' . $item['mdate'] . '</guid>
			<description><![CDATA[' . strip_tags($item['content']) . ']]></description>
			<content:encoded><![CDATA[' . $item['content'] . ']]></content:encoded>
		</item>
	';
	}
	$rss = $rss.'</channel>
	</rss>';
	header("Content-Type: application/xml");
	die($rss);
}
?>