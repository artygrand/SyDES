<?php
/**
* SyDES :: box module for manage all pages
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

class Pages extends Module{
	public $name = 'pages';
	public $response = array();
	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowed4html = array('view', 'edit', 'save', 'delete', 'toggle', 'remove', 'recover', 'clearcache');

	/**
	* Sets the allowed actions for user over AJAX
	* @var array
	*/
	public static $allowed4ajax = array('loadchilds', 'setnewalias', 'setnewparent', 'toggle', 'remove', 'recover', 'metaadd', 'metaupdate', 'metadelete', 'clearcache', 'modal');
	
	/**
	* Sets the allowed actions for demo user
	* @var array
	*/
	public static $allowed4demo = array('view');



	public function view(){
		//$out = render('module/pages/tpl/main.php', array('text' => 'a это текст модуля'));
		//Admin::$config['domains']['www.sy.des'] = 'default';


		$out = '<pre>' . print_r(Admin::$config, true)  . '</pre>';
		//$out .= serialize(Admin::$config);

		return array('title' => 'Редактирование страницы', 'contentCenter' => $out);
	}
	public function edit(){

		
		
		return array('title' => 'Заголовок2', 'code' => 'gecnj');
	}
	
	public function modal(){
		return array('modal' => array('title' => 'Настройка интерфейса', 'content' => 'about <b>content</b>'));
		//return array('success' => 'hooray!');
		//return array('error' => 'damn!');
		//return array('reload' => '1');
	}
}
?>