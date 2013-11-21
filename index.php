<?php
/**
* SyDES :: index file
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
require 'config.php';
require SYS_DIR . 'common.php';

// www, or not www, that is the question 
$www = stripos($_SERVER["HTTP_HOST"], 'www.');
if((WWW and $www === false) or (!WWW and $www !== false)){
	$r2h = WWW ? 'www.' . $_SERVER["HTTP_HOST"] : substr($_SERVER["HTTP_HOST"], 4);
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: http://' . $r2h . $_SERVER["REQUEST_URI"]);
	die;
}

// some site created?
if (!is_file(SITE_DIR . 'baseconfig.db')){
	header('Location: ' . ADMIN . '/?act=createsite');
	die; // let's create them
}

$baseconfig = unserialize(file_get_contents(SITE_DIR . 'baseconfig.db'));
$site = $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/');
$site = isset($baseconfig['domains'][$site]) ? $baseconfig['domains'][$site] : DEFAULTSITE;
$config = unserialize(file_get_contents(SITE_DIR . $site . '/config.db'));
$config['locale'] = $baseconfig['sites'][$site]['locales'];

// if site in maintenance mode, then say "See ya later"
if ($config['maintenance_mode'] == 1 and !in_array(getip(), $baseconfig['admin']['admin_ip'])){
	die($config['say']);
}

// check for page cache if needed
if ($config['need_cache']){
	$uri = $_SERVER["REQUEST_URI"];
	if(in_array(ltrim($uri, '/'), $config['locale'])){
		$crc = md5($uri . '/'); // home page with locale "/ru/" 
	} else {
		$crc = md5($uri); // other pages
	}
	if (is_file(CACHE_DIR . $site . '_' . $crc)){
		include (CACHE_DIR . $site . '_' . $crc);
		die;
	}
}

$db = new PDO('sqlite:' . SITE_DIR . $site . '/database.db');

// check, what is needed to load, helper or any page?
if (isset($_GET['helper'])){
	$helpers = str_replace(array(SYS_DIR . 'helper/', '.php'), '', glob(SYS_DIR . 'helper/*.php'));
	if (in_array($_GET['helper'], $helpers)){
		include SYS_DIR . 'helper/' . $_GET['helper'] . '.php';
	}
} elseif (isset($_GET['p'])){
	if(preg_match('![^\w/-]!', $_GET['p'])) die ('Shoo, shoo!');
	if(count($config['locale']) > 1){
		$pieces = explode('/', $_GET['p'], 2);
		if(in_array($pieces[0], $config['locale'])){
			$locale = $pieces[0];
			if(isset($pieces[1])){
				// get page with current locale
				$page = getPageData($db, $locale, $pieces[1]);
			} else {
				// get home page with current locale
				$page = getPageData($db, $locale, 0);
			}
		} else {
			// if is set many locales, but not selected current, redirect to default locale
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: /'. $config['locale'][0] . '/' . $_GET['p']);
			die;
		}
	} else {
		$locale = $config['locale'][0];
		// get page with default (single) locale
		$page = getPageData($db, $locale, $_GET['p']);
	}
} else {
	// home page without locale, I think
	$locale = $config['locale'][0];
	if(count($config['locale']) > 1){
		// if is set many locales, redirect to default locale
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: /'. $locale);
		die;
	} else {
		// get home page with default (single) locale
		$page = getPageData($db, $locale, 0);
	}
}

// if the page exists
if ($page){
	// if this page not from helper, load meta data
	if(isset($page[0])){
		$page = $page[0];
		$meta = getMetaData($db, $locale, $page['id']);
	} else {
		$meta = array();
	}
	$layout = unserialize(file_get_contents(TEMPLATE_DIR . $config['template'] . '/layouts.db'));
	$layout = $layout[$page['layout']];
	$template = file_get_contents(TEMPLATE_DIR . $config['template'] . '/' . $layout['file']);
	$blocks = array('left','right','top','bottom');
	foreach($blocks as $key){
		$template = str_replace('{box:' . $key . '}', $layout[$key], $template);
	}
} else {
	header("HTTP/1.0 404 Not Found");
	$template = file_get_contents(TEMPLATE_DIR . $config['template'] . '/404.html');
	$page = array();
	$meta = getMetaData($db, $locale, 0);
}
foreach($baseconfig['domains'] as $base => $nsite){
	if ($nsite == $site) break;
}

// paste content to template 
$template = str_replace('{base}', "http://$base/", $template);
foreach($meta as $key => $val){
	$template = str_replace('{meta:' . $key . '}', $val, $template);
}
foreach($page as $key => $val){
	$template = str_replace('{' . $key . '}', $val, $template);
}

// use multilaguage
if (preg_match_all('/{lang:([^}]+)}/', $template, $matches)){
	include SITE_DIR. $site . '/language/' . $locale . '.php';
	lang('init', $l);
	foreach($matches[1] as $ib => $text){
		$template = str_replace($matches[0][$ib], lang($text), $template);
	}
}

// let's clean the template
$template = preg_replace('/{if[^}]*?{meta:.*?}.*?if}/', '', $template);
$template = preg_replace('!{meta:[^}]*}!', '', $template);
$template = str_replace(array('{if', 'if}'), '', $template);

// initialize all info-blocks
if (preg_match_all('/{iblock:([^\?]+?)(\?.+)?}/', $template, $matches)){
	for ($ib = 0; $ib <= $aib = count($matches[1])-1; $ib++){
		if (is_file(SYS_DIR . 'iblock/'.$matches[1][$ib].'.iblock')){
			if ($matches[2][$ib]){
				$match = str_replace('?', '', $matches[2][$ib]);
				$match = str_replace('&amp;', '&', $match);
				parse_str($match); // string like a "first=value&arr[]=foo+bar&arr[]=baz"
			}
			ob_start();
			include SYS_DIR . 'iblock/' . $matches[1][$ib] . '.iblock';
			$block_content = ob_get_contents();
			ob_end_clean();
		} else {
			$block_content = 'Missing code of "' . $matches[1][$ib] . '" iblock.';
		}
		$template = str_replace($matches[0][$ib], $block_content, $template);
	}
}

echo $template;
// cache needed only for real pages
if (!empty($meta) and $config['need_cache']){
	file_put_contents(CACHE_DIR . $site . '_' . $crc, $template);
}
?>