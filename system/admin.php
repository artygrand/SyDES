<?php
/**
* SyDES :: admin core file
* Contain the basic methods of the engine
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Admin{
	public static $config = array();
	public static $siteConfig = array();
	public static $token = '';
	public static $hook = array();
	public static $menu = array('site_title'=>'', 'page_types'=>'', 'modules'=>'');
	public static $lang = 'en';
	public static $mode = 'html';
	public static $db = '';
	public static $site = DEFAULTSITE;
	public static $locale = 'en';

	/**
	* Sets admin language
	* @return void
	*/
	public function setLang(){
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
		if (DEMO){
			self::$mode = 'demo';
		} elseif (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			self::$mode = 'ajax';
		}
	}
	
	/**
	* Sets the site for work
	* @return void
	*/
	public function setSite(){
		if (!empty($_GET['site']) and array_key_exists($_GET['site'], self::$config['sites'])){
			self::$site = $_GET['site'];
		} elseif (!empty($_COOKIE['site']) and array_key_exists($_COOKIE['site'], self::$config['sites'])){
			self::$site = $_COOKIE['site'];
		} else {
			$dir = dirname($_SERVER['SCRIPT_NAME']);
			$domain = $_SERVER['HTTP_HOST'] . substr($dir, 0, strrpos($dir, '/'));
			if (isset(self::$config['domains'][$domain])){
				self::$site = self::$config['domains'][$domain];
			} else {
				$sites = array_keys(self::$config['sites']);
				self::$site = $sites[0];
			}
		}
		setcookie('site', self::$site, time()+604800);
	}

	/**
	* Sets current site locale
	* @return void
	*/
	public function setLocale(){
		if (!empty($_GET['locale']) and in_array($_GET['locale'], self::$config['sites'][self::$site]['locales'])){
			self::$locale = $_GET['locale'];
			setcookie('locale', self::$locale, time()+604800);
		} elseif (!empty($_COOKIE['locale']) and in_array($_COOKIE['locale'], self::$config['sites'][self::$site]['locales'])){
			self::$locale = $_COOKIE['locale'];
		} else {
			self::$locale = self::$config['sites'][self::$site]['locales'][0];
			setcookie('locale', self::$locale, time()+604800);
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
		file_put_contents('../site/' . date('Ym') . '.log', "$date | $ip | $string" . PHP_EOL, FILE_APPEND | LOCK_EX);
	}

	/**
	* Hooks plugins and execute them
	* @param object $mod
	* @param string $act
	* @param array $data - data from module
	* @param array $funcs - names of functions 
	* @return array
	*/
	public function hook($mod, $act, $data, $funcs){
		if (is_array($funcs)){
			ksort($funcs);
			foreach($funcs as $func){
				if ($func and function_exists($func)){
					$data = $func($mod, $data);
				}
			}
		}
		return $data;
	}

	/**
	* Run the module and plugins
	* @param object $mod
	* @param string $act
	* @return void
	*/
	public function execute($mod, $act){
		$this->response = array();
		if (isset(self::$hook['before'][$mod->name][$act])){
			$this->response = $this->hook($mod, $act, $this->response, self::$hook['before'][$mod->name][$act]);
		}
		$this->response = array_merge($this->response, (array)$mod->$act());
		if (isset(self::$hook['after'][$mod->name][$act])){
			$this->response = $this->hook($mod, $act, $this->response, self::$hook['after'][$mod->name][$act]);
		}
	}

	public function renderPage(){
		if (self::$mode == 'ajax'){
			echo json_encode($this->response);
		} else {
			$dummy = array(
				'title' => lang('admin'),
				'siteName' => self::$config['sites'][self::$site]['name'],
				'pageTypes' => '',
				'modules' => $this->getModuleList(),
				'pages' => $this->getPagesList(),
				'breadcrumbs' => '',
				'js' => '',
				'css' => '',
				'siteSelect' => $this->siteSelect(),
				'contentLeft' => '',
				'contentCenter' => '',
				'contentRight' => '',
				'footerLeft' => '',
				'footerCenter' => '',
				'cssfiles' => array(),
				'jsfiles' => array(),
				'skin' => isset($_COOKIE['skin']) ? $_COOKIE['skin'] : 'black'
			);
			$this->response = array_merge($dummy, $this->response);
			
			if (!empty($this->response['form_url'])){
				$this->response['opn'] = 'form name="form" method="post" enctype="multipart/form-data" action="' . $this->response['form_url'] . '"';
				$this->response['cls'] = 'form';
			} else {
				$this->response['opn'] = 'div';
				$this->response['cls'] = 'div';
			}
			
			$this->response['sm'] = 12;
			$this->response['lg'] = 12;
			if (!empty($this->response['contentLeft'])){
				$this->response['sm'] = $this->response['sm']-3;
				$this->response['lg'] = $this->response['lg']-2;
			}
			if (!empty($this->response['contentRight'])){
				$this->response['sm'] = $this->response['sm']-3;
				$this->response['lg'] = $this->response['lg']-2;
			}

			echo render('template/main.php', $this->response);
		}
		
	}
	
	private function siteSelect(){
		foreach(self::$config['sites'] as $site => $data){
			if (count($data['locales']) > 1){
				foreach($data['locales'] as $locale){
					$sites["site={$site}&locale={$locale}"] = "{$data['name']} : {$locale}";
					if ($site == self::$site and $locale == self::$locale){
						$current = "site={$site}&locale={$locale}";
					}
				}
			} else {
				$sites["site={$site}&locale={$data['locales'][0]}"] = "{$data['name']}";
				if ($site == self::$site){
					$current = "site={$site}&locale={$data['locales'][0]}";
				}
			}
		}
		if (count($sites) > 1){
			return getSelect($sites, $current, 'class="siteselect" data-toggle="tooltip" data-placement="left" title="Выберите сайт для редактирования"');
		} else {
			return false;
		}
	}

	private function getModuleList(){
		$list = array();
		foreach(self::$config['sites'][self::$site]['modules'] as $module){
			$name = self::$config['modules'][$module]['name'];
			$list["?mod={$module}"] = isset($name[self::$lang]) ? $name[self::$lang] : $name['en'];
			if (self::$config['modules'][$module]['quick']){
				$list["?mod={$module}"] .= '</a><a href="?mod=' . $module . '&act=edit" data-toggle="tooltip" data-placement="right" title="' . lang('add_more'). '">[+1]';
			}
		}
		return getList($list, false, 'class="list-unstyled"');
	}

	private function getPagesList(){
		$list = array();
		foreach(self::$siteConfig['page_types'] as $type => $data){
			$list["?mod=pages&type={$type}"] = $data['title'];
		}
		return getList($list, false, 'class="list-unstyled"');
	}

	public static function createSite($site){
		if (file_exists(SITE_DIR . $site)){
			redirect('?mod=config&act=site_manager_view', lang('site_already_created'));
		}
		if ($site == DEFAULTSITE){
			$dom = dirname($_SERVER['PHP_SELF']);
			$dom = $_SERVER['SERVER_NAME'] . substr($dom, 0, strrpos($dom, '/'));
			self::$config['domains'][$dom] = $site;
			self::$config['sites'][$site] = array('modules' => array(), 'locales' => array(self::$lang), 'name' => $_POST['sitename']);
			self::$config['modules'] = array();
		} else {
			$data = $_POST['sites']['new'];
			$domains = explode("\n", $data['domains']);
			foreach($domains as $dom){
				$dom = trim($dom);
				if(!empty($dom)){
					self::$config['domains'][$dom] = $site;
				}
			}
			self::$config['sites'][$site] = array(
				'locales' => explode(' ', trim($data['locales'])),
				'name' => $data['site_name']
			);
			self::$config['sites'][$site]['modules'] = isset($data['modules']) ? $data['modules'] : array();
		}
		file_put_contents(SITE_DIR . 'baseconfig.db', serialize(self::$config));
		mkdir(SITE_DIR . $site, 0777);
		
		$config = array(
			'maintenance_mode' => false,
			'need_cache' => false,
			'template' => 'default',
			'say' => self::$htmlSay,
			'page_types' => array(
				'page' => array(
					'title' => lang('pages'),
					'layout' => 'page',
					'structure' => 'tree',
					'root' => '0',
					'meta' => array()
				)
			)
		);
		file_put_contents(SITE_DIR . $site . '/config.db', serialize($config));
		chmod(SITE_DIR . $site . '/config.db', 0777);
		
		redirect('?mod=config&act=sitemanager_view');
	}

	public static function saveConf(){
		file_put_contents(SITE_DIR . 'baseconfig.db', serialize(self::$config));
	}

	public static function checkSysReq(){
		$paths = array('../cache', '../site', '../system/iblock', '../template/default', '../upload/images');
		$wr = '';
		foreach ($paths as $path){
			if(!is_writable($path)){
				$wr .= '<li>' . $path . '</li>';
			}
		}
		if ($wr){
			$wr .= '<li>' . lang('folders_in_upload') . '</li>';
		}
		
		$req_pdo = class_exists('PDO', false);
		$pdo_drv = $req_pdo ? PDO::getAvailableDrivers(): array();
		$req_sqlite = in_array('sqlite', $pdo_drv);
		$req_json = function_exists('json_encode');
		$req_rewrite = in_array('mod_rewrite', apache_get_modules());
		
		$errors = '';
		$errors .= version_compare(PHP_VERSION, '5.3.0') < 0 ? '<li>' . $l[$lang]['php_too_old'] . '</li>' : '';
		$errors .= !$req_pdo ? '<li>' . lang('pdo_not_supported') . '</li>' : '';
		$errors .= !$req_sqlite ? '<li>' . lang('pdo_sqlite_not_supported') . '</li>' : '';
		$errors .= !$req_json ? '<li>' . lang('json_not_supported') . '</li>' : '';
		$errors .= !$req_rewrite ? '<li>' . lang('mod_rewrite_not_supported') . '</li>' : '';

		return array($errors, $wr);
	}
	
	public static function getSaveButton($file, $button = ''){
		if (is_writable($file) and is_writable(dirname($file))){
			return $button ? $button : '<div class="form-group"><button type="submit" class="btn btn-primary btn-block">' . lang('save') . '</button></div>';
		} else {
			return '<div class="form-group"><button type="button" class="btn btn-primary btn-block disabled">' . lang('not_writeable') . '</button></div>';
		}
	}
	
	private static $htmlSay = '<!DOCTYPE html>
<html>
 <head>
  <style>
  html, body{height:100%;}
   body {background:#fff;margin:0;padding:0;color:#555;font:normal 30px/30px Arial;}
   #text {position:absolute;top:50%;left:50%;margin:-51px 0 0 -173px;text-align:center;}
   .block1{width:390px;height:100%;margin:0 auto;border-left:1px dashed #555;border-right:1px dashed #555;}
   .block2{width:100%;height:140px;position:absolute;top:50%;margin-top:-75px;border-top:1px dashed #555;border-bottom:1px dashed #555;}
  </style>
 </head>
 <body>
  <div id="text">Нам очень жаль, но<br>сайт временно отключен<br>Приходите позднее</div>
  <div class="block1">&nbsp;</div><div class="block2">&nbsp;</div>
 </body>
</html>';
}
?>