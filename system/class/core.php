<?php
/**
* The main class of the administrative center
* Contain the basic methods of the engine
* @version 1.0
. @since 1.7
* @copyright 2011-2012, ArtyGrand (artygrand.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Core{
	/**
	* Dummy properties
	*/
	public static $db = '';
	public static $config = '';
	public static $hook = array();
	public static $locale = '';
	public $menu = array('site_title'=>'', 'page_types'=>'', 'modules'=>'');
	/**
	* Sets language for admin center
	* @var string
	*/
	public static $language = 'en';

	/**
	* Prepare database
	* @param string $dataBaseType
	* @param string $host
	* @param array $auth
	* @return void
	*/
	public function __construct(){
		// register the autoload function
		spl_autoload_register(array($this, 'loadModule'));

		// select the database
		if (DB_DRIVER == 'sqlite'){
			self::$db = new PDO('sqlite:' . DB_NAME);
		} else {
			$port = defined(MYSQL_PORT) ? ';port=' . MYSQL_PORT : '';
			self::$db = new PDO('mysql:host=' . MYSQL_SERVER . ';dbname=' . DB_NAME . $port, MYSQL_USER, MYSQL_PASSWORD);
		}
		
		// get site config
		self::$config = unserialize(file_get_contents(SYS_DIR . 'config.db'));
		$this -> getUserLanguage();
		
		// get menu array from cache or create him
		if (is_file(SYS_DIR . 'menu.db')){
			$this -> menu = unserialize(file_get_contents(SYS_DIR . 'menu.db'));
		} else {
			$this -> checkPageTypes();
			$this -> checkModules(true);
			$this -> getSiteTitle();
			if(!file_put_contents(SYS_DIR . 'menu.db', serialize($this -> menu))) die('Can\'t create file: ' . SYS_DIR . 'menu.db');
		}
		
		// set current edited locale
		if (isset($_GET['locale']) and in_array($_GET['locale'], Core::$config['locale'])){
			setcookie('locale', $_GET['locale'], time()+3600*24*2);
			self::$locale = $_GET['locale'];
		} else{
			self::$locale = (isset($_COOKIE['locale']) and in_array($_COOKIE['locale'], Core::$config['locale'])) ? $_COOKIE['locale'] : self::$config['locale'][0];
		}
	}
	
	/**
	* Autoload function for modules
	* @param string $module
	* @return void
	*/
	public function loadmodule($module) {
		if(strpos($module, '/')=== false and is_file(MOD_DIR . $module . '.php')){
			include_once MOD_DIR . $module . '.php';
		} else {
			$this -> redirectTo('', lang('unauthorized_request'));
		}
	}
	
	/**
	* Redirections with messages and status
	* @param string $link
	* @param string $m
	* @param string $s ('error' or 'success')
	* @return header
	*/
	public function redirectTo($link, $m = '', $s = 'error'){
		$host  = $_SERVER['HTTP_HOST'];
		$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
		setcookie('message', $m, time()+5);
		setcookie('messageStatus', $s, time()+5);
		header("Location: http://$host$uri/$link");
		exit;
	}
	
	/**
	* Determine user's language. Native or selected.
	* @return void
	*/
	public function getUserLanguage(){
		if(!empty($_COOKIE['admin_language'])){
			self::$language = substr($_COOKIE['admin_language'], 0, 2);
		} elseif(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			self::$language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		}
		foreach (glob(LANG_DIR . '*/admin.php') as $l) {
			$t = str_replace(array(LANG_DIR, '/admin.php'), '', $l);
			$this -> existLangs[$t] = array('title' => $t);
		}
		if (!is_file(LANG_DIR . self::$language . '/admin.php')){
			$rand = array_rand ($this -> existLangs);
			self::$language = $this -> existLangs[$rand]['title'];
		}
	}
	
	/**
	* Determine selected skin.
	* @return string
	*/
	public function getSkin(){
		return isset($_COOKIE['skin']) ? $_COOKIE['skin'] : 'black';
	}
	
	/**
	* Create language list if quantity of languages is more than one
	* @return void
	*/
	public function getLanguageSelect(){
		if (count($this -> existLangs) == 1) return;
		return getSelect($this -> existLangs, 'title', self::$language, 'id="language"');
	}
	
	/**
	* Return skin selector
	* @return string
	*/
	public function getSkinSelect(){
		$skins = glob(TPL_DIR . 'skins/*.css');
		if (count($skins) == 1) return;
		$ss = '<div id="skin_selector">';
		foreach($skins as $skin){
			
			$ss .= '<a href="#" class="skin_item" title="' . $skin . '" style="background:' . $skin . ';">' . $skin . '</a>';
		}
		$del = array(TPL_DIR . 'skins/', '.css');
		$ss = str_replace($del, '', $ss);
		return	$ss .'</div>';
	}
	
	/**
	* Prepares menu and render the page
	* @return void
	*/
	public function GetHTML($template){
		require TPL_DIR . $template;
	}

	/**
	* Prepares a list of existing page types for menu
	* @return void
	*/
	public function checkPageTypes(){
		$stmt = self::$db -> query("SELECT type, name FROM page_types ORDER BY pos");
		$stmt -> setFetchMode(PDO::FETCH_ASSOC);
		while($type = $stmt -> fetch()){
			$this -> menu['page_types'] .= "\t\t\t\t\t" . '<li><a href="?mod=pages&type=' . $type['type'] . '">' . $type['name'] . '</a><a href="?mod=pages&type=' . $type['type'] . '&act=edit" title="' . lang('add_more') . '">[+1]</a></li>' . PHP_EOL;
		}
	}
	
	/**
	* Print variable if exist
	* @param string $index
	* return void
	*/
	public function render($index){
		if (isset($this -> content[$index])) echo $this -> content[$index];
	}
	
	/**
	* Exit from account
	* return void
	*/
	public function quit(){
		session_destroy();
		setcookie('member', '');
		setcookie('pass', '');
		$this -> redirectTo('');
	}
	
	/**
	* Check authorisation and create session if cookies is defined
	* @return bool
	*/
	public function IsAuthorized(){
		if (!isset($_SESSION['member']) or !isset($_SESSION['pass']) or $_SESSION['member'] !== self::$config['login'] or $_SESSION['pass'] !== md5(self::$config['pass'] . getip())){
			//remember me?
			if (self::$config['cookie'] == 1 and isset($_COOKIE['member']) and isset($_COOKIE['pass'])){
				if ($_COOKIE['member'] !== self::$config['login'] or $_COOKIE['pass'] !== md5(self::$config['pass'] . getip())){
					$this -> logAccess($_COOKIE['member'], $_COOKIE['pass'], 'Wrong Cookies');
					return false;
				} else {
					$_SESSION['member'] = $_COOKIE['member']; 
					$_SESSION['pass'] = $_COOKIE['pass'];
					$this -> logAccess($_COOKIE['member'], '****', 'Used Cookies');
					return true;
				}
			} else {
				return false;
			}
		} else {
			return true;
		}
	}
	
	/**
	* Check authorisation for ajax
	* @return bool
	*/
	public function IsAjaxAuthorized(){
		if (!isset($_SESSION['member']) or !isset($_SESSION['pass']) or $_SESSION['member'] !== self::$config['login'] or $_SESSION['pass'] !== md5(self::$config['pass'] . getip())){
			return false;
		} else {
			return true;
		}
	}
	
	/**
	* Add info to access log
	* @param string $login
	* @param string $pass
	* @param string $text
	* return void
	*/
	public function logAccess($login, $pass, $text){
		$login = htmlspecialchars($login);
		$pass = htmlspecialchars($pass);
		if (self::$language == 'ru'){
			$date = rus_date('r');
		} else {
			$date = date('r');
		}  
		$stmt = self::$db -> prepare("INSERT INTO access(date, ip, login, password, text) VALUES (:date, :ip, :login, :password, :text)");
		$stmt -> execute(array('date' => $date, 'ip' => getip(), 'login' => $login, 'password' => $pass, 'text' => $text));
	}

	/**
	* Check for unregistered modules and install it
	* And create cache of list for menu
	* @return void
	*/
	public function checkModules($force = false){
		// get Module List
		$existMods = globRecursive(MOD_DIR, array("php"));
		unset($existMods['pages.php'], $existMods['config.php'], $existMods['template.php'], $existMods['iblock.php'], $existMods['access.php']);
		
		// get Registered Module List
		$stmt = self::$db -> query("SELECT key FROM config_meta WHERE page_id = 2");
		$mod = $stmt -> fetchAll(PDO::FETCH_ASSOC);
		$registeredMods = array();
		if ($mod){
			foreach($mod as $m){
				$registeredMods[] = $m['key'];
			}
		}
		
		foreach($existMods as $mod){
			if (!in_array($mod['title'], $registeredMods)){
				$modul = new $mod['title'];
				$modul -> install();
				$is[] = 1;
			}
		}
		// maybe module was deleted
		if(!isset($is) and count($existMods) != count($registeredMods)){
			$mods = str_replace('.php', '', array_keys($existMods));
			$result = array_values(array_diff($registeredMods, $mods));
			$count = str_pad('?', (count($result)*2)-1, ',?');
			$stmt = self::$db -> prepare("DELETE FROM config_meta WHERE page_id = 2 AND key IN({$count})");
			$stmt -> execute($result);
			$is[] = 1;
		}
		
		// update menu cache if exists new modules
		if (isset($is) or $force){
			foreach($this -> existLangs as $lang){;
				$this -> menu['modules'][$lang['title']] = '';
				foreach($existMods as $mod){
					$add = $mod['title']::$quickAdd ? '<a href="?mod=' . $mod['title'] . '&act=edit"  title="' . lang('add_more') . '">[+1]</a>' : '';
					$this -> menu['modules'][$lang['title']] .= '<li><a href="?mod=' . $mod['title'] . '">' . $mod['title']::$nativeName[$lang['title']] . '</a>' . $add . "</li>\n";
				}
			}
			if (!$force) file_put_contents(SYS_DIR . 'menu.db', serialize($this -> menu));
		}
	}
	public function getMenuSiteTitle(){
		return $this -> menu['site_title'];
	}
	public function getMenuPagesList(){
		return $this -> menu['page_types'];
	}
	public function getMenuModuleList(){
		return $this -> menu['modules'][self::$language];
	}
	public function type(){
		global $module;
		if (isset($module -> type)) return $module -> type;
	}
	public function getSiteTitle(){
		$stmt = self::$db -> query("SELECT value FROM config_meta WHERE page_id = 1 AND key = 'site_title'");
		$t = $stmt -> fetchColumn();
		$this -> menu['site_title'] = $t ? $t : lang('site_title_empty');
	}
	
	public static function clearMenuCache(){
		unlink(SYS_DIR . 'menu.db');
	}
}
?>