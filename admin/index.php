<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

require '../config.php';
require DIR_SYS  . 'bootstrap.php';

$registry->section = 'admin';
$admin = new Admin();
$admin->getSite();

if (!$user->isLoggedIn()){
	$app->route = 'user/login';
} else {
	if (IS_POST && !$user->hasToken()){
		$app->action('common/error/csrf');
	}

	if (empty($registry->request->get['route'])){
		$registry->request->get = array_merge($registry->request->get, array('route' => DEFAULT_ADMIN_ROUTE));
	}

	if ($registry->site){
		$app->config_site = include DIR_SITE . $registry->site . '/config.php';
		$app->connect2db();
		$admin->getLocale();
	}
	$app->getRoute();
}

$app->checkUpdate();
$app->run($admin);