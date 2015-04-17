<?php
/**
 * @package SyDES
 *
 * @copyright 2011-2015, ArtyGrand <artygrand.ru>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class ModulesController extends Controller{
	public $name = 'modules';
	
	public function index(){
		$box_modules = array('common', 'config', 'constructors', 'iblocks', 'pages', 'search', 'templates', 'tools', 'user');
		$all_modules = str_replace(DIR_MODULE, '', glob(DIR_MODULE . '*'));
		$new = array_diff($all_modules, $box_modules);
		$installed = array_keys($this->config_site['modules']);

		foreach($new as $module){
			$this->load->language('module_' . $module);
			$modules[$module]['name'] = t('module_' . $module);
			$modules[$module]['installed'] = in_array($module, $installed);
		}

		$data['content'] = $this->load->view('config/modules-list', array('modules' => $modules));
		$data['sidebar_left'] = $this->getSideMenu('config/modules', array('interface'));
		$data['sidebar_right'] = ' ';
		$data['meta_title'] = t('modules');

		$crumbs = array(
			array('url' => '?route=config', 'title' => t('settings')),
			array('title' => t('modules'))
		);
		$data['breadcrumbs'] = H::breadcrumb($crumbs);

		$this->response->data = $data;
	}
}