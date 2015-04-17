<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

abstract class HasRegistry{
	protected $registry;

	public function __construct(){
		$this->registry = Registry::getInstance();
	}

	public function __get($key){
		return $this->registry->$key;
	}

	public function __set($key, $value){
		$this->registry->$key = $value;
	}

	public function __isset($name){
		return isset($this->registry->$name);
	}
}