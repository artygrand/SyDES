<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class Response{
	public $data = array();
	public $notify;
	public $alerts = array();
	public $style = array();
	public $script = array();
	public $context = array();
	public $headers = array();
	public $js = array('l10n' => array(), 'settings' => array());
	public $body;
	public $status = 200;
	public $mime = 'html';
	public $gzip = false;
	public $nocache = false;
	public $etag = false;

	public function __construct(){
		if (isset($_SESSION['alerts'])){
			$this->alerts = $_SESSION['alerts'];
			unset($_SESSION['alerts']);
		}
	}

	public function notify($message, $status = 'success'){
		$this->notify = array(
			'message' => $message,
			'status' => $status
		);
	}

	public function alert($message, $status = 'success'){
		$this->alerts[] = array(
			'message' => $message,
			'status' => $status
		);
	}

	public function addHeader($header){
		$this->headers[] = $header;
	}

	public function addJsL10n($array){
		$this->js['l10n'] = array_merge($this->js['l10n'], $array);
	}

	public function addJsSettings($array){
		$this->js['settings'] = array_merge($this->js['settings'], $array);
	}

	public function redirect($url = ''){
		if (!empty($this->alerts)){
			$_SESSION['alerts'] = $this->alerts;
			unset($this->alerts);
		}

		if (IS_AJAX){
			$this->body['redirect'] = $url;
		} else {
			$host = $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
			$this->addHeader("Location: http://{$host}{$url}");
		}
		$this->flush();
		die;
	}

	public function reload(){
		$this->body['reload'] = 1;
	}

	public function flush(){
		if (!headers_sent()){
			if (!empty($this->notify)){
				if (IS_AJAX){
					$this->body['notify'] = $this->notify;
				} else {
					setcookie('notify.message', $this->notify['message'], time()+3);
					setcookie('notify.status', $this->notify['status'], time()+3);
				}
			}

			if (is_array($this->body)){
				$this->mime = 'json';
			}

			if ($this->nocache){
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Sat, 26 Jul 1997 12:00:00 GMT");
				header('Pragma: no-cache');
			}
			if ($this->etag){
				header('ETag: "' . md5($this->body) . '"');
			}
			header('HTTP/1.0 '.$this->status . ' ' . App::$STATUS_CODE[$this->status]);
			header('Content-type: ' . App::$MIME_TYPE[$this->mime]);

			foreach ($this->headers as $h){
				header($h, true);
			}

			echo is_array($this->body) ? json_encode($this->body) : $this->body;
		}
	}
}