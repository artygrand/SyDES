<?php
$robots = "User-agent: *
Disallow: /?*
Disallow: /index.php?*
Sitemap: http://{$_SERVER['HTTP_HOST']}/sitemap.xml

User-agent: Yandex
Disallow: /?*
Disallow: /index.php?*
Host: {$_SERVER["HTTP_HOST"]}";
header("Content-Type: text/plain");
die($robots);
?>
