<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
foreach(Core::$config['domains'] as $base => $nsite){
	if ($nsite == $site) break; //mystical seeker of base
}
$stmt = Core::$db -> query("SELECT fullpath FROM pages WHERE status > 0 ORDER BY fullpath");
$paths = $stmt -> fetchAll(PDO::FETCH_COLUMN);

$sitemap = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
if(count(Core::$siteConfig['locales']) == 1){
	foreach($paths as $path){
		$sitemap .= '
	<url>
		<loc>http://' . $base . $path . '</loc>
	</url>';
	}
} else {
	foreach(Core::$siteConfig['locales'] as $locale){
		foreach($paths as $path){
			$sitemap .= '
	<url>
		<loc>http://' . $base . '/' . $locale . $path . '</loc>
	</url>' . PHP_EOL;
		}
	}
}
$sitemap .= '</urlset> ';
header("Content-Type: application/xml");
die($sitemap);
?>