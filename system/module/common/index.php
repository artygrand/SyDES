<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class CommonController extends Controller{
	public $name = 'common';

	public function index(){
		
	}

	public function clearcache(){
		$this->cache->clear();
		header('Location: ' . $this->request->get['return']);
		die;
	}

	public function getiblocks(){
		$config = array();
		foreach (glob(DIR_IBLOCK . '*/manifest.ini') as $iblock){
			$ini = parse_ini_file($iblock, true);
			$locale = isset($ini[$this->locale]['title']) ? $this->locale : 'en';
			$config[] = array(
				str_replace(array(DIR_IBLOCK, '/manifest.ini'), '', $iblock),
				$ini[$locale]['title'],
				$ini[$locale]['description'],
			);
		}
		$this->response->body = $config;
	}
}