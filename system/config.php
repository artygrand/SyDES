<?php
/**
* SyDES :: system configuration
* @version 1.7
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

define('SYS_DIR', __DIR__ . '/');
define('MOD_DIR', 'modules/');
define('TPL_DIR', 'tpl/');
define('CLASS_DIR', SYS_DIR . 'class/');
define('LANG_DIR', SYS_DIR . 'language/');
define('CACHE_DIR', SYS_DIR . 'cache/');
define('IBLOCK_DIR', SYS_DIR . 'iblocks/');
define('VERSION', '1.7');
define('DEFAULT_MODULE', 'pages');
define('DEFAULT_ACTION', 'view');
define('DB_DRIVER', 'sqlite'); // sqlite or mysql

define('DB_NAME', SYS_DIR . 'database.db'); // full_path_to_db.sqlite or mysql_db_name 

/* MySQL database */
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', '');
define('MYSQL_SERVER', 'localhost');
define('MYSQL_PORT', '3306');
define('MYSQL_PREFIX', 'syd_');

define('DEMO', false);

date_default_timezone_set('Asia/Novosibirsk');
?>