<?php
/**
* SyDES :: box module for configure and manage sites
* @version 1.8✓
* @copyright 2011-2013, ArtyGrand <artygrand.ru>
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/
class Config extends Module{
	public $name = 'config';
	public static $allowed4html = array('view', 'save', 'sitemanager_view', 'profile_view', 'modules_view', 'backups_view', 'pages_view', 'site_update', 'site_delete', 'profile_update', 'module_install', 'module_uninstall', 'pages_update');
	public static $allowed4ajax = array('modal_interface', 'metaadd', 'metaupdate', 'metadelete', 'clearcache', 'modal_module_edit', 'module_save', 'interface_update');
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
		$this->r['contentRight'] = Admin::getSaveButton(SITE_DIR . Admin::$site . '/config.db') . User::getMasterInput();
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
		$this->r['contentRight'] = Admin::getSaveButton(SITE_DIR . Admin::$site . '/config.db') . User::getMasterInput();
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
		$this->r['contentRight'] = Admin::getSaveButton(SITE_DIR . Admin::$site . '/config.db') . User::getMasterInput();
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
		
		include 'module/templates/templates.php';
		$template = new Templates();
		$template->createLayouts();
		foreach($template->getLayouts() as $k => $v){
			$layouts[$k] = $v['name'];
		}
		if (!issetTable('pages')){
			include 'module/pages/pages.php';
			$pages = new Pages();
			$pages->install();
		}
		$stmt = Admin::$db -> query("SELECT pages.id, pages_content.title FROM pages, pages_content WHERE pages.parent_id = 0 AND pages_content.page_id = pages.id AND pages.type = 'page' AND pages_content.locale = '" . Admin::$locale . "' ORDER BY pages.id");
		foreach($stmt -> fetchAll(PDO::FETCH_ASSOC) as $k){
			$roots[$k['id']] = $k['title'];
		}
		$types = Admin::$siteConfig['page_types'];
		$types['new'] = array('title' => '<span class="glyphicon glyphicon-plus"></span> ' . lang('create_new_pagetype'),'layout' => '','structure' => '','root' => '','meta' => array());
		$crumbs[] = array('url' => '?mod=config','title' => lang('configuration'));
		$crumbs[] = array('title' => lang('page_types'));
		$this->r['title'] = lang('page_types');
		$this->r['contentCenter'] = render('module/config/tpl/page_types.php', array('types' => $types, 'layouts' => $layouts, 'roots' => $roots));
		$this->r['contentRight'] = Admin::getSaveButton(SITE_DIR . Admin::$site . '/config.db') . User::getMasterInput();
		$this->r['breadcrumbs'] = getBreadcrumbs($crumbs);
		$this->r['form_url'] = '?mod=config&act=pages_update';
		return $this->r;
	}

	public function pages_update(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
		foreach($_POST['types'] as $type => $data){
			if ($type != 'new'){
				$data['meta'] = explode(' ', $data['meta']);
				Admin::$siteConfig['page_types'][$type] = array_merge(Admin::$siteConfig['page_types'][$type], $data);
			}
		}
		$new = $_POST['types']['new'];
		if (!empty($new['title']) and !empty($new['type'])){
			$type = $new['type'];
			unset($new['type']);
			$new['meta'] = explode(' ', $new['meta']);
			Admin::$siteConfig['page_types'][$type] = $new;
		}
		file_put_contents(SITE_DIR . Admin::$site . '/config.db', serialize(Admin::$siteConfig));
		Admin::log('User is saved page types');
		redirect('?mod=config&act=pages_view', lang('saved'), 'success');
	}

	public function site_update(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
		foreach($_POST['sites'] as $site => &$data){
			$data['domains'] = trim($data['domains']);
			$data['locales'] = trim($data['locales']);
			$data['site_name'] = trim($data['site_name']);
			if ($site != 'new'){
				Admin::$config['sites'][$site]['name'] = $data['site_name'];
				Admin::$config['sites'][$site]['locales'] = explode(" ", $data['locales']);
				Admin::$config['sites'][$site]['modules'] = empty($data['modules']) ? array() : $data['modules'];
				$domains = explode("\n", $data['domains']);
				foreach($domains as $dom){
					$dom = trim($dom);
					if(!empty($dom)){
						Admin::$config['domains'][$dom] = $site;
					}
				}
			}
		}
		Admin::saveConf();
		$new = $_POST['sites']['new'];
		if (!empty($new['domains']) and !empty($new['site_name']) and !empty($new['locales'])){
			$base = explode("\n", $new['domains']);
			if (!empty($base[0])){
				Admin::createSite(properUri(trim($base[0])));
				Admin::log('User is created new site');
			}
		}
		redirect('?mod=config&act=sitemanager_view', lang('saved'), 'success');
	}

	public function site_delete(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
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
			Admin::log('User is deleted site ' . $_GET['site']);
		}
		redirect('?mod=config&act=sitemanager_view', lang('saved'), 'success');
	}
	
	public function profile_update(){
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
		if (!empty($_POST['username'])){
			Admin::$config['admin']['username'] = $_POST['username'];
		}
		if (!empty($_POST['password'])){
			Admin::$config['admin']['password'] = $_POST['password'];
		}
		if (!empty($_POST['newmastercode'])){
			Admin::$config['admin']['mastercode'] = $_POST['newmastercode'];
		}
		Admin::$config['admin']['autologin'] = isset($_POST['autologin']);
		Admin::$config['admin']['admin_ip'] = !empty($_POST['admin_ip']) ? explode(' ', $_POST['admin_ip']) : array();
		Admin::saveConf();
		Admin::log('User is saved profile');
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
		if (!User::isMasterActive()){
			throw new Exception('restricted');
		}
		Admin::$siteConfig['template'] = $_POST['template'];
		Admin::$siteConfig['maintenance_mode'] = isset($_POST['maintenance_mode']);
		Admin::$siteConfig['need_cache'] = isset($_POST['need_cache']);
		Admin::$siteConfig['say'] = $_POST['say'];
		file_put_contents(SITE_DIR . Admin::$site . '/config.db', serialize(Admin::$siteConfig));
		Admin::log('User is saved site settings');
		redirect('?mod=config&act=view', lang('saved'), 'success');
	}
	
	public function modal_interface(){
		foreach (glob('language/*.php') as $l){
			$l = str_replace(array('language/', '.php'), '', $l);
			$langs[$l] = $l;
		}
		foreach (glob('template/css/skin/*.css') as $l){
			$l = str_replace(array('template/css/skin/', '.css'), '', $l);
			$skins[] = $l;
		}
		$form = render('module/config/tpl/modal_interface.php', array('langs' => $langs, 'menu' => isset($_COOKIE['menu']), 'skins' => $skins));
		return array('modal' => array('title' => lang('interface_setting'), 'content' => $form, 'form_url' => '?mod=config&act=interface_update'));
	}

	public function interface_update(){
		setcookie('lang', $_POST['lang'], time()+604800);
		if (isset($_POST['menu'])){
			setcookie('menu', 'click', time()+604800);
		} else {
			setcookie('menu', '', time()-1);
		}
		return array('success' => lang('saved'));
	}

	private function getModuleSelector($site){
		$modules = array();
		foreach(Admin::$config['modules'] as $mod => $data){
			$modules[$mod] = $data['name'][Admin::$lang];
		}
		$used = $site == 'new' ? '' : (isset(Admin::$config['sites'][$site]['modules']) ? Admin::$config['sites'][$site]['modules'] : '');
		return getSelect($modules, $used, $props = 'name="sites[' . $site . '][modules][]" multiple class="form-control site-modules"');
	}
	
	private function rmFoldr($dir){
		foreach(glob($dir . '/*') as $obj){
			is_dir($obj) ? $this->rmFoldr($obj) : unlink($obj);
		}
		rmdir($dir);
	}
	
	private function getLayouts(){
		$layouts = TEMPLATE_DIR . Admin::$siteConfig['template'] . '/' . 'layouts.db';
		return file_exists($layouts) ? unserialize(file_get_contents($layouts)) : array();
	}
}
?>