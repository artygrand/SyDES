<?php
/**
 * This is like wordPress functions.php
 * Just creare function and hook it
 * These functions will be executed before or after module
 *
 * @package SyDES
 *
 * @copyright 2011-2014, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
 
/*
Example:
Admin::$hook['before or after']['classname_in_lover_case']['action'][order] = 'my_func';
function my_func($mod, $data){
	do something
	return $data;
}
	$mod is module object
	$data is array returned by module
*/


?>