<?php
class Import{
	public $name = 'import';

	/**
	* Sets the allowed actions for user ✓
	* @var array
	*/
	public static $allowed4html = array('view', 'put', 'get');

	/**
	* Sets the allowed actions for user over AJAX
	* @var array
	*/
	public static $allowed4ajax = array();
	
	/**
	* Sets the allowed actions for demo user
	* @var array
	*/
	public static $allowed4demo = array('view');

	public function view(){
		return array('title' => 'text', 'contentCenter' => 'content');
	}

}
?>