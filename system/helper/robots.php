<?php
$pos = stripos($_SERVER["HTTP_HOST"], 'www.');
$host = $pos !== false ? substr($_SERVER["HTTP_HOST"], 4) : $_SERVER["HTTP_HOST"];
$robots = 'User-agent: *
Disallow: /?*
Sitemap: http://' . $host . '/sitemap.xml

User-agent: Yandex
Host: ' . $host;
header("Content-Type: text/plain");
die ($robots);
?>
