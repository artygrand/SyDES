<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

define('DIR_ROOT', dirname(__FILE__) . '/');
define('DIR_CACHE',  DIR_ROOT . 'cache/');
define('DIR_THUMB',  DIR_ROOT . 'cache/img/');
define('DIR_SITE',   DIR_ROOT . 'site/');
define('DIR_SYS',    DIR_ROOT . 'system/');
define('DIR_VENDOR', DIR_ROOT . 'vendor/');
define('DIR_CORE',    DIR_SYS . 'core/');
define('DIR_IBLOCK',  DIR_SYS . 'iblock/');
define('DIR_MODULE',  DIR_SYS . 'module/');
define('DIR_LIBRARY', DIR_SYS . 'library/');
define('DIR_LANGUAGE',DIR_SYS . 'language/');
define('DIR_PLUGIN',  DIR_SYS . 'plugin/');
define('DIR_TEMPLATE',DIR_ROOT. 'template/');

define('PRESERVE_BASE', true);
define('DEBUG', true);
define('ADMIN', 'admin'); //wanna rename folder?
define('DEFAULT_ADMIN_ROUTE', 'pages');

date_default_timezone_set('Asia/Novosibirsk');

if (DEBUG){
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
}