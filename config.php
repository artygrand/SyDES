<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

define('ROOT_DIR', dirname(__FILE__) . '/');
define('THUMB_DIR', ROOT_DIR . 'thumb/');
define('CACHE_DIR', ROOT_DIR . 'cache/');
define('SITE_DIR', ROOT_DIR . 'site/');
define('SYS_DIR', ROOT_DIR . 'system/');
define('IBLOCK_DIR', ROOT_DIR . 'iblock/');
define('HELPER_DIR', ROOT_DIR . 'system/helper/');
define('CLASS_DIR', ROOT_DIR . 'system/lib/');
define('TEMPLATE_DIR', ROOT_DIR . 'template/');

define('DEFAULT_MODULE', 'pages');
define('DEFAULT_ACTION', 'view');

define('VERSION', '2.0');
define('ADMIN', 'admin'); //wanna rename folder?
define('DEFAULTSITE', 'default'); 
define('DEMO', false);
define('WWW', false);
define('DEBUG', true);

date_default_timezone_set('Asia/Novosibirsk');
?>