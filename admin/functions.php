<?php
/**
* This is like wordPress functions.php
* Just creare function and hook it
* These functions will be executed after module
*
* Example:
* Core::$hook['classname_in_lover_case']['action'][order] = 'my_func';
* function my_func($mod, $data){
* 	do something
* 	$mod is module object
* 	$data is array returned by module
* 	return $data;
* }
*/
Core::$hook['pages']['save'][] = 'addNewsDate';
function addNewsDate($mod, $data){
	if($mod -> type == 'news' and !$_GET['id']){
		$mod -> meta -> add(Core::$db -> lastInsertId(), 'date', date('d.m.Y'));
	}
	return $data;
}

Core::$hook['pages']['edit'][] = 'addMetaTileInput';
function addMetaTileInput($mod, $data){
	if($mod -> type == 'page' and !isset($_GET['id'])){
	$data['content'] = str_replace('<!--add-->', '
			<div class="title">Мета тег title</div>
			<div><input type="text" value="" name="metatitle" class="full big date"></div>', $data['content']);
	}
	return $data;
}

Core::$hook['pages']['save'][] = 'addMetaTile';
function addMetaTile($mod, $data){
	if($mod -> type == 'page' and !$_GET['id'] and $_POST['metatitle']){
		$mod -> meta -> add(Core::$db -> lastInsertId(), 'title', $_POST['metatitle']);
	}
	return $data;
}


?>