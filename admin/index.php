<?php
/**
* SyDES administrative center - main file
* @version 1.7
* @copyright 2011-2012, ArtyGrand (artygrand.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
session_start();

// loading of system files
require '../system/config.php';
require SYS_DIR . 'common.php';
require CLASS_DIR . 'core.php';
require 'functions.php';
require CLASS_DIR . 'module.php';

// Initialize core
$Core = new Core();

// get lost
if (isset($_GET['act']) and $_GET['act'] === 'exit'){
	$Core -> quit();
}
//Load the language file if it exist
require_once LANG_DIR . Core::$language . '/admin.php';

if (DEMO or $Core -> IsAuthorized()){
	// get module
	$module = empty($_GET['mod']) ? DEFAULT_MODULE : $_GET['mod'];
	$module = new $module();

	//get action
	$action = empty($_GET['act']) ? DEFAULT_ACTION : $_GET['act'];
	
	if ($action == DEFAULT_ACTION){
		$Core -> checkModules();
	}

	if (in_array($action, $module::$allowedActions)){
		try {
			$Core -> content = hook($module, $action, $module -> $action());
		} catch (Exception $e) {
			$Core -> redirectTo('?mod=' . $module -> name, $e->getMessage());
		}
		if(isset($Core -> content['redirect'])){
			if(is_array($Core -> content['redirect'])){
				$Core -> redirectTo($Core -> content['redirect']['url'], $Core -> content['redirect']['message'], 'success');
			} else if(isset($_SERVER['HTTP_REFERER'])){
				header('Location:' . $_SERVER['HTTP_REFERER']);
				exit;
			} else{
				$Core -> redirectTo('?mod=' . $module -> name, lang('success'), 'success');
			}
		}
		$Core -> GetHTML('index.html');
	} else {
		$Core -> redirectTo('?mod=' . $module -> name, lang('unauthorized_request'));
	}
} else {
	include 'login.php';
}
?>