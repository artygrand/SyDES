<?php
/**
* Module. Example of simple module
* @version: 1.0.0
* @author ArtyGrand
*/
 
class Example extends Module{
	/**
	* Sets the native module name for menu
	* @var array
	*/
	public static $nativeName = array(
		'ru' => 'Пример модуля',
		'en' => 'Module example'
	);
	
	/**
	* Show "add more" button in menu?
	* @var bool
	*/
	public static $quickAdd = true;
	
	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowedActions = array('view', 'edit', 'save', 'delete');
	
	/**
	* Sets module language package
	* @var array
	*/
	public $ml = array(
		'ru' => array(
			'breadcumbs' => 'Пример хлебных крошек'
		),
		'en' => array(
			'breadcumbs' => 'Breadcrumbs example'
		)
	);

	public $tableStructure = array(
		'name' => array('type' => 'TEXT', 'title' => 'Ф.И.О', 'visible' => true, 'tag' => 'text', 'props' => 'class="full middle"'),
		'email' => array('type' => 'TEXT', 'title' => 'E-mail', 'visible' => true, 'tag' => 'text', 'props' => 'class="full middle"'),
		'city' => array('type' => 'TEXT', 'title' => 'Город', 'visible' => true, 'tag' => 'select', 'values' => array('Новосибирск', 'Екатеринбург', 'Питер', 'Москва'), 'props' => 'class="span4"'),
		'comment' => array('type' => 'TEXT', 'title' => 'Комментарий', 'visible' => true, 'tag' => 'textarea', 'props' => 'class="full image"')
	);

	function __construct(){
		$this -> setModuleName();
		parent::__construct();
	}
}
?>