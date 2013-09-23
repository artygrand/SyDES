<?php
$pos = stripos($_SERVER["HTTP_HOST"], 'www.');
$host = $pos !== false ? substr($_SERVER["HTTP_HOST"], 4) : $_SERVER["HTTP_HOST"];
$robots = 'User-agent: *
Disallow: /?*
Disallow: /index.php?*
Sitemap: http://' . $host . '/sitemap.xml

User-agent: Yandex
Disallow: /?*
Disallow: /index.php?*
Host: ' . $host;
header("Content-Type: text/plain");
die ($robots);
?>
