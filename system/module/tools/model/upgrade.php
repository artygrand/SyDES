<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class UpgradeModel extends Model{
	public function getContent($url){
		if(ini_get('allow_url_fopen')){
			$data = file_get_contents($url);
		} else {
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_URL, $url);

			$data = curl_exec($ch);
			curl_close($ch);
		}

		return $data;
	}
}