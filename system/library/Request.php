<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Request{
	public $get;
	public $post;
	public $cookie;
	public $files;
	public $server;

	public function __construct(){
		$this->get = $this->clean($_GET);
		$this->post = $this->clean($_POST);
		$this->cookie = $_COOKIE;
		$this->files = $_FILES;
		$this->server = $_SERVER;

		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			define('IS_POST', true);
		} else {
			define('IS_POST', false);
		}

		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			define('IS_AJAX', true);
		} else {
			define('IS_AJAX', false);
		}
	}

	public function clean($data){
		if (is_array($data)){
			foreach ($data as $key => $value){
				unset($data[$key]);
				$data[$this->clean($key)] = $this->clean($value);
			}
		} else {
			$data = htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
		}
		return $data;
	}
}