<?php
/**
* Just sitemap plugin, for search engines
*
* version 1.0
* author ArtyGrand
*/
$base = substr($base, 0, -1);
$sitemap = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
if(count($conf['locale']) == 1){
	$stmt = $db -> query("SELECT fullpath FROM pages WHERE status = '1' ORDER BY fullpath;");
	$rawLoc = $stmt -> fetchAll(PDO::FETCH_ASSOC);
	foreach($rawLoc as $loc){
		$sitemap .= '   <url>
		  <loc>http://' . $base . $loc['fullpath'] . '</loc>
	   </url>' . PHP_EOL;
	}
} else {
	foreach($conf['locale'] as $loca){
		$stmt = $db -> query("SELECT pages.fullpath FROM pages, pages_content WHERE pages.status = '1' AND pages_content.locale = '{$loca}' AND pages_content.page_id = pages.id");
		$rawLoc = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		foreach($rawLoc as $loc){
			$sitemap .= '   <url>
			  <loc>http://' . $base . '/' . $loca . $loc['fullpath'] . '</loc>
		   </url>' . PHP_EOL;
		}
	}
}
$sitemap .= '</urlset> ';
header("Content-Type: application/xml");
die($sitemap);
?>