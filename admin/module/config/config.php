<?php
/**
* SyDES :: box module for configure and manage sites
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
 
class Config extends Module{
	public $name = 'config';
	public $r = array();
	/**
	* Sets the allowed actions for user
	* @var array
	*/
	public static $allowed4html = array('view', 'save', 'sitemanager_view', 'profile_view', 'modules_view', 'backups_view', 'pages_view', 'site_update', 'site_delete', 'profile_update', 'module_install', 'module_uninstall');

	/**
	* Sets the allowed actions for user over AJAX
	* @var array
	*/
	public static $allowed4ajax = array('modal_interface', 'metaadd', 'metaupdate', 'metadelete', 'clearcache', 'modal_module_edit', 'module_save');

	/**
	* Sets the allowed actions for demo user
	* @var array
	*/
	public static $allowed4demo = array('view');
	
	function __construct(){
		$this->r['contentLeft'] = render('module/config/tpl/nav.php');
	}
	
	public function view(){
		foreach(glob('../template/*') as $template){
			$template = str_replace('../template/', '', $template);
			$templates[$template] = $template;
		}
		$main = render('module/config/tpl/main.php', array(
			'templates' => getSelect($templates, Admin::$siteConfig['template'], 'name="template" class="form-control"'),
			'maintenance_check' => Admin::$siteConfig['maintenance_mode'] ? ' checked' : '',
			'cache_check' => Admin::$siteConfig['need_cache'] ? ' checked' : '',
			'say' => Admin::$siteConfig['say']
		));
		$crumbs[] = array('title' => lang('configuration'));
		$this->r['title'] = lang('site_settings');
		$this->r['contentCenter'] = $main;
		$this->r['contentRight'] = '<button type="submit" class="btn btn-primary btn-block">' . lang('save') . '</button>';
		$this->r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$this->r['form_url'] = '?mod=config&act=save';
		return $this->r;
	}
	
	public function profile_view(){
		$crumbs[] = array('url' => '?mod=config','title' => lang('configuration'));
		$crumbs[] = array('title' => lang('profile'));
		$this->r['title'] = lang('profile');
		$this->r['contentCenter'] = render('module/config/tpl/profile.php', array(
			'autologin_check' => Admin::$config['admin']['autologin'] ? 'checked' : '',
			'admin_ip_list' => implode(' ', Admin::$config['admin']['admin_ip'])
		));
		$this->r['contentRight'] = '<button type="submit" class="btn btn-primary btn-block">' . lang('save') . '</button>';
		$this->r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$this->r['form_url'] = '?mod=config&act=profile_update';
		return $this->r;
	}
	
	public function sitemanager_view(){
		if (isset(Admin::$config['sites'])){
			foreach(Admin::$config['sites'] as $site => $confs){
				$sites[$site]['name'] = Admin::$config['sites'][$site]['name'];
				$sites[$site]['locales'] = implode(' ', Admin::$config['sites'][$site]['locales']);
				$sites[$site]['modules'] = $this->getModuleSelector($site);
				$ds = array();
				foreach(Admin::$config['domains'] as $d => $s){
					if($s == $site) $ds[] = $d;
				}
				$sites[$site]['domains'] = implode("\n", $ds);
			}
		}
		$sites['new'] = array(
			'name' => '<span class="glyphicon glyphicon-plus"></span> ' . lang('create_new_site'),
			'locales' => '',
			'modules' => $this->getModuleSelector('new'),
			'domains' => ''
		);

		$main = render('module/config/tpl/site_manager.php', array('sites' => $sites));
		$crumbs[] = array('url' => '?mod=config','title' => lang('configuration'));
		$crumbs[] = array('title' => lang('site_manager'));

		$this->r['title'] = lang('site_manager');
		$this->r['contentCenter'] = $main;
		$this->r['contentRight'] = '<button type="submit" class="btn btn-primary btn-block">' . lang('save') . '</button>';
		$this->r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$this->r['form_url'] = '?mod=config&act=site_update';
		return $this->r;
	}
	
	public function modules_view(){
		$box = array('logs','pages','config','templates','iblocks');
		foreach(glob('module/*') as $mod){
			$mod = str_replace('module/', '', $mod);
			if(!in_array($mod, $box)){
				if(file_exists('module/' . $mod . '/lang/' . Admin::$lang . '.php')){
					include 'module/' . $mod . '/lang/' . Admin::$lang . '.php';
				} else {
					include "module/{$mod}/lang/en.php";
				}
				$modules[$mod]['name'] = $l['module_name'];
				$modules[$mod]['installed'] = in_array($mod, array_keys(Admin::$config['modules']));
			}
		}
		
		$main = render('module/config/tpl/modules.php', array('modules' => $modules));
		$crumbs[] = array('url' => '?mod=config','title' => lang('configuration'));
		$crumbs[] = array('title' => lang('modules'));

		$this->r['title'] = lang('modules');
		$this->r['contentCenter'] = $main;
		$this->r['contentRight'] = ' ';
		$this->r['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $this->r;
	}
	
	public function backups_view(){
		if (isset(Admin::$config['sites'])){
			foreach(Admin::$config['sites'] as $site => $confs){
				$sites[$site]['name'] = Admin::$config['sites'][$site]['name'];
				$bkps = array();
				foreach(glob('../site/' . $site . '/backup_*.zip') as $bkp){
					$bkps[$bkp] = lang('download_backup') . ' ' . str_replace(array('../site/' . $site . '/backup_', '.zip'), '', $bkp);
				}
				$sites[$site]['archives'] = $bkps ? getList($bkps, '') : lang('empty');
			}
		}
		// TODO единый бекап, если получится в архивы
		$main = render('module/config/tpl/backups.php', array('sites' => $sites));
		$crumbs[] = array('url' => '?mod=config','title' => lang('configuration'));
		$crumbs[] = array('title' => lang('backups'));

		$this->r['title'] = lang('backups');
		$this->r['contentCenter'] = $main;
		$this->r['contentRight'] = ' ';
		$this->r['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $this->r;
	}

	public function pages_view(){
		// TODO
		$main = render('module/config/tpl/page_types.php', array());
		$right = '<button type="button" class="btn btn-primary btn-block">' . lang('save') . '</button>';
		$crumbs[] = array('url' => '?mod=config','title' => lang('configuration'));
		$crumbs[] = array('title' => lang('page_types'));

		$this->r['title'] = lang('page_types');
		$this->r['contentCenter'] = $main;
		$this->r['contentRight'] = '<button type="submit" class="btn btn-primary btn-block">' . lang('save') . '</button>';
		$this->r['breadcrumbs'] = getBreadcrumbs($crumbs);
		return $this->r;
	}
	
	public function site_update(){
		foreach($_POST as $site => $data){
			$_POST[$site]['domains'] = trim($_POST[$site]['domains']);
			$_POST[$site]['locales'] = trim($_POST[$site]['locales']);
			$_POST[$site]['site_name'] = trim($_POST[$site]['site_name']);
			if ($site != 'new'){
				Admin::$config['sites'][$site]['name'] = $_POST[$site]['site_name'];
				Admin::$config['sites'][$site]['locales'] = explode(" ", $_POST[$site]['locales']);
				Admin::$config['sites'][$site]['modules'] = empty($_POST[$site]['modules']) ? array() : $_POST[$site]['modules'];
				$domains = explode("\n", $_POST[$site]['domains']);
				foreach($domains as $dom){
					$dom = trim($dom);
					if(!empty($dom)){
						Admin::$config['domains'][$dom] = $site;
					}
				}
			}
		}
		Admin::saveConf();
		
		if (!empty($_POST['new']['domains']) and !empty($_POST['new']['site_name']) and !empty($_POST['new']['locales'])){
			$base = explode("\n", $_POST['new']['domains']);
			if (!empty($base[0])){
				Admin::createSite(properUri(trim($base[0])));
			}
		}
		redirect('?mod=config&act=sitemanager_view', lang('saved'), 'success');
	}

	public function site_delete(){
		if (!isset($_GET['token']) or $_GET['token'] !== Admin::$token){
			redirect('?mod=config&act=sitemanager_view', lang('unauthorized_request'));
		}
		Admin::$db = null;
		unset(Admin::$config['sites'][$_GET['site']]);
		$this->rmFoldr(SITE_DIR . $_GET['site']);
		
		foreach(Admin::$config['domains'] as $dom => $site){
			if($site == $_GET['site']) unset(Admin::$config['domains'][$dom]);
		}
		
		if (empty(Admin::$config['domains']) or empty(Admin::$config['sites'])){
			unlink(SITE_DIR . 'baseconfig.db');
		} else {
			Admin::saveConf();
		}
		redirect('?mod=config&act=sitemanager_view', lang('saved'), 'success');
	}
	
	public function profile_update(){
		if (!empty($_POST['username'])){
			Admin::$config['admin']['username'] = $_POST['username'];
		}
		if (!empty($_POST['password'])){
			Admin::$config['admin']['password'] = $_POST['password'];
		}
		if (!empty($_POST['mastercode'])){
			Admin::$config['admin']['mastercode'] = $_POST['mastercode'];
		}
		Admin::$config['admin']['autologin'] = isset($_POST['autologin']);
		Admin::$config['admin']['admin_ip'] = !empty($_POST['admin_ip']) ? explode(' ', $_POST['admin_ip']) : array();
		Admin::saveConf();
		redirect('?mod=config&act=profile_view', lang('saved'), 'success');
	}
	
	public function module_install(){
		if (file_exists("module/{$_GET['module']}/{$_GET['module']}.php")){
			foreach(glob("module/{$_GET['module']}/lang/*.php") as $lang){
				include $lang;
				$lang = str_replace(array("module/{$_GET['module']}/lang/", '.php'), '', $lang);
				Admin::$config['modules'][$_GET['module']]['name'][$lang] = $l['module_name'];
			}
			Admin::$config['modules'][$_GET['module']]['quick'] = false;
			Admin::saveConf();
		}

		redirect('?mod=config&act=modules_view', lang('module_installed'), 'success');
	}
	
	public function module_uninstall(){
		unset(Admin::$config['modules'][$_GET['module']]);
		foreach(Admin::$config['sites'] as $site => $sit){
			foreach($sit['modules'] as $module => $mod){
				if ($mod == $_GET['module']){
					unset(Admin::$config['sites'][$site]['modules'][$module]);
					break;
				}
				
			}
		}
		Admin::saveConf();
		redirect('?mod=config&act=modules_view', lang('module_uninstalled'), 'success');
	}
	
	public function modal_module_edit(){
		if (isset(Admin::$config['modules'][$_GET['module']])){
			$mod = Admin::$config['modules'][$_GET['module']];
			$mod_name = isset($mod['name'][Admin::$lang]) ? $mod['name'][Admin::$lang] : $mod['name']['en'];
			$quickAdd = getCheckbox('quick_add', $mod['quick'], lang('show_quick_add_link'));
			return array('modal' => array('title' => lang('configuring_the_module') . ' ' . $mod_name, 'content' => $quickAdd, 'form_url' => '?mod=config&act=module_save&module=' . $_GET['module']));
		}
		return array('error' => lang('error'));
	}
	
	public function module_save(){
		Admin::$config['modules'][$_GET['module']]['quick'] = isset($_POST['quick_add']);
		Admin::saveConf();
		return array('success' => lang('saved'));
	}
	
	public function save(){
		Admin::$siteConfig['template'] = $_POST['template'];
		Admin::$siteConfig['maintenance_mode'] = isset($_POST['maintenance_mode']);
		Admin::$siteConfig['need_cache'] = isset($_POST['need_cache']);
		Admin::$siteConfig['say'] = $_POST['say'];
		file_put_contents(SITE_DIR . Admin::$site . '/config.db', serialize(Admin::$siteConfig));
		redirect('?mod=config&act=view', lang('saved'), 'success');
	}
	
	public function modal_interface(){
	// TODO
		return array('modal' => array('title' => 'Настройка интерфейса', 'content' => 'about <b> new content</b>'));
	}

	private function getModuleSelector($site){
		$modules = array();
		foreach(Admin::$config['modules'] as $mod => $data){
			$modules[$mod] = $data['name'][Admin::$lang];
		}
		$used = $site == 'new' ? '' : (isset(Admin::$config['sites'][$site]['modules']) ? Admin::$config['sites'][$site]['modules'] : '');
		return getSelect($modules, $used, $props = 'name="' . $site . '[modules][]" multiple class="form-control site-modules"');
	}
	
	private function rmFoldr($dir){
		foreach(glob($dir . '/*') as $obj){
			is_dir($obj) ? $this->rmFoldr($obj) : unlink($obj);
		}
		rmdir($dir);
	}
}
?>