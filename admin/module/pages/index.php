<?php
class Pages{
	public $name = 'pages';
	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowed4html = array('view', 'edit', 'save', 'delete', 'toggle', 'remove', 'recover', 'clearcache');

	/**
	* Sets the allowed actions for user over AJAX
	* @var array
	*/
	public static $allowed4ajax = array('loadchilds', 'setnewalias', 'setnewparent', 'toggle', 'remove', 'recover', 'metaadd', 'metaupdate', 'metadelete', 'clearcache');



	public function view(){
		$out = render('module/pages/tpl/main.php', array('text' => 'a это текст модуля'));
		return array('title' => 'Заголовок', 'code' => $out);
	}
	public function edit(){

		
		
		return array('main' => 'это другая страница', 'code' => '');
	}
}
?>