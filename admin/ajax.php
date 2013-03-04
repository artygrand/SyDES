<?php
/**
* SyDES administrative center - ajax connector
* @version 1.0
* @copyright 2011-2012, ArtyGrand (artygrand.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
session_start();

// some little security
if(!isset($_SERVER['HTTP_REFERER']) or strpos($_SERVER['HTTP_REFERER'], '://' . $_SERVER['SERVER_NAME'] . '/') === false or strpos($_SERVER['HTTP_REFERER'], '://' . $_SERVER['SERVER_NAME'] . '/') > 5){
	die('{"error":"Referrer not passed"}');
}

// loading of system files
require 'config.php';
require SYS_DIR . 'common.php';
require CLASS_DIR . 'core.php';
require CLASS_DIR . 'module.php';

// Initialize core
$Core = new Core();

if (DEMO or $Core -> IsAjaxAuthorized()){
	//Load the language file if it exist
	require_once LANG_DIR . Core::$language . '/admin.php';

	// register the autoload function
	spl_autoload_register(array($Core, 'loadModule'));

	// get module
	if (empty($_GET['mod'])){
		die(' ');
	} else {
		$module = $_GET['mod'];
		$module = new $module();
	}
	
	//get action
	if (empty($_GET['act'])){
		die(' ');
	} else {
		$action = $_GET['act'];
	}

	if (in_array($action, $module::$allowedAjaxActions)){
		try {
			echo json_encode($module -> $action());
		} catch (Exception $e) {
			$message['error'] = $e->getMessage();
			echo json_encode($message);
		}
	} else {
		echo json_encode(array('error'=>lang('unauthorized_request')));
	}
}
?>