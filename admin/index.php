<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
session_start();

require '../config.php';
require SYS_DIR . 'common.php';
require SYS_DIR . 'core.php';
require SYS_DIR . 'admin.php';
require SYS_DIR . 'module.php';
require SYS_DIR . 'user.php';
require 'hook.php';

if (DEBUG){
	ini_set('display_errors',1);
	error_reporting(E_ALL);
}

$admin = new Admin();

$admin->setLang();
require 'language/' . Admin::$lang . '.php';
lang('init', $l);
$admin->setMode();

if (file_exists(SITE_DIR . 'baseconfig.db')){
	Admin::$config = unserialize(file_get_contents(SITE_DIR . 'baseconfig.db'));
} else {
	User::createUser();
	Admin::createSite(DEFAULTSITE);
}

// check user's rights
$user = new User();
if (!empty($_GET['act']) and $_GET['act'] == 'logout'){
	$user->logout();
	redirect('');
}
if (!$user->isAuthorized()) die($user->showLoginForm());
if (!$user->hasPermission()) die(json_encode(array('error' => lang('unauthorized_request'))));

// continue to configure the instance
$admin->setSite();
Admin::connect2db(Admin::$site);
Admin::$siteConfig = unserialize(file_get_contents(SITE_DIR . Admin::$site . '/config.db'));
$admin->setLocale();

// get module and run the action
$module = empty($_GET['mod']) ? DEFAULT_MODULE : $_GET['mod'];
$action = empty($_GET['act']) ? DEFAULT_ACTION : $_GET['act'];

if (strpos($module, '/') !== false or !is_file("module/$module/$module.php")){
	redirect('', lang('unauthorized_request'));
}

require "module/$module/$module.php";
if (!in_array($action, $module::${'allowed4' . Admin::$mode})){
	redirect('', lang('unauthorized_request'));
}
if (file_exists('module/' . $module . '/language/' . Admin::$lang . '.php')){
	require 'module/' . $module . '/language/' . Admin::$lang . '.php';
	lang('add', $l);
} elseif(file_exists("module/$module/language/en.php")){
	require "module/$module/language/en.php";
	lang('add', $l);
}
$module = new $module();

try{
	$admin->execute($module, $action);
} catch (Exception $e){
	if (Admin::$mode == 'ajax'){
		die(json_encode(array('error' => lang($e->getMessage()))));
	} else {
		redirect('?mod=' . $module->name, lang($e->getMessage()));
	}
}
$admin->renderPage();
?>