<?php
/**
* SyDES :: admin index file
* @version 1.8
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
session_start();

require '../config.php';
require SYS_DIR . 'common.php';
require SYS_DIR . 'admin.php';
require SYS_DIR . 'module.php';
require SYS_DIR . 'user.php';
require 'hook.php';

// configure the instance
$admin = new Admin();

$admin->selectLang();
require 'language/' . Admin::$lang . '.php';

$admin->setMode();

Admin::$config = unserialize(file_get_contents(SITE_DIR . 'baseconfig.db'));

// check user's rights
$user = new User();
if (!empty($_GET['act']) and $_GET['act'] == 'logout'){
	$user->logout();
	Admin::redirectTo('');
}
if (!$user->isAuthorized()){
	die($user->showLoginForm());
}
if (!$user->hasPermission()){
	die(' ');
}

// continue to configure the instance
$admin->selectSite();
$admin->db = new PDO('sqlite:' . SITE_DIR . $admin->site . '/database.db');
$admin->siteConfig = unserialize(file_get_contents(SITE_DIR . $admin->site . '/config.db'));
$admin->selectLocale();

// get module and run the action
$module = empty($_GET['mod']) ? DEFAULT_MODULE : $_GET['mod'];
$action = empty($_GET['act']) ? DEFAULT_ACTION : $_GET['act'];

if (strpos($module, '/') !== false or !is_file("module/{$module}/index.php")){
	Admin::redirectTo('', lang('unauthorized_request'));
}

require "module/{$module}/index.php";
if (!in_array($action, $module::${'allowed4' . Admin::$mode})){
	Admin::redirectTo('', lang('unauthorized_request'));
}
$module = new $module();

try{
	$admin->response = $admin->hook($module, $action, $module->$action());
} catch (Exception $e){
	if (Admin::$mode == 'html'){
		Admin::redirectTo('?mod=' . $module->name, lang($e->getMessage()));
	} else {
		echo json_encode(array('error' => lang($e->getMessage())));
	}
}

$admin->renderPage();
?>