<?php
/**
* SyDES :: basic class of module
* Contain the basic methods of the engine
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Module{
	public static $allowed4html = array('view', 'edit', 'save', 'delete');
	public static $allowed4ajax = array();
	public static $allowed4demo = array('view', 'edit');
	public $structure; // child module's table structure
	public $lang; // language pack

	function __construct(){
		
	}

}
?>