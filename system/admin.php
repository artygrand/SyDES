<?php
/**
* SyDES :: admin core file
* Contain the basic methods of the engine
* @version 1.8
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Admin{
	/**
	* Dummy properties
	*/
	public static $config = array();
	public static $token = '';
	public static $hook = array();
	public static $menu = array('site_title'=>'', 'page_types'=>'', 'modules'=>'');
	public static $lang = 'en';
	public static $mode = 'html';
	public $site = 'default';
	public $locale = 'en';

	/**
	* Redirections with messages and status
	* @param string $link
	* @param string $m
	* @param string $s ('error' or 'success')
	* @return void
	*/
	public static function redirectTo($link, $m = '', $s = 'error'){
		$host = $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/';
		setcookie('messText', $m, time()+5);
		setcookie('messStatus', $s, time()+5);
		header("Location: http://$host$link");
		exit;
	}
	
	/**
	* Sets admin language
	* @return void
	*/
	public function selectLang(){
		$needUpd = true;
		if (!empty($_GET['lang'])){
			self::$lang = $_GET['lang'];
		} elseif (!empty($_COOKIE['lang'])){
			self::$lang = $_COOKIE['lang'];
			$needUpd = false;
		} elseif ($_SERVER['HTTP_ACCEPT_LANGUAGE']){
			self::$lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		}

		foreach (glob('language/*.php') as $l){
			$l = str_replace(array('language/', '.php'), '', $l);
			$langs[] = $l;
		}
		if (!in_array(self::$lang, $langs)){
			self::$lang = $langs[0];
			$needUpd = true;
		}

		if ($needUpd){
			setcookie('lang', self::$lang, time()+604800);
		}
	}

	/**
	* Sets working mode, for AJAX response or fullHTML
	* @return void
	*/
	public function setMode(){
		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			self::$mode = 'ajax';
		}
	}
	
	/**
	* Sets the site for work
	* @return void
	*/
	public function selectSite(){
		if (!empty($_GET['site']) and array_key_exists($_GET['site'], array_flip(self::$config['sites']))){
			$this->site = $_GET['site'];
			return;
		}
		$dir = dirname($_SERVER['SCRIPT_NAME']);
		$site = $_SERVER['HTTP_HOST'] . substr($dir, 0, strrpos($dir, '/'));
		if (isset(self::$config['sites'][$site])){
			$this->site = self::$config['sites'][$site];
		}
	}

	/**
	* Sets current site locale
	* @return void
	*/
	public function selectLocale(){
		if (!empty($_GET['locale']) and in_array($_GET['locale'], $this->siteConfig['locale'])){
			$this->locale = $_GET['locale'];
			setcookie('locale', $this->locale, time()+604800);
		} else{
			$this->locale = (!empty($_COOKIE['locale']) and in_array($_COOKIE['locale'], $this->siteConfig['locale'])) ? $_COOKIE['locale'] : $this->siteConfig['locale'][0];
		}
	}
	
	/**
	* Just logging all actions
	* @param string $string
	* @return void
	*/
	public static function log($string){
		$string = htmlspecialchars($string);
		$date = self::$lang == 'ru' ? rus_date('r') : date('r');
		$ip = getip();
		file_put_contents('../site/log.txt', "$date | $ip | $string" . PHP_EOL, FILE_APPEND | LOCK_EX);
	//	$stmt = self::$db -> prepare("INSERT INTO log(date, ip, string) VALUES (:date, :ip, :string)");
	//	$stmt -> execute(array('date' => $date, 'ip' => $ip, 'string' => $string));
	}
	
	public function hook($mod, $act, $data){
		if (isset(self::$hook[$mod->name][$act])){
			$funcs = self::$hook[$mod->name][$act];
			if (is_array($funcs)){
				ksort($funcs);
				foreach($funcs as $func){
					if ($func and function_exists($func)){
						$data = $func($mod, $data);
					}
				}
			}
		}
		return $data;
	}
	
	public function renderPage(){
		if (self::$mode == 'html'){
			echo render('template/main.php', $this->response);
		} else {
			echo json_encode($this->response);
		}
		
	}
}
?>