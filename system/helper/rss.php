<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
if(preg_match('![^\w/-]!', $_GET['p'])){
	header("HTTP/1.0 404 Not Found");
	die;
}

$parent = isset($pieces[1]) ? $pieces[1] : $_GET['p'];
foreach(Core::$siteConfig['page_types'] as $k => $v){
	if ($v['structure'] == 'tree'){
		$tree_types[] = $k;
	}
}
$tree_types = "'" . implode("','", $tree_types) . "'";

$data = getPages(array("fullpath LIKE '/{$parent}/%'", "type NOT IN ({$tree_types})", "status > 0"), 'id DESC', 10);

if (!$data){
	header("HTTP/1.0 404 Not Found");
	die;
}

$site_title = Core::$config['sites'][$site]['name'];
$site_description = '';
$last = current($data);

$rss = '<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	>
<channel>
	<title>' . $site_title . '</title>
	<atom:link href="http://' . $_SERVER['SERVER_NAME'] . '/rss/' . $_GET['p'] . '" rel="self" type="application/rss+xml" />
	<link>http://' . $_SERVER['SERVER_NAME'] . '</link>
	<description>' . $site_description . '</description>
	<lastBuildDate>' . date('r', strtotime($last['meta:date'])) . '</lastBuildDate>
	<generator>http://sydes.ru</generator>
	<language>' . Core::$siteConfig['locale'] . '</language>
	<sy:updatePeriod>weekly</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>
';
$slash = isset($pieces[1]) ? '/' : '';
foreach ($data as $item){
	$rss .= '	<item>
		<title>' . $item['title'] . '</title>
		<link>' . $_SERVER['SERVER_NAME'] . $slash . $item['fullpath'] . '</link>
		<pubDate>' . date('r', strtotime($item['meta:date'])) . '</pubDate>
		<guid isPermaLink="false">' . $item['id'] . '</guid>
		<description><![CDATA[' . strip_tags($item['content']) . ']]></description>
		<content:encoded><![CDATA[' . $item['content'] . ']]></content:encoded>
	</item>
';
}
$rss .= '</channel>
</rss>';
header("Content-Type: application/xml");
die($rss);
?>