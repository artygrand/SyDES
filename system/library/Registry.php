<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Registry{
	protected $registry;
	private static $instance;

	private function __construct(){}
	private function __clone(){}
	private function __wakeup(){}

	public static function getInstance(){
		if (empty(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function reuse(Closure $ctor){
		return function($self) use ($ctor){
			static $instance = null;
			return null === $instance ? $instance = $ctor($self) : $instance;
		};
	}

	public function wrapClosure(Closure $value){
		return function() use ($value){
			return $value;
		};
	}

	public function __set($name, $value){
		$this->registry[$name] = $value;
	}

	public function __get($name){
		if (!array_key_exists($name, $this->registry)){
			return false;
		}
		return $this->registry[$name] instanceof Closure ? $this->registry[$name]($this) : $this->registry[$name];
	}

	public function __call($name, array $arguments){
		if (!array_key_exists($name, $this->registry)){
			return false;
		}
		if (!$this->registry[$name] instanceof Closure){
			return false;
		}
		array_unshift($arguments, $this);
		return call_user_func_array($this->registry[$name], $arguments);
	}
	
	public function __isset($name){
		return array_key_exists($name, $this->registry);
	}
}