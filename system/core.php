<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
class Core{
	public static $config = array();
	public static $siteConfig = array();
	public static $db = '';
	public static $site = DEFAULTSITE;
	public static $locale = 'en';
	
	public static function connect2db($site){
		self::$db = new PDO('sqlite:' . SITE_DIR . $site . '/database.db');
		self::$db->exec('SET NAMES "utf8"');
		self::$db->exec('SET time_zone = "'. date_default_timezone_get() .'"');
	}
}
?>