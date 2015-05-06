<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

if (!isset($_SERVER['HTTP_HOST'])){
	header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
	die;
}

session_start();
define('VERSION', '2.1.0-b');

spl_autoload_register(function($class){
	$library_path = DIR_LIBRARY . str_replace('\\', '/', $class) . '.php';
	$vendor_path = DIR_VENDOR . str_replace('\\', '/', $class) . '.php';
	if (file_exists($vendor_path)){
		include_once($vendor_path);
	} elseif (file_exists($library_path)){
		include_once($library_path);
	}
});

set_error_handler(function($errno, $errstr, $errfile, $errline){
	global $registry;
	if (!(error_reporting() & $errno)){
		return;
	}
	switch ($errno){
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;
		default:
			$error = 'Unknown';
			break;
	}
	$registry->response->alert('<b>' . $error . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>', 'danger');
	return true;
});

class BaseException extends Exception{
	public function __construct($message, $status = 'danger', $redirect = ''){
		$response = Registry::getInstance()->response;
		$response->alert($message, $status);
		if (!empty($redirect)){
			$response->redirect($redirect);
		}
		parent::__construct($message);
	}
}

require DIR_SYS  . 'functions.php';
require DIR_CORE . 'hasregistry.php';
require DIR_CORE . 'app.php';
require DIR_CORE . 'model.php';
require DIR_CORE . 'controller.php';

$app = new App();
$app->getLanguage();

$registry = Registry::getInstance();
$registry->request = new Request();
$registry->response = new Response();
$registry->cache = new Cache();
$registry->load = new Loader();
$registry->load->language('common');

$user = new User();
$registry->user = $user;

$plugins = glob(DIR_PLUGIN . '*');
if (!empty($plugins)){
	foreach ($plugins as $plugin){
		include $plugin;
	}
}

if (!file_exists(DIR_SITE . 'config.php')){
	$app->action('user/signup');
}

if (glob(DIR_SITE . 's*', GLOB_ONLYDIR)){
	$app->parseUri();
}

$registry->config_admin = include DIR_SITE . 'config.php';
$user->set($registry->config_admin['user']);

$app->trigger('app.bootstrap');