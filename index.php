<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

require 'config.php';
require DIR_SYS  . 'bootstrap.php';

// pass users to the site in maintenance mode
if (isset($_GET['let_me_in'])){
	setcookie('is_admin', '1', time()+604800, '/');
	$registry->response->redirect();
}

$app->section = 'front';
$front = new Front();
$app->config_site = include DIR_SITE . $app->site . '/config.php';

if ($user->isLoggedIn()){
	$user->is_editor = true;
}

$app->connect2db();

if ($app->config_site['maintenance_mode'] && !isset($app->request->cookie['is_admin'])){
	$app->route = 'common/error/e503';
} else {
	if ($app->config_site['need_cache'] && !IS_POST && !$user->is_editor){ // check for page cache if needed
		$cache = DIR_CACHE . $app->site . '/' . str_replace(array('\\','/',':','*','?','"','<','>','|'), '', $app->uri) . '.html';
		if (is_file($cache)){
			include $cache;
			die;
		}
	}

	$app->parseRequest();
	$app->getRoute();
}

$app->run($front);

if ($app->config_site['need_cache'] && !IS_POST && !$user->is_editor && $app->route != 'common/error/e404'){
	$dir = dirname($cache);
	is_dir($dir) || @mkdir($dir) || die(t('error_cant_create_cache_folder'));
	file_put_contents($cache, $registry->response->body); // cache this page
}