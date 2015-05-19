<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Loader extends HasRegistry{
	public function model($module, $global = true){
		$part = strpos($module, '/') !== false ? explode('/', $module) : array($module, $module);
		$file = DIR_MODULE . $part[0] . '/model/' . $part[1] . '.php';

		if (!file_exists($file)){
			throw new BaseException(sprintf(t('error_file_not_found'), $file));
		}

		include_once $file;
		$class = ucfirst($part[1]) . 'Model';
		if ($global){
			$model = $part[1] . '_model';
			$this->$model = new $class();
		} else {
			return new $class();
		}
		
	}

	public function view($template, $data = array()){
		$part = explode('/', $template);
		if (count($part) != 2){
			$this->response->errors[] = t('error_loadview_argument');
			return;
		}

		$file_override = DIR_TEMPLATE . $this->config_site['template'] . '/module/' .  $template . '.php';
		$file = DIR_MODULE . $part[0] . '/view/' . $part[1] . '.php';
		if (file_exists($file_override)){
			return render($file_override, $data);
		} elseif (file_exists($file)){
			return render($file, $data);
		} else {
			throw new BaseException(sprintf(t('error_file_not_found'), $file));
		}
	}

	public function language($filename, $global = true, $language = false){
		if (!$language){
			$language = $this->language;
		}
		$file = DIR_LANGUAGE . $language . '/' . $filename . '.php';
		if (!is_file($file)){
			$file = DIR_LANGUAGE . 'en/' . $filename . '.php';
			if (!is_file($file)){
				return;
			}
		}
		$array = include $file;
		if ($global){
			t('add', $array);
		} else {
			return $array;
		}
		
	}
}