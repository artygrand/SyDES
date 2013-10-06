<?php
/**
* SyDES :: system configuration
* @version 1.8
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('ROOT_DIR', dirname(__FILE__) . '/');
define('CACHE_DIR', ROOT_DIR . 'cache/');
define('SITE_DIR', ROOT_DIR . 'site/');
define('SYS_DIR', ROOT_DIR . 'system/');
define('TEMPLATE_DIR', ROOT_DIR . 'template/');

define('DEFAULT_MODULE', 'pages');
define('DEFAULT_ACTION', 'view');

define('VERSION', '1.8');
define('DEMO', false);
define('WWW', false);

date_default_timezone_set('Asia/Novosibirsk');
?>