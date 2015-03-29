<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

require 'config.php';
require SYS_DIR . 'common.php';
require SYS_DIR . 'core.php';

if (DEBUG){
	ini_set('display_errors',1);
	error_reporting(E_ALL);
}

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

Core::$config = unserialize(file_get_contents(SITE_DIR . 'baseconfig.db'));
$site = $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '\\/');
$site = isset(Core::$config['domains'][$site]) ? Core::$config['domains'][$site] : DEFAULTSITE;
Core::$siteConfig = unserialize(file_get_contents(SITE_DIR . $site . '/config.db'));
Core::$siteConfig['locales'] = Core::$config['sites'][$site]['locales'];

// if site in maintenance mode, then say "See ya later"
if (Core::$siteConfig['maintenance_mode'] == 1 and !in_array(getip(), Core::$config['admin']['admin_ip'])){
	die(Core::$siteConfig['say']);
}

// check for page cache if needed
if (Core::$siteConfig['need_cache']){
	$uri = $_SERVER["REQUEST_URI"];
	if(in_array(ltrim($uri, '/'), Core::$siteConfig['locales'])){
		$crc = md5($uri . '/'); // home page with locale "/ru/" 
	} else {
		$crc = md5($uri); // other pages
	}
	if (is_file(CACHE_DIR . $site . '_' . $crc)){
		include (CACHE_DIR . $site . '_' . $crc);
		die;
	}
}

Core::connect2db($site);

// set site locale
if (count(Core::$siteConfig['locales']) > 1 and isset($_GET['p'])){
	$pieces = explode('/', $_GET['p'], 2);
	if (in_array($pieces[0], Core::$siteConfig['locales'])){
		Core::$siteConfig['locale'] = $pieces[0];
	} else {
		Core::$siteConfig['locale'] = Core::$siteConfig['locales'][0];
	}
} else {
	Core::$siteConfig['locale'] = Core::$siteConfig['locales'][0];
}

// check, what is needed to load, helper or any page?
if (isset($_GET['helper'])){
	$helpers = str_replace(array(HELPER_DIR, '.php'), '', glob(HELPER_DIR . '*.php'));
	if (in_array($_GET['helper'], $helpers)){
		include HELPER_DIR . $_GET['helper'] . '.php';
	}
} elseif (isset($_GET['p'])){
	if (preg_match('![^\w/-]!', $_GET['p'])){
		header("HTTP/1.0 404 Not Found");
		die ('Shoo, shoo!');
	}
	if (count(Core::$siteConfig['locales']) > 1){
		if (in_array($pieces[0], Core::$siteConfig['locales'])){
			if (isset($pieces[1])){
				// get page with current locale
				$page = getPage($pieces[1]);
			} else {
				// get home page with current locale
				$page = getPage(0);
			}
		} else {
			// if is set many locales, but not selected current, redirect to default locale
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: /'. Core::$siteConfig['locales'][0] . '/' . $_GET['p']);
			die;
		}
	} else {
		// get page with default (single) locale
		$page = getPage($_GET['p']);
	}
} else {
	// home page without locale, I think
	if (count(Core::$siteConfig['locales']) > 1){
		// if is set many locales, redirect to default locale
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: /'. Core::$siteConfig['locale']);
		die;
	} else {
		// get home page with default (single) locale
		$page = getPage(0);
	}
}

// if the page exists
if ($page){
	// if this page not from helper, load meta data
	$layout = unserialize(file_get_contents(TEMPLATE_DIR . Core::$siteConfig['template'] . '/layouts.db'));
	$layout = $layout[$page['layout']];
	$template = file_get_contents(TEMPLATE_DIR . Core::$siteConfig['template'] . '/' . $layout['file']);
	$box = array('left','right','top','bottom');
	foreach($box as $key){
		$template = str_replace('{box:' . $key . '}', $layout[$key], $template);
	}
} else {
	header("HTTP/1.0 404 Not Found");
	if (is_file(TEMPLATE_DIR . Core::$siteConfig['template'] . '/404.html')){
		$template = file_get_contents(TEMPLATE_DIR . Core::$siteConfig['template'] . '/404.html');
		$page = array();
	} else {
		die;
	}
}
foreach(Core::$config['domains'] as $base => $nsite){
	if ($nsite == $site) break; //mystical seeker of base
}

// paste content to template 
$template = str_replace('{base}', "http://$base/", $template);
foreach($page as $key => $val){
	$template = str_replace('{' . $key . '}', $val, $template);
}

// use multilaguage
if (preg_match_all('/{lang:([^}]+)}/', $template, $matches)){
	include SITE_DIR. $site . '/language/' . Core::$siteConfig['locale'] . '.php';
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
		if (is_file(IBLOCK_DIR . $matches[1][$ib].'.iblock')){
			$block_content = getIblock($page, $matches[1][$ib], $matches[2][$ib]);
		} else {
			$block_content = 'Missing code of "' . $matches[1][$ib] . '" iblock.';
		}
		$template = str_replace($matches[0][$ib], $block_content, $template);
	}
}

echo $template;
// cache needed only for real pages
if (!isset($_GET['helper']) and Core::$siteConfig['need_cache']){
	file_put_contents(CACHE_DIR . $site . '_' . $crc, $template);
}
?>