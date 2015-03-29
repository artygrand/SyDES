<?php
/**
* Module. Example of simple module
* @version: 1.0.0
* @author ArtyGrand
*/
 
class Simple extends Module{
	public $name = 'simple';

	public $structure = array(
		'name' => array('type' => 'TEXT', 'title' => 'Ф.И.О', 'visible' => true, 'tag' => 'text', 'props' => 'class="form-control"'),
		'image' => array('type' => 'TEXT', 'title' => 'Картинка', 'visible' => true, 'tag' => 'text', 'props' => 'class="image form-control"'),
		'city' => array('type' => 'TEXT', 'title' => 'Город', 'visible' => true, 'tag' => 'select', 'values' => array('Новосибирск', 'Екатеринбург', 'Питер', 'Москва'), 'props' => 'class="form-control" multiple'),
		'comment' => array('type' => 'TEXT', 'title' => 'Комментарий', 'visible' => false, 'tag' => 'textarea', 'props' => 'class="form-control" rows="3"'),
		'active' => array('type' => 'TEXT', 'title' => 'Активно?', 'visible' => true, 'tag' => 'checkbox', 'props' => '')
	);
}
?>