<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Cache{ 
	private $expire = 2678400; //month

	public function get($key){
		$files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^\w\.-]/', '', $key) . '.*');
		if ($files){
			$data = unserialize(file_get_contents($files[0]));
			foreach ($files as $file){
				$time = substr(strrchr($file, '.'), 1);
      			if ($time < time()){
					if (file_exists($file)){
						unlink($file);
					}
      			}
    		}
			return $data;			
		}
	}

  	public function set($key, $value, $expire = false){
		if (!$expire){
			$expire = $this->expire;
		}
    	$this->clear($key);
		$file = DIR_CACHE . 'cache.' . preg_replace('/[^\w\.-]/', '', $key) . '.' . (time() + $expire);
		file_put_contents($file, serialize($value), LOCK_EX);
  	}

	public function clear($key = false){
		if ($key){
			$files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^\w\.-]/', '', $key) . '.*');
		} else {
			$registry = Registry::getInstance();
			$pages = glob(DIR_CACHE . $registry->site . '/*');
			$files = glob(DIR_CACHE . 'cache.*');
			$files = array_merge($files, $pages);
		}

		if ($files){
    		foreach ($files as $file){
      			if (file_exists($file)){
					unlink($file);
				}
    		}
		}
  	}
}