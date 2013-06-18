<?php
$plugs = str_replace(array('system/plugin/','.php'), '', glob('system/plugin/*.php'));

//remove www
$pos = stripos($_SERVER["HTTP_HOST"], 'www.');
if ($pos !== false){
	$redirect2host = substr($_SERVER["HTTP_HOST"], 4);
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: http://' . $redirect2host . $_SERVER["REQUEST_URI"]);
	die;
}

//install at first run
if (is_file('install/index.php') and !is_file('system/database.db')){
	header('Location: install/'); die;
}
//load configs
require 'system/config.php';
$conf = unserialize(file_get_contents('system/config.db'));

if ($conf['need_cache'] == 1){
	$uri = $_SERVER["REQUEST_URI"];
	if(in_array(ltrim($uri, '/'), $conf['locale'])){
		$crc = md5($uri . '/'); // home page with locale "/ru/" 
	} else {
		$crc = md5($uri); // other pages
	}
	if (is_file('system/cache/' . $crc)){ 
		include ('system/cache/' . $crc);
		die;
	}
}

//load functions
require_once 'system/common.php';
$db = new PDO('sqlite:' . DB_NAME);

//if site is off, then say "See ya later"
if ($conf['work'] !== 1 and !in_array(getip(), $conf['admin_ip'])){
	die($conf['say']);
}
$base = !$conf['base'] ? getbase('/\\') : $conf['base'];

// check, what is needed to load. plugin or page?
if (in_array(key($_GET), $plugs)){
	include 'system/plugin/' . key($_GET) . '.php';
} elseif (isset($_GET['p'])){
	if(preg_match('![^\w/-]!', $_GET['p'])) die ('Shoo, shoo!');
	if(count($conf['locale']) > 1){
		$pieces = explode('/', $_GET['p'], 2);
		if(in_array($pieces[0], $conf['locale'])){
			$locale = $pieces[0];
			if(isset($pieces[1])){
				// get page with current locale
				$page = getPageData($db, $locale, $pieces[1]);
			} else {
				// get home page with current locale
				$page = getPageData($db, $locale, 1);
			}
		} else {
			// if is set many locales, but not selected current, redirect to default locale
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: /'. $conf['locale'][0] . '/' . $_GET['p']);
			die;
		}
	} else {
		$locale = $conf['locale'][0];
		// get page with default (single) locale
		$page = getPageData($db, $locale, $_GET['p']);
	}
} else {
	// home page without locale, I think
	$locale = $conf['locale'][0];
	if(count($conf['locale']) > 1){
		// if is set many locales, redirect to default locale
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: /'. $locale);
		die;
	} else {
		// get home page with default (single) locale
		$page = getPageData($db, $locale, 1);
	}	
}

//if the page exists, load meta data and template
if ($page){
	$meta = getMetaData($db, $page[0]['id'], $locale);
	$template = file_get_contents('templates/' . $conf['template'] . '/' . $page[0]['template'] . '.html');
} else {
	header("HTTP/1.0 404 Not Found");
	$template = file_get_contents('templates/' . $conf['template'] . '/404.html');
	$meta = array();
}

//paste content to template 
$template = str_replace('{base}', $base, $template);

if ($meta){
	foreach($meta as $key => $val){
		$template = str_replace('{meta:' . $key . '}', $val, $template);
	}
}
if ($page){
	foreach($page[0] as $key => $val){
		$template = str_replace('{' . $key . '}', $val, $template);
	}
}

$template = preg_replace('!{meta:[^}]*}!', '', $template);

//initialize all info-blocks
if (preg_match_all('/{iblock:([^\?]+?)(\?.+)?}/', $template, $matches)){
	for ($ib = 0; $ib <= count($matches[1])-1; $ib++){
		if (is_file('system/iblocks/'.$matches[1][$ib].'.iblock')){
			ob_start();
			if ($matches[2][$ib]){
				$match = str_replace('?', '', $matches[2][$ib]);
				$match = str_replace('&amp;', '&', $match);
				parse_str($match); //string like a "first=value&arr[]=foo+bar&arr[]=baz"
			}
			require 'system/iblocks/' . $matches[1][$ib] . '.iblock';
			$block_content = ob_get_contents();
			ob_end_clean();
		} else {
			$block_content = 'Missing code of "' . $matches[1][$ib] . '" iblock.';
		}
		$template = str_replace($matches[0][$ib], $block_content, $template);
	}
}

echo $template;
if ($page and $conf['need_cache'] == 1){
	file_put_contents('system/cache/' . $crc, $template);
}
?>