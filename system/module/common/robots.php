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
Host: {$this->base}
Sitemap: http://{$this->base}/sitemap.xml";

	$this->response->body = $text;
	}
}