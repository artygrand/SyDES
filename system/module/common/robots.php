<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class RobotsController extends Controller{
	public $name = 'robots';
	public static $front = array('index');

	public function index(){
		$this->response->mime = 'txt';
		$text = "User-agent: *
Disallow: /index.php*
Disallow: /?*
Disallow: /*?utm_source=
Disallow: /search
Host: {$this->base}";

	if (count($this->config_site['locales']) > 1){
		foreach($this->config_site['locales'] as $locale){
			$text .= "\nSitemap: http://{$this->base}/{$locale}/sitemap.xml";
		}
	} else {
		$text .= "\nSitemap: http://{$this->base}/sitemap.xml";
	}

	$this->response->body = $text;
	}
}